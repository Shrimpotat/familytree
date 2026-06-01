<?php
require 'config.php';
$people = fetch_all_persons();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }
$person = get_person($id);
if (!$person) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => s($_POST['first_name'] ?? ''),
        'last_name' => s($_POST['last_name'] ?? ''),
        'gender' => s($_POST['gender'] ?? ''),
        'birth_date' => s($_POST['birth_date'] ?? ''),
        'death_date' => s($_POST['death_date'] ?? ''),
        'bio' => s($_POST['bio'] ?? ''),
        'father_id' => s($_POST['father_id'] ?? null) ?: null,
        'mother_id' => s($_POST['mother_id'] ?? null) ?: null,
        'spouse_id' => s($_POST['spouse_id'] ?? null) ?: null,
        'family_name' => s($_POST['family_name'] ?? ''),
    ];
    try {
        update_person($id, $data);
        header('Location: index.php'); exit;
    } catch (Exception $e) { $error = $e->getMessage(); }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Person</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="form-wrap">
    <h2>Edit Person</h2>
    <?php if (!empty($error)): ?><div class="error"><?= s($error) ?></div><?php endif; ?>
    <form method="post">
        <label>First name<input name="first_name" value="<?= s($person['first_name']) ?>" required></label>
        <label>Last name<input name="last_name" value="<?= s($person['last_name']) ?>" required></label>
        <label>Gender
            <select name="gender">
                <option value="">--</option>
                <option value="Male" <?= ($person['gender']??'')==='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= ($person['gender']??'')==='Female'?'selected':'' ?>>Female</option>
                <option value="Other" <?= ($person['gender']??'')==='Other'?'selected':'' ?>>Other</option>
            </select>
        </label>
        <label>Birth date<input type="date" name="birth_date" value="<?= s($person['birth_date']??'') ?>"></label>
        <label>Death date<input type="date" name="death_date" value="<?= s($person['death_date']??'') ?>"></label>
        <label>Father
            <select name="father_id">
                <option value="">--</option>
                <?php foreach ($people as $p): if ($p['id'] === $id) continue; ?>
                    <option value="<?= $p['id'] ?>" <?= ($person['father_id']??'')===$p['id']?'selected':'' ?>><?= s($p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Mother
            <select name="mother_id">
                <option value="">--</option>
                <?php foreach ($people as $p): if ($p['id'] === $id) continue; ?>
                    <option value="<?= $p['id'] ?>" <?= ($person['mother_id']??'')===$p['id']?'selected':'' ?>><?= s($p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Spouse
            <select name="spouse_id">
                <option value="">--</option>
                <?php foreach ($people as $p): if ($p['id'] === $id) continue; ?>
                    <option value="<?= $p['id'] ?>" <?= ($person['spouse_id']??'')===$p['id']?'selected':'' ?>><?= s($p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Family name<input name="family_name" value="<?= s($person['family_name']??'') ?>"></label>
        <label>Bio<textarea name="bio"><?= s($person['bio']??'') ?></textarea></label>
        <div class="form-actions">
            <button type="submit">Update</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>
