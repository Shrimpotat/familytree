<?php
require __DIR__ . '/config.php';

if (php_sapi_name() !== 'cli') {
    echo "Run from CLI: php update_missing_lastname.php\n";
    exit(1);
}

echo "Fetching all persons...\n";
$people = fetch_all_persons();
$count = 0;
foreach ($people as $id => $p) {
    $last = $p['last_name'] ?? null;
    if (empty($last)) {
        echo "Updating $id: setting last_name=Pepito\n";
        $p['last_name'] = 'Pepito';
        try {
            update_person($id, $p);
            $count++;
        } catch (Exception $e) {
            echo "Error updating $id: " . $e->getMessage() . "\n";
        }
    }
}
echo "Done. Updated $count records.\n";
