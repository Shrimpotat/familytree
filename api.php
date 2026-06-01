<?php
require __DIR__ . '/config.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/familytree/api', '', $path);
$parts = array_filter(explode('/', $path));
$parts = array_values($parts);

try {
    // GET /persons - Fetch all persons
    if ($method === 'GET' && count($parts) === 1 && $parts[0] === 'persons') {
        $people = fetch_all_persons();
        echo json_encode($people);
    }
    
    // GET /persons/:id - Fetch single person
    else if ($method === 'GET' && count($parts) === 2 && $parts[0] === 'persons') {
        $person = get_person($parts[1]);
        if ($person) {
            echo json_encode($person);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Person not found']);
        }
    }
    
    // POST /persons - Create person
    else if ($method === 'POST' && count($parts) === 1 && $parts[0] === 'persons') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['first_name']) || trim($data['first_name']) === '') {
            http_response_code(400);
            echo json_encode(['error' => 'first_name is required']);
            exit;
        }
        
        $person = [
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'gender' => $data['gender'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'death_date' => $data['death_date'] ?? null,
            'bio' => $data['bio'] ?? null,
            'father_id' => $data['father_id'] ?? null,
            'mother_id' => $data['mother_id'] ?? null,
            'spouse_id' => $data['spouse_id'] ?? null,
        ];
        
        $id = add_person($person);
        $created = get_person($id);
        echo json_encode($created);
    }
    
    // PATCH /persons/:id - Update person
    else if ($method === 'PATCH' && count($parts) === 2 && $parts[0] === 'persons') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $parts[1];
        
        $existing = get_person($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Person not found']);
            exit;
        }
        
        $updated = array_merge($existing, $data);
        update_person($id, $updated);
        
        $result = get_person($id);
        echo json_encode($result);
    }
    
    // DELETE /persons/:id - Delete person
    else if ($method === 'DELETE' && count($parts) === 2 && $parts[0] === 'persons') {
        $id = $parts[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $reassign_to = $data['reassign_to'] ?? null;
        
        delete_person_and_reassign($id, $reassign_to);
        echo json_encode(['success' => true, 'message' => 'Person deleted']);
    }
    
    // GET /search?q=name - Search persons
    else if ($method === 'GET' && count($parts) === 1 && $parts[0] === 'search') {
        $q = strtolower($_GET['q'] ?? '');
        
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }
        
        $people = fetch_all_persons();
        $results = [];
        
        foreach ($people as $id => $person) {
            $fullname = strtolower(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
            if (strpos($fullname, $q) !== false) {
                $results[] = $person;
            }
        }
        
        echo json_encode($results);
    }
    
    // GET /lineage/:id - Get ancestors
    else if ($method === 'GET' && count($parts) === 2 && $parts[0] === 'lineage') {
        $id = $parts[1];
        $people = fetch_all_persons();
        $result = [];
        
        function collect_ancestors_json($id, &$people, &$result = [], $depth = 0) {
            if (!$id || isset($result[$id]) || $depth > 50) return;
            if (!isset($people[$id])) return;
            $person = $people[$id];
            $result[$id] = $person;
            if (!empty($person['father_id'])) collect_ancestors_json($person['father_id'], $people, $result, $depth+1);
            if (!empty($person['mother_id'])) collect_ancestors_json($person['mother_id'], $people, $result, $depth+1);
        }
        
        collect_ancestors_json($id, $people, $result);
        echo json_encode($result);
    }
    
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
