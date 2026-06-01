<?php
require __DIR__ . '/config.php';

if (php_sapi_name() !== 'cli') {
    echo "Run from CLI: php migrate_partner_to_spouse.php\n";
    exit(1);
}

$people = fetch_all_persons();
$count = 0;
foreach ($people as $id => $p) {
    $partner = $p['partner_id'] ?? null;
    $spouse = $p['spouse_id'] ?? null;
    if (!empty($partner) && empty($spouse)) {
        $p['spouse_id'] = $partner;
        try {
            update_person($id, $p);
            echo "Migrated $id: partner_id -> spouse_id ($partner)\n";
            $count++;
        } catch (Exception $e) {
            echo "Error migrating $id: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migration complete. $count records updated.\n";
