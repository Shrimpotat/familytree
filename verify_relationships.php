<?php
require __DIR__ . '/config.php';

$people = fetch_all_persons();
$ids = array_keys($people);
$total = count($people);
$hasFather = 0; $hasMother = 0; $hasSpouse = 0; $legacyCount = 0;
$broken = ['father' => [], 'mother' => [], 'spouse' => []];
$genderCounts = ['Male'=>0,'Female'=>0,'Other'=>0,'Unknown'=>0];

foreach ($people as $id => $p) {
    if (!empty($p['father_id'])) {
        $hasFather++;
        if (!isset($people[$p['father_id']])) {
            $broken['father'][] = [$id, $p['father_id']];
        }
    }
    if (!empty($p['mother_id'])) {
        $hasMother++;
        if (!isset($people[$p['mother_id']])) {
            $broken['mother'][] = [$id, $p['mother_id']];
        }
    }
    if (!empty($p['spouse_id'])) {
        $hasSpouse++;
        if (!isset($people[$p['spouse_id']])) {
            $broken['spouse'][] = [$id, $p['spouse_id']];
        }
    }
    if (!empty($p['legacy_id'])) {
        $legacyCount++;
    }
    $gender = $p['gender'] ?? '';
    if (isset($genderCounts[$gender])) {
        $genderCounts[$gender]++;
    } else {
        $genderCounts['Unknown']++;
    }
}

function showLine($text = '') { echo $text . "\n"; }
showLine("Total persons: $total");
showLine("Persons with father set: $hasFather");
showLine("Persons with mother set: $hasMother");
showLine("Persons with spouse set: $hasSpouse");
showLine("Imported legacy entries: $legacyCount");
showLine("");
showLine("Gender counts:");
foreach ($genderCounts as $gender => $count) {
    showLine(" - $gender: $count");
}
showLine("");
$brokenCount = count($broken['father']) + count($broken['mother']) + count($broken['spouse']);
showLine("Broken relationship references: $brokenCount");
if ($brokenCount > 0) {
    foreach ($broken as $type => $items) {
        if (empty($items)) continue;
        showLine("\n$type references with no matching person:");
        foreach ($items as [$person, $ref]) {
            $name = ($people[$person]['first_name'] ?? '') . ' ' . ($people[$person]['last_name'] ?? '');
            showLine(" - $person ($name) -> missing $type id $ref");
        }
    }
}

showLine("\nSummary of entries with legacy IDs:");
$legacySample = 0;
foreach ($people as $id => $p) {
    if (!empty($p['legacy_id'])) {
        showLine(" - $id: " . trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) . " (legacy_id={$p['legacy_id']})");
        $legacySample++;
        if ($legacySample >= 25) break;
    }
}
if ($legacyCount > 25) {
    showLine("... plus " . ($legacyCount - 25) . " more legacy entries.");
}
