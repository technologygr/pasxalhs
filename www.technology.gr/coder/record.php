<?php
require 'config.php';
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit();
}

// Fetch dropdown options from helper tables
$licenses = $pdo->query("SELECT name FROM licenses ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$workplaces = $pdo->query("SELECT name FROM workplaces ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$employees = $pdo->query("SELECT name FROM employees ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$work_types = ["Εκτακτη βλάβη","Προγραμματισμένο Service","Προγραμματισμένος έλεγχος","Αλλο γεγονός"];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save form
    $data = [
        'movement_date' => $_POST['movement_date'],
        'license' => $_POST['license'],
        'kilometers' => intval($_POST['kilometers']),
        'employee' => $_POST['employee'],
        'workplace' => $_POST['workplace'],
        'work_type' => $_POST['work_type'],
        'description' => $_POST['description'],
    ];
    if ($id) {
        // update
        $sql = "UPDATE car_jobs SET movement_date=:movement_date, license=:license, kilometers=:kilometers, employee=:employee, workplace=:workplace, work_type=:work_type, description=:description WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    } else {
        // insert
        $sql = "INSERT INTO car_jobs (movement_date, license, kilometers, employee, workplace, work_type, description) VALUES (:movement_date,:license,:kilometers,:employee,:workplace,:work_type,:description)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $id = $pdo->lastInsertId();
    }
    header('Location: index.php');
    exit();
}

// If editing, fetch existing data
$record = [
    'movement_date' => date('Y-m-d'),
    'license' => '',
    'kilometers' => '',
    'employee' => '',
    'workplace' => '',
    'work_type' => '',
    'description' => ''
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM car_jobs WHERE id = ?');
    $stmt->execute([$id]);
    $record = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $id ? 'Επεξεργασία' : 'Νέα' ?> Εργασία Οχήματος</title>
<style>
    body{font-family:Arial,sans-serif;margin:0;padding:1em;background:#f4f4f4;}
    h1{text-align:center;}
    form{max-width:600px;margin:0 auto;background:#fff;padding:1em;border-radius:5px;}
    label{display:block;margin-bottom:.5em;}
    input,select,textarea{width:100%;padding:.5em;margin-top:.2em;box-sizing:border-box;}
    button,a{padding:.5em 1em;margin-top:1em;display:inline-block;}
    a{background:#ccc;color:#000;text-decoration:none;border-radius:4px;}
    button{background:#007bff;color:#fff;border:none;border-radius:4px;}
</style>
</head>
<body>
<h1><?= $id ? 'Επεξεργασία' : 'Νέα' ?> Εργασία Οχήματος</h1>
<form method="post">
    <label>Ημερομηνία κίνησης: <input type="date" name="movement_date" value="<?= htmlspecialchars($record['movement_date']) ?>" required></label><br>
    <label>Αριθμός πινακίδας:
        <select name="license" required>
            <option value="">--Επιλογή--</option>
            <?php foreach ($licenses as $opt): ?>
            <option value="<?= $opt ?>" <?= $record['license']==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Χιλιόμετρα: <input type="number" name="kilometers" value="<?= htmlspecialchars($record['kilometers']) ?>" required></label><br>
    <label>Υπάλληλος:
        <select name="employee" required>
            <option value="">--Επιλογή--</option>
            <?php foreach ($employees as $opt): ?>
            <option value="<?= $opt ?>" <?= $record['employee']==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Τόπος εργασίας:
        <select name="workplace" required>
            <option value="">--Επιλογή--</option>
            <?php foreach ($workplaces as $opt): ?>
            <option value="<?= $opt ?>" <?= $record['workplace']==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Είδος εργασίας:
        <select name="work_type" required>
            <option value="">--Επιλογή--</option>
            <?php foreach ($work_types as $opt): ?>
            <option value="<?= $opt ?>" <?= $record['work_type']==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Περιγραφή:<br>
        <textarea name="description" rows="4" cols="50"><?= htmlspecialchars($record['description']) ?></textarea>
    </label><br>
    <button type="submit">Αποθήκευση</button>
    <a href="index.php">Ακύρωση</a>
</form>
</body>
</html>
