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
        'next_service_date' => $_POST['next_service_date'] ?: null,
        'next_service_km' => $_POST['next_service_km'] !== '' ? intval($_POST['next_service_km']) : null,
        'user_notes' => $_POST['user_notes'],
        'battery' => $_POST['battery'] ?? 'ΟΧΙ',
        'tires' => $_POST['tires'] ?? 'ΟΧΙ',
    ];

    $warnings = [];
    $stmt = $pdo->prepare('SELECT kilometers FROM car_jobs WHERE license=? ORDER BY movement_date DESC, id DESC LIMIT 1');
    $stmt->execute([$data['license']]);
    $lastKm = $stmt->fetchColumn();
    if($lastKm !== false && $data['kilometers'] < $lastKm){
        $warnings[] = 'Προειδοποίηση: μικρότερα χιλιόμετρα από την τελευταία καταχώριση.';
    }
    if($data['next_service_date'] && $data['next_service_date'] <= $data['movement_date']){
        $warnings[] = 'Προειδοποίηση: η ημερομηνία επόμενου service είναι προγενέστερη.';
    }
    if($data['next_service_km'] !== null && $data['next_service_km'] <= $data['kilometers']){
        $warnings[] = 'Προειδοποίηση: τα χιλιόμετρα επόμενου service είναι μικρότερα.';
    }
    if ($id) {
        // update
        $sql = "UPDATE car_jobs SET movement_date=:movement_date, license=:license, kilometers=:kilometers, employee=:employee, workplace=:workplace, work_type=:work_type, description=:description, next_service_date=:next_service_date, next_service_km=:next_service_km, user_notes=:user_notes, battery=:battery, tires=:tires WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
    } else {
        // insert
        $sql = "INSERT INTO car_jobs (movement_date, license, kilometers, employee, workplace, work_type, description, next_service_date, next_service_km, user_notes, battery, tires) VALUES (:movement_date,:license,:kilometers,:employee,:workplace,:work_type,:description,:next_service_date,:next_service_km,:user_notes,:battery,:tires)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $id = $pdo->lastInsertId();
    }
    if($warnings){
        $_SESSION['warning'] = implode(' ', $warnings);
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
    'description' => '',
    'next_service_date' => '',
    'next_service_km' => '',
    'user_notes' => '',
    'battery' => 'ΟΧΙ',
    'tires' => 'ΟΧΙ'
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
    body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f4f4f4;}
    header{padding:1em;background:#333;color:#fff;text-align:center;}
    footer{padding:.3em;background:#333;color:#fff;font-size:12px;text-align:left;}
    h1{text-align:center;margin:0;padding:1em 0;}
    form{max-width:600px;margin:0 auto;background:#fff;padding:1em;border-radius:5px;}
    label{display:block;margin-bottom:.5em;}
    input,select,textarea{width:100%;padding:.5em;margin-top:.2em;box-sizing:border-box;}
    button,a{padding:.5em 1em;margin-top:1em;display:inline-block;}
    a{background:#ccc;color:#000;text-decoration:none;border-radius:4px;}
    button{background:#007bff;color:#fff;border:none;border-radius:4px;}
</style>
</head>
<body>
<header>
    <h1>Εργασίες Οχημάτων</h1>
    <nav>
        <a href="index.php" style="color:#fff;margin-right:10px;">Αρχική</a>
        <a href="record.php" style="color:#fff;margin-right:10px;">Νέα Καταχώρηση</a>
        <a href="calendar.php" style="color:#fff;margin-right:10px;">Ημερολόγιο</a>
        <a href="helpers.php" style="color:#fff;">Βοηθητικά</a>
    </nav>
</header>
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
    <label>Ημερομηνία επόμενου Service: <input type="date" name="next_service_date" value="<?= htmlspecialchars($record['next_service_date']) ?>"></label><br>
    <label>Χιλιόμετρα επόμενου Service: <input type="number" name="next_service_km" value="<?= htmlspecialchars($record['next_service_km']) ?>"></label><br>
    <label>Σημειώσεις χρήστη:<br>
        <textarea name="user_notes" rows="2" cols="50"><?= htmlspecialchars($record['user_notes']) ?></textarea>
    </label><br>
    <label>Αφορά μπαταρία:
        <select name="battery">
            <option value="ΟΧΙ" <?= $record['battery']=='ΟΧΙ'?'selected':'' ?>>ΟΧΙ</option>
            <option value="ΝΑΙ" <?= $record['battery']=='ΝΑΙ'?'selected':'' ?>>ΝΑΙ</option>
        </select>
    </label><br>
    <label>Αφορά ελαστικά:
        <select name="tires">
            <option value="ΟΧΙ" <?= $record['tires']=='ΟΧΙ'?'selected':'' ?>>ΟΧΙ</option>
            <option value="ΝΑΙ" <?= $record['tires']=='ΝΑΙ'?'selected':'' ?>>ΝΑΙ</option>
        </select>
    </label><br>
    <button type="submit">Αποθήκευση</button>
    <a href="index.php">Ακύρωση</a>
</form>
<footer>ver 1.0  (c) 2025</footer>
</body>
</html>
