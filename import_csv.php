<?php
require __DIR__ . '/config.php';

if (php_sapi_name() !== 'cli') {
    echo "Run from CLI: php import_csv.php path/to/file.csv\n";
    exit(1);
}

$csv = $argv[1] ?? __DIR__ . '/import.csv';
if (!file_exists($csv)) {
    echo "CSV file not found: $csv\n";
    exit(1);
}

$fh = fopen($csv, 'r');
if (!$fh) {
    echo "Failed to open CSV file\n";
    exit(1);
}

$header = fgetcsv($fh);
if (!$header) {
    echo "Empty or invalid CSV\n";
    exit(1);
}

// Normalize header
$cols = array_map(function($c){ return trim($c); }, $header);

while ($row = fgetcsv($fh)) {
    if (count($row) !== count($cols)) {
        // Skip malformed row
        continue;
    }
    $r = array_combine($cols, $row);
    $csv_id = trim($r['ID'] ?? '');
    if ($csv_id === '') continue;

    $fullname = trim($r['Full name'] ?? $r['Given names now'] ?? '');
    $parts = preg_split('/\\s+/', $fullname);
    $first = $parts[0] ?? '';
    array_shift($parts);
    $last = trim(implode(' ', $parts));
    // Temporary default last name to avoid undefined key errors
    if ($last === '' || $last === null) $last = 'Pepito';

    $person = [];
    if ($first !== '') $person['first_name'] = $first;
    // Ensure last_name always present (temporary)
    $person['last_name'] = $last;
    if (!empty($r['Mother ID'])) $person['mother_id'] = trim($r['Mother ID']) ?: null;
    if (!empty($r['Father ID'])) $person['father_id'] = trim($r['Father ID']) ?: null;
    if (!empty($r['Partner ID'])) {
        $person['spouse_id'] = trim($r['Partner ID']) ?: null;
    }
    // store original CSV id for reference
    $person['legacy_id'] = $csv_id;

    // Check if a person document with this ID exists
    $existing = get_person($csv_id);
    try {
        if ($existing) {
            update_person($csv_id, array_merge($existing, $person));
            echo "Updated person: $csv_id - " . ($person['first_name'] ?? '') . " " . ($person['last_name'] ?? '') . "\n";
        } else {
            // Create document with specific ID
            $fields = [];
            foreach ($person as $k => $v) {
                $fields[$k] = php_to_fs_value($v);
            }
            $path = "/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/persons?documentId=" . urlencode($csv_id);
            $res = firestore_call('POST', $path, ['fields' => $fields]);
            echo "Created person: $csv_id - " . ($person['first_name'] ?? '') . " " . ($person['last_name'] ?? '') . "\n";
        }
    } catch (Exception $e) {
        echo "Error for $csv_id: " . $e->getMessage() . "\n";
    }
}

fclose($fh);
echo "Import complete.\n";
