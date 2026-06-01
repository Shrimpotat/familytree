<?php
// Firebase Firestore configuration using REST API (no SDK/Composer needed)
// Works with PHP 8.2+ without extensions
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FIREBASE_PROJECT_ID', 'pp2fmly3');
define('FIREBASE_CREDENTIALS', __DIR__ . '/firebase_credentials.json');
define('FIRESTORE_API_BASE', 'https://firestore.googleapis.com/v1');
define('FIREBASE_TOKEN_URL', 'https://www.googleapis.com/oauth2/v4/token');

// Load and validate credentials
if (!file_exists(FIREBASE_CREDENTIALS)) {
    die("<h2>Firestore init error</h2>" .
        "<p>Could not find <strong>firebase_credentials.json</strong> in the project root.</p>" .
        "<p>1. Go to <strong>Firebase Console</strong> → <strong>Project Settings</strong> → <strong>Service Accounts</strong></p>" .
        "<p>2. Click <strong>Generate New Private Key</strong> and save the JSON file.</p>" .
        "<p>3. Save it to: <strong>C:\\xampp\\htdocs\\familytree\\firebase_credentials.json</strong></p>" .
        "<p>4. Reload this page.</p>");
}

$creds = json_decode(file_get_contents(FIREBASE_CREDENTIALS), true);
if (!$creds || empty($creds['private_key'])) {
    die("<h2>Firestore init error</h2><p>Invalid firebase_credentials.json format.</p>");
}

// Create JWT token for service account authentication
function get_firestore_token() {
    global $creds;
    $now = time();
    $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'iss' => $creds['client_email'],
        'sub' => $creds['client_email'],
        'aud' => FIREBASE_TOKEN_URL,
        'iat' => $now,
        'exp' => $now + 3600,
        'scope' => 'https://www.googleapis.com/auth/cloud-platform'
    ]));
    $signature = '';
    openssl_sign("$header.$payload", $signature, $creds['private_key'], 'sha256');
    $token_jwt = "$header.$payload." . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => FIREBASE_TOKEN_URL,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $token_jwt]),
        CURLOPT_RETURNTRANSFER => 1,
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response['access_token'] ?? null;
}

// Cache token (in-memory, valid for 1 hour)
$_firestore_token = null;
$_firestore_token_time = 0;

function get_cached_token() {
    global $_firestore_token, $_firestore_token_time;
    if (!$_firestore_token || time() - $_firestore_token_time > 3500) {
        $_firestore_token = get_firestore_token();
        $_firestore_token_time = time();
    }
    return $_firestore_token;
}

// Make REST API calls with authentication
function firestore_call($method, $path, $data = null) {
    $token = get_cached_token();
    if (!$token) {
        throw new Exception('Failed to get Firebase auth token');
    }
    
    $url = FIRESTORE_API_BASE . $path;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    if ($http_code >= 400) {
        throw new Exception('Firestore API error: ' . ($result['error']['message'] ?? $response));
    }
    return $result;
}

// Convert Firestore value format to PHP
function fs_value_to_php($value) {
    if (isset($value['nullValue'])) return null;
    if (isset($value['booleanValue'])) return $value['booleanValue'];
    if (isset($value['integerValue'])) return (int)$value['integerValue'];
    if (isset($value['doubleValue'])) return (float)$value['doubleValue'];
    if (isset($value['stringValue'])) return $value['stringValue'];
    if (isset($value['timestampValue'])) return $value['timestampValue'];
    if (isset($value['arrayValue'])) return array_map('fs_value_to_php', $value['arrayValue']['values'] ?? []);
    if (isset($value['mapValue'])) {
        $out = [];
        foreach ($value['mapValue']['fields'] ?? [] as $k => $v) {
            $out[$k] = fs_value_to_php($v);
        }
        return $out;
    }
    return null;
}

// Convert PHP value to Firestore format
function php_to_fs_value($value) {
    if ($value === null) return ['nullValue' => 'NULL_VALUE'];
    if (is_bool($value)) return ['booleanValue' => $value];
    if (is_int($value)) return ['integerValue' => (string)$value];
    if (is_float($value)) return ['doubleValue' => $value];
    if (is_string($value)) return ['stringValue' => $value];
    if (is_array($value)) {
        $indexed = array_values($value);
        if ($indexed === $value) { // numeric array
            return ['arrayValue' => ['values' => array_map('php_to_fs_value', $value)]];
        } else { // assoc array
            $fields = [];
            foreach ($value as $k => $v) {
                $fields[$k] = php_to_fs_value($v);
            }
            return ['mapValue' => ['fields' => $fields]];
        }
    }
    return ['stringValue' => (string)$value];
}

