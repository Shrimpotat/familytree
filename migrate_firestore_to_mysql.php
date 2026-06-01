<?php
require __DIR__ . '/config.php';

// MySQL connection settings. Update these values before running.
$mysqlHost = getenv('MYSQL_HOST') ?: '127.0.0.1';
$mysqlPort = getenv('MYSQL_PORT') ?: '3306';
$mysqlDb   = getenv('MYSQL_DATABASE') ?: 'familytree';
$mysqlUser = getenv('MYSQL_USER') ?: 'root';
$mysqlPass = getenv('MYSQL_PASSWORD') ?: '';

$dsn = "mysql:host={$mysqlHost};port={$mysqlPort};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "MySQL connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$mysqlDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$mysqlDb}`");
} catch (PDOException $e) {
    echo "Database creation or selection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS persons (
    id VARCHAR(64) NOT NULL,
    legacy_id VARCHAR(64) DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    birth_date VARCHAR(50) DEFAULT NULL,
    death_date VARCHAR(50) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    father_id VARCHAR(64) DEFAULT NULL,
    mother_id VARCHAR(64) DEFAULT NULL,
    spouse_id VARCHAR(64) DEFAULT NULL,
    family_name VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_legacy_id (legacy_id),
    INDEX idx_name (first_name, last_name),
    INDEX idx_father_id (father_id),
    INDEX idx_mother_id (mother_id),
    INDEX idx_spouse_id (spouse_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
);

$people = fetch_all_persons();
$total = count($people);
if ($total === 0) {
    echo "No Firestore person records found.\n";
    exit(0);
}

$insertStmt = $pdo->prepare(
    'INSERT INTO persons (id, legacy_id, first_name, last_name, gender, birth_date, death_date, bio, father_id, mother_id, spouse_id, family_name, created_at, updated_at) VALUES (:id, :legacy_id, :first_name, :last_name, :gender, :birth_date, :death_date, :bio, :father_id, :mother_id, :spouse_id, :family_name, :created_at, :updated_at)
    ON DUPLICATE KEY UPDATE
        legacy_id = VALUES(legacy_id),
        first_name = VALUES(first_name),
        last_name = VALUES(last_name),
        gender = VALUES(gender),
        birth_date = VALUES(birth_date),
        death_date = VALUES(death_date),
        bio = VALUES(bio),
        father_id = VALUES(father_id),
        mother_id = VALUES(mother_id),
        spouse_id = VALUES(spouse_id),
        family_name = VALUES(family_name),
        updated_at = VALUES(updated_at)'
);

$inserted = 0;
foreach ($people as $id => $person) {
    $insertStmt->execute([
        ':id' => $id,
        ':legacy_id' => $person['legacy_id'] ?? null,
        ':first_name' => $person['first_name'] ?? '',
        ':last_name' => $person['last_name'] ?? 'Pepito',
        ':gender' => $person['gender'] ?? null,
        ':birth_date' => $person['birth_date'] ?? null,
        ':death_date' => $person['death_date'] ?? null,
        ':bio' => $person['bio'] ?? null,
        ':father_id' => $person['father_id'] ?? null,
        ':mother_id' => $person['mother_id'] ?? null,
        ':spouse_id' => $person['spouse_id'] ?? null,
        ':family_name' => $person['family_name'] ?? null,
        ':created_at' => $person['created_at'] ?? date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s'),
    ]);
    $inserted++;
}

echo "Migrated $inserted Firestore persons into MySQL database '{$mysqlDb}'.\n";
echo "Run this script again any time to sync existing Firestore person data into MySQL.\n";
