<?php
require 'config.php';
$people = fetch_all_persons();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <h1>Family Tree</h1>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="tree.php">Tree View</a>
    </nav>
</header>
<main>
    <section class="controls">
        <a class="btn" href="add_person.php"><i class="fa fa-plus"></i> Add Person</a>
        <input id="search" placeholder="Search by name...">
    </section>

    <section id="results" class="grid">
        <?php foreach ($people as $p): ?>
            <div class="card">
                <h3><?= s($p['first_name'] . ' ' . $p['last_name']) ?></h3>
                <p><?= s($p['bio'] ?? '') ?></p>
                <p><strong>Born:</strong> <?= s($p['birth_date'] ?? '') ?></p>
                <p><strong>Gender:</strong> <?= s($p['gender'] ?? '') ?></p>
                <div class="actions">
                    <a href="edit_person.php?id=<?= $p['id'] ?>">Edit</a>
                    <a href="delete_person.php?id=<?= $p['id'] ?>">Delete</a>
                    <a href="lineage.php?id=<?= $p['id'] ?>">Lineage</a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
