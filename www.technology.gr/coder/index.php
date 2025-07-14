<?php
require 'config.php';
session_start();

// Simple login check
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['username'] === '279902' && $_POST['password'] === '413500') {
            $_SESSION['logged_in'] = true;
            header('Location: index.php');
            exit();
        } else {
            $error = 'Λάθος στοιχεία.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="el">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Είσοδος</title>
    <style>
        body{font-family:Arial,sans-serif;padding:1em;background:#f4f4f4;}
        form{max-width:300px;margin:2em auto;padding:1em;background:#fff;border-radius:5px;}
        label{display:block;margin-bottom:.5em;}
        input[type=text],input[type=password]{width:100%;padding:.5em;margin-bottom:1em;box-sizing:border-box;}
        button{width:100%;padding:.5em;}
        .error{color:red;text-align:center;}
    </style>
    </head>
    <body>
    <form method="post">
        <h2 style="text-align:center;">Σύνδεση</h2>
        <?php if(isset($error)) echo '<p class="error">'.$error.'</p>'; ?>
        <label>Username: <input type="text" name="username" required></label>
        <label>Password: <input type="password" name="password" required></label>
        <button type="submit">Είσοδος</button>
    </form>
    </body>
    </html>
    <?php
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare('DELETE FROM car_jobs WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: index.php');
    exit();
}

function fmt_date($d){
    return date('d-m-Y', strtotime($d));
}

// fetch dropdown lists for filters
$licenses = $pdo->query("SELECT name FROM licenses ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$workplaces = $pdo->query("SELECT name FROM workplaces ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$work_types = ["Εκτακτη βλάβη","Προγραμματισμένο Service","Προγραμματισμένος έλεγχος","Αλλο γεγονός"];

// Build filtering query
$sql = 'SELECT * FROM car_jobs WHERE 1';
$params = [];
$filter_date = $_GET['f_date'] ?? '';
$filter_license = $_GET['f_license'] ?? '';
$filter_workplace = $_GET['f_workplace'] ?? '';
$filter_work_type = $_GET['f_work_type'] ?? '';
if($filter_date){ $sql .= ' AND movement_date = ?'; $params[] = $filter_date; }
if($filter_license){ $sql .= ' AND license = ?'; $params[] = $filter_license; }
if($filter_workplace){ $sql .= ' AND workplace = ?'; $params[] = $filter_workplace; }
if($filter_work_type){ $sql .= ' AND work_type = ?'; $params[] = $filter_work_type; }
$sql .= ' ORDER BY movement_date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Καταχώρηση Εργασιών Οχημάτων</title>
<style>
    body{font-family:Arial,sans-serif;margin:0;padding:0;}
    header{padding:1em;background:#333;color:#fff;text-align:center;}
    .container{padding:1em;}
    table{border-collapse:collapse;width:100%;}
    th,td{border:1px solid #ccc;padding:8px;text-align:left;}
    @media(max-width:600px){
        table,thead,tbody,tr,th,td{display:block;}
        tr{margin-bottom:1em;}
        th{background:#f0f0f0;}
        th,td{border:none;padding:4px;}
        td:before{content:attr(data-label);font-weight:bold;display:block;}
    }
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
<div class="container">
<p><a href="record.php">Νέα Καταχώρηση</a></p>
<div style="overflow-x:auto;">
<form method="get">
<table>
<tr>
    <th>Ημερομηνία<br>🔍</th>
    <th>Πινακίδα<br>🔍</th>
    <th>Χιλιόμετρα</th>
    <th>Υπάλληλος</th>
    <th>Τόπος<br>🔍</th>
    <th>Είδος<br>🔍</th>
    <th>Περιγραφή</th>
    <th>Ενέργειες</th>
</tr>
<tr>
    <td><input type="date" name="f_date" value="<?= htmlspecialchars($filter_date) ?>" style="width:100%;"></td>
    <td>
        <select name="f_license" style="width:100%;">
            <option value="">--</option>
            <?php foreach($licenses as $opt): ?>
            <option value="<?= $opt ?>" <?= $filter_license==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td></td>
    <td></td>
    <td>
        <select name="f_workplace" style="width:100%;">
            <option value="">--</option>
            <?php foreach($workplaces as $opt): ?>
            <option value="<?= $opt ?>" <?= $filter_workplace==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <select name="f_work_type" style="width:100%;">
            <option value="">--</option>
            <?php foreach($work_types as $opt): ?>
            <option value="<?= $opt ?>" <?= $filter_work_type==$opt?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td></td>
    <td><button type="submit">OK</button> <a href="index.php">Reset</a></td>
</tr>
<?php foreach ($records as $row): ?>
<tr>
    <td data-label="Ημερομηνία"><?= fmt_date($row['movement_date']) ?></td>
    <td data-label="Πινακίδα"><?= htmlspecialchars($row['license']) ?></td>
    <td data-label="Χιλιόμετρα"><?= number_format($row['kilometers'],0,',','.') ?></td>
    <td data-label="Υπάλληλος"><?= htmlspecialchars($row['employee']) ?></td>
    <td data-label="Τόπος"><?= htmlspecialchars($row['workplace']) ?></td>
    <td data-label="Είδος"><?= htmlspecialchars($row['work_type']) ?></td>
    <td data-label="Περιγραφή"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
    <td>
        <a href="record.php?id=<?= $row['id'] ?>" title="Επεξεργασία" style="text-decoration:none;">✏️</a>
        |
        <a href="?delete=<?= $row['id'] ?>" title="Διαγραφή" onclick="return confirm('Διαγραφή εγγραφής;');" style="color:red;text-decoration:none;">❌</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
</form>
</div>
<p style="margin-top:1em;"><a href="export.php?type=excel">Export Excel</a> | <a href="export.php?type=pdf">Export PDF</a></p>
</div>
</body>
</html>
