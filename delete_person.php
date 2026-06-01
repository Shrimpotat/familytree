<?php
require 'config.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }
$people = fetch_all_persons();
$person = get_person($id);
if (!$person) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reassign_to = s($_POST['reassign_to'] ?? null) ?: null;
    try {
        delete_person_and_reassign($id, $reassign_to);
        header('Location: index.php'); exit;
    } catch (Exception $e) { $error = $e->getMessage(); }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Person</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="form-wrap">
    <h2>Delete <?= s($person['first_name'].' '.$person['last_name']) ?></h2>
    <?php if (!empty($error)): ?><div class="error"><?= s($error) ?></div><?php endif; ?>
    <form method="post">
        <p>Do you want to reassign this person's children to another parent? Choose a reassignment or leave blank to clear parent links.</p>
        <label>Reassign children to
            <select name="reassign_to">
                <option value="">-- none --</option>
                <?php foreach ($people as $p): if ($p['id'] === $id) continue; ?>
                    <option value="<?= $p['id'] ?>"><?= s($p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-actions">
            <button type="submit" class="danger">Delete</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>
