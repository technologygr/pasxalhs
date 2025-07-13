<?php
require 'config.php';
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit();
}
$entity = isset($_GET['entity']) ? $_GET['entity'] : '';
$names = [
    'licenses' => 'Πινακίδες',
    'employees' => 'Υπάλληλοι',
    'workplaces' => 'Τόποι Εργασίας'
];
if (!isset($names[$entity])) {
    exit('Invalid entity');
}
$table = $entity;
$title = $names[$entity];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id=?");
    $stmt->execute([intval($_GET['delete'])]);
    header("Location: manage.php?entity=$entity");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($id) {
        $stmt = $pdo->prepare("UPDATE $table SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO $table (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: manage.php?entity=$entity");
    exit();
}

$item = ['name' => ''];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$items = $pdo->query("SELECT * FROM $table ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ?></title>
<style>
    body{font-family:Arial,sans-serif;margin:0;padding:1em;background:#f4f4f4;}
    h1{text-align:center;}
    table{border-collapse:collapse;width:100%;margin-bottom:1em;}
    th,td{border:1px solid #ccc;padding:6px;text-align:left;}
    form{max-width:400px;margin:0 auto;background:#fff;padding:1em;border-radius:5px;}
    label{display:block;margin-bottom:.5em;}
    input{width:100%;padding:.5em;margin-top:.2em;box-sizing:border-box;}
    button,a{padding:.5px 1em;margin-top:.5em;display:inline-block;}
    a{background:#ccc;color:#000;text-decoration:none;border-radius:4px;}
    button{background:#007bff;color:#fff;border:none;border-radius:4px;}
</style>
</head>
<body>
<h1><?= $title ?></h1>
<table>
<tr><th>Όνομα</th><th>Ενέργειες</th></tr>
<?php foreach($items as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td>
        <a href="manage.php?entity=<?= $entity ?>&id=<?= $row['id'] ?>">✏️</a>
        |
        <a href="manage.php?entity=<?= $entity ?>&delete=<?= $row['id'] ?>" onclick="return confirm('Διαγραφή;');" style="color:red;">❌</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<form method="post">
    <input type="hidden" name="id" value="<?= $id ?>">
    <label>Όνομα:
        <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
    </label>
    <button type="submit">Αποθήκευση</button>
    <a href="helpers.php">Πίσω</a>
</form>
</body>
</html>
