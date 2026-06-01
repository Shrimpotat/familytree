<?php
require 'config.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }
$people = fetch_all_persons();
$person = get_person($id);
if (!$person) { header('Location: index.php'); exit; }
$anc = [];
collect_ancestors($id, $people, $anc);
$desc = [];
collect_descendants($id, $people, $desc);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lineage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Lineage of <?= s($person['first_name'].' '.$person['last_name']) ?></h1><nav><a href="index.php">Dashboard</a></nav></header>
<main>
    <section>
        <h2>Ancestors (<?= count($anc) ?>)</h2>
        <div class="grid">
            <?php foreach ($anc as $a): ?>
                <div class="card"><h3><?= s($a['first_name'].' '.$a['last_name']) ?></h3><p><?= s($a['birth_date'] ?? '') ?></p></div>
            <?php endforeach; ?>
        </div>
    </section>
    <section>
        <h2>Descendants (<?= count($desc) ?>)</h2>
        <div class="grid">
            <?php foreach ($desc as $d): ?>
                <div class="card"><h3><?= s($d['first_name'].' '.$d['last_name']) ?></h3><p><?= s($d['birth_date'] ?? '') ?></p></div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