// Parse Firestore document to PHP array
function parse_fs_doc($doc) {
    if (!isset($doc['fields'])) return [];
    $out = [];
    foreach ($doc['fields'] as $k => $v) {
        $out[$k] = fs_value_to_php($v);
    }
    return $out;
}

// Extract doc ID from path
function extract_doc_id($name) {
    $parts = explode('/', $name);
    return end($parts);
}

// Fetch all persons
function fetch_all_persons() {
    try {
        $out = [];
        $pageToken = null;

        do {
            $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons?pageSize=1000";
            if ($pageToken) {
                $path .= '&pageToken=' . urlencode($pageToken);
            }

            $result = firestore_call('GET', $path);
            foreach ($result['documents'] ?? [] as $doc) {
                $data = parse_fs_doc($doc);
                $data['id'] = extract_doc_id($doc['name']);
                $out[$data['id']] = $data;
            }
            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);

        return $out;
    } catch (Exception $e) {
        error_log('fetch_all_persons error: ' . $e->getMessage());
        return [];
    }
}

function get_person($id) {
    try {
        $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons/$id";
        $doc = firestore_call('GET', $path);
        $data = parse_fs_doc($doc);
        $data['id'] = $id;
        return $data;
    } catch (Exception $e) {
        return null;
    }
}

function add_person($data) {
    try {
        $data['created_at'] = date('c');
        $data['updated_at'] = date('c');
        
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[$k] = php_to_fs_value($v);
        }
        
        $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons";
        $result = firestore_call('POST', $path . '?documentId=', ['fields' => $fields]);
        return extract_doc_id($result['name']);
    } catch (Exception $e) {
        throw $e;
    }
}

function update_person($id, $data) {
    try {
        $data['updated_at'] = date('c');
        
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[$k] = php_to_fs_value($v);
        }
        
        $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons/$id";
        firestore_call('PATCH', $path, ['fields' => $fields]);
    } catch (Exception $e) {
        throw $e;
    }
}

function delete_person_and_reassign($id, $reassign_to = null) {
    try {
        $people = fetch_all_persons();
        
        // Reassign children
        foreach ($people as $p) {
            $update = false;
            if (!empty($p['father_id']) && $p['father_id'] === $id) {
                $p['father_id'] = $reassign_to;
                $update = true;
            }
            if (!empty($p['mother_id']) && $p['mother_id'] === $id) {
                $p['mother_id'] = $reassign_to;
                $update = true;
            }
            if ($update) {
                update_person($p['id'], $p);
            }
        }
        
        // Delete
        $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons/$id";
        firestore_call('DELETE', $path);
    } catch (Exception $e) {
        throw $e;
    }
}

// Lineage helpers (same as before)
function collect_ancestors($id, &$people, &$result = [], $depth = 0) {
    if (!$id || isset($result[$id]) || $depth > 50) return;
    if (!isset($people[$id])) return;
    $person = $people[$id];
    $result[$id] = $person;
    if (!empty($person['father_id'])) collect_ancestors($person['father_id'], $people, $result, $depth+1);
    if (!empty($person['mother_id'])) collect_ancestors($person['mother_id'], $people, $result, $depth+1);
}

function collect_descendants($id, &$people, &$result = [], $depth = 0) {
    if (!$id || isset($result[$id]) || $depth > 50) return;
    if (!isset($people[$id])) return;
    $children = [];
    foreach ($people as $p) {
        if (!empty($p['father_id']) && $p['father_id'] === $id) $children[] = $p;
        if (!empty($p['mother_id']) && $p['mother_id'] === $id) $children[] = $p;
    }
    foreach ($children as $c) {
        if (isset($result[$c['id']])) continue;
        $result[$c['id']] = $c;
        collect_descendants($c['id'], $people, $result, $depth+1);
    }
}

function search_persons_by_name($term) {
    $term = mb_strtolower(trim($term));
    $all = fetch_all_persons();
    $out = [];
    foreach ($all as $p) {
        $fullname = mb_strtolower(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
        if ($term === '' || strpos($fullname, $term) !== false) $out[] = $p;
    }
    return $out;
}

// Sanitize
function s($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

?>

