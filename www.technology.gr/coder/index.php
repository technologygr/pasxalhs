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

// Fetch all records
$stmt = $pdo->query('SELECT * FROM car_jobs ORDER BY movement_date DESC, id DESC');
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
    <a href="helpers.php" style="color:#fff;">Βοηθητικά</a>
</nav>
</header>
<div class="container">
<p><a href="record.php">Νέα Καταχώρηση</a></p>
<div style="overflow-x:auto;">
<table>
<tr>
    <th>Ημερομηνία</th>
    <th>Πινακίδα</th>
    <th>Χιλιόμετρα</th>
    <th>Υπάλληλος</th>
    <th>Τόπος</th>
    <th>Είδος</th>
    <th>Περιγραφή</th>
    <th>Ενέργειες</th>
</tr>
<?php foreach ($records as $row): ?>
<tr>
    <td data-label="Ημερομηνία"><?= htmlspecialchars($row['movement_date']) ?></td>
    <td data-label="Πινακίδα"><?= htmlspecialchars($row['license']) ?></td>
    <td data-label="Χιλιόμετρα"><?= htmlspecialchars($row['kilometers']) ?></td>
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
</div>
<p style="margin-top:1em;"><a href="export.php?type=excel">Export Excel</a> | <a href="export.php?type=pdf">Export PDF</a></p>
</div>
</body>
</html>
