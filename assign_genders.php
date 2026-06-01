<?php
require __DIR__ . '/config.php';

$genderMap = [
    'Alin' => 'Male',
    'Anastacio' => 'Male',
    'Aquilino' => 'Male',
    'Arlene' => 'Female',
    'Arrian' => 'Female',
    'Azun' => 'Female',
    'Belen' => 'Female',
    'Benhur' => 'Male',
    'Benjamen' => 'Male',
    'Bobong' => 'Male',
    'Bonifacio' => 'Male',
    'Cering' => 'Female',
    'Diana' => 'Female',
    'Dionesia' => 'Female',
    'Dioscorro' => 'Male',
    'Egmedio' => 'Male',
    'Eric' => 'Male',
    'Eslao' => 'Male',
    'Estan' => 'Male',
    'Fernando' => 'Male',
    'Gereria' => 'Female',
    'JackDeniel' => 'Male',
    'Japeth' => 'Male',
    'Jhea' => 'Female',
    'Jholo' => 'Male',
    'Juliana' => 'Female',
    'Jun' => 'Male',
    'Kesha' => 'Female',
    'Kier' => 'Female',
    'Mangmerto' => 'Male',
    'Marcus' => 'Male',
    'Maribel' => 'Female',
    'Mariche' => 'Female',
    'Marlyn' => 'Female',
    'Matet' => 'Female',
    'Melanio' => 'Male',
    'Mely' => 'Female',
    'Merly' => 'Female',
    'Miggy' => 'Male',
    'Naria' => 'Female',
    'Pani' => 'Male',
    'Paquito' => 'Male',
    'Pina' => 'Female',
    'Pistong' => 'Male',
    'Quirino' => 'Male',
    'Rae' => 'Female',
    'Rain' => 'Male',
    'Rosario' => 'Male',
    'Sergio' => 'Male',
    'Sini' => 'Female',
    'Soling' => 'Female',
    'Temoteo' => 'Male',
    'Teofisto Jr' => 'Male',
    'Ysabelle' => 'Female',
    'Zandy' => 'Female',
    'Zusima' => 'Female',
];

$lowerMap = [];
foreach ($genderMap as $name => $gender) {
    $lowerMap[mb_strtolower($name, 'UTF-8')] = $gender;
}

$people = fetch_all_persons();
if (empty($people)) {
    echo "No persons found in Firestore.\n";
    exit(1);
}

$updated = 0;
$skipped = 0;
$notFound = [];

foreach ($people as $id => $person) {
    $firstName = trim($person['first_name'] ?? '');
    if ($firstName === '') {
        continue;
    }

    $key = mb_strtolower($firstName, 'UTF-8');
    if (!isset($lowerMap[$key])) {
        continue;
    }

    $desiredGender = $lowerMap[$key];
    $currentGender = trim($person['gender'] ?? '');

    if ($currentGender === $desiredGender) {
        $skipped++;
        continue;
    }

    $person['gender'] = $desiredGender;
    try {
        update_person($id, $person);
        echo "Updated $id: $firstName -> $desiredGender\n";
        $updated++;
    } catch (Exception $e) {
        echo "Failed to update $id ($firstName): " . $e->getMessage() . "\n";
        $notFound[] = $id;
    }
}

echo "\nSummary:\n";
echo " - Updated: $updated\n";
echo " - Unchanged/skipped: $skipped\n";
if (!empty($notFound)) {
    echo " - Failed updates: " . count($notFound) . "\n";
}
