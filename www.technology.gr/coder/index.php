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
    <meta name="robots" content="noindex, nofollow">
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

function display_text($text){
    $safe = nl2br(htmlspecialchars($text));
    if(mb_strlen($text)<=350) return $safe;
    $short = nl2br(htmlspecialchars(mb_substr($text,0,350)));
    $expand = '<span class="expand" style="cursor:pointer;color:blue;">&#x2795;</span>';
    $collapse = '<span class="collapse" style="cursor:pointer;color:blue;">&#x2796;</span>';
    return $short.' '.$expand.'<span class="full-text" style="display:none;">'.$safe.' '.$collapse.'</span>';
}

// fetch dropdown lists for filters
$licenses = $pdo->query("SELECT name FROM licenses ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$workplaces = $pdo->query("SELECT name FROM workplaces ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$work_types = $pdo->query("SELECT name FROM work_types ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

// Build filtering query
$where = [];
$params = [];
$filter_date = $_GET['f_date'] ?? '';
$filter_license = $_GET['f_license'] ?? '';
$filter_workplace = $_GET['f_workplace'] ?? '';
$filter_work_type = $_GET['f_work_type'] ?? '';
$filter_battery = $_GET['f_battery'] ?? '';
$filter_tires = $_GET['f_tires'] ?? '';
if($filter_date){ $where[] = 'movement_date = ?'; $params[] = $filter_date; }
if($filter_license){ $where[] = 'license = ?'; $params[] = $filter_license; }
if($filter_workplace){ $where[] = 'workplace = ?'; $params[] = $filter_workplace; }
if($filter_work_type){ $where[] = 'work_type = ?'; $params[] = $filter_work_type; }
if($filter_battery){ $where[] = 'battery = ?'; $params[] = $filter_battery; }
if($filter_tires){ $where[] = 'tires = ?'; $params[] = $filter_tires; }
$whereSql = $where ? (' WHERE '.implode(' AND ', $where)) : '';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page-1)*$perPage;

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM car_jobs'.$whereSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();

$sql = 'SELECT * FROM car_jobs'.$whereSql.' ORDER BY movement_date DESC, id DESC LIMIT ? OFFSET ?';
$stmt = $pdo->prepare($sql);
$dataParams = array_merge($params, [$perPage, $offset]);
$stmt->execute($dataParams);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Καταχώρηση Εργασιών Οχημάτων</title>
<style>
    body{font-family:Arial,sans-serif;margin:0;padding:0;}
    header{padding:1em;background:#333;color:#fff;text-align:center;}
    footer{padding:.3em;background:#333;color:#fff;font-size:12px;text-align:left;}
    .container{padding:1em;}
    table{border-collapse:collapse;width:100%;}
    th,td{border:1px solid #ccc;padding:8px;text-align:left;}
    tbody tr:nth-child(even){background:#e8f4ff;}
    @media(max-width:600px){
        table,thead,tbody,tr,th,td{display:block;}
        tr{margin-bottom:1em;}
        thead tr:first-child{display:none;}
        th,td{border:none;padding:4px;text-align:left !important;}
        td:before{content:attr(data-label);font-weight:bold;display:block;}
    }
</style>
</head>
<body>
<header>
<h1>Εργασίες Οχημάτων</h1>
<nav>
    <a href="index.php" style="color:#fff;margin-right:10px;">Αρχική</a>
    <a href="record.php" style="color:#fff;margin-right:10px;">Νέα Καταχώριση</a>
    <a href="calendar.php" style="color:#fff;margin-right:10px;">Ημερολόγιο</a>
    <a href="helpers.php" style="color:#fff;">Βοηθητικά</a>
</nav>
</header>
<div class="container">
<?php if(isset($_SESSION['warning'])){echo '<p style="color:red">'.$_SESSION['warning'].'</p>';unset($_SESSION['warning']);} ?>
<p><a href="record.php">Νέα Καταχώριση</a></p>
<div style="overflow-x:auto;">
<form method="get">
<table>
<thead>
<tr>
    <th>Ημερομηνία εγγραφής<br>🔍</th>
    <th>Οχημα<br>🔍</th>
    <th>Χιλιόμετρα</th>
    <th>Υπάλληλος</th>
    <th>Τόπος<br>🔍</th>
    <th>Είδος<br>🔍</th>
    <th>Περιγραφή</th>
    <th>Ημ/νία Επόμ. Service</th>
    <th>ΧΛΜ Επόμ. Service</th>
    <th>Σημειώσεις</th>
    <th>Αφορά Μπαταρία<br>🔍</th>
    <th>Αφορά Ελαστικά<br>🔍</th>
    <th>Ενέργειες</th>
</tr>
<tr class="filters">
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
    <td></td>
    <td></td>
    <td></td>
    <td>
        <select name="f_battery" style="width:100%;">
            <option value="">--</option>
            <option value="ΝΑΙ" <?= $filter_battery=='ΝΑΙ'?'selected':'' ?>>ΝΑΙ</option>
            <option value="ΟΧΙ" <?= $filter_battery=='ΟΧΙ'?'selected':'' ?>>ΟΧΙ</option>
        </select>
    </td>
    <td>
        <select name="f_tires" style="width:100%;">
            <option value="">--</option>
            <option value="ΝΑΙ" <?= $filter_tires=='ΝΑΙ'?'selected':'' ?>>ΝΑΙ</option>
            <option value="ΟΧΙ" <?= $filter_tires=='ΟΧΙ'?'selected':'' ?>>ΟΧΙ</option>
        </select>
    </td>
    <td><button type="submit" style="background:green;color:#fff;">OK</button> <a href="index.php" title="RESET ΦΙΛΤΡΩΝ" style="text-decoration:none;">&#x21bb;</a></td>
</tr>
</thead>
<tbody>
<?php foreach ($records as $row): ?>
<?php
    $license = htmlspecialchars($row['license']);
    $licenseDisp = '<strong>'.mb_substr($license,0,8,'UTF-8').'</strong>'.mb_substr($license,8,null,'UTF-8');
?>
<tr>
    <td data-label="Ημερομηνία εγγραφής"><?= fmt_date($row['movement_date']) ?></td>
    <td data-label="Οχημα"><?= $licenseDisp ?></td>
    <td data-label="Χιλιόμετρα" style="text-align:center;">
        <?= number_format($row['kilometers'],0,',','.') ?>
    </td>
    <td data-label="Υπάλληλος"><?= htmlspecialchars($row['employee']) ?></td>
    <td data-label="Τόπος"><?= htmlspecialchars($row['workplace']) ?></td>
    <td data-label="Είδος"><?= htmlspecialchars($row['work_type']) ?></td>
    <td data-label="Περιγραφή"><?= display_text($row['description']) ?></td>
    <td data-label="Ημ/νία Επόμ. Service"><?= $row['next_service_date']?fmt_date($row['next_service_date']):'' ?></td>
    <td data-label="ΧΛΜ Επόμ. Service" style="text-align:center;"><?= $row['next_service_km']?number_format($row['next_service_km'],0,',','.'):'' ?></td>
    <td data-label="Σημειώσεις"><?= display_text($row['user_notes']) ?></td>
    <td data-label="Αφορά Μπαταρία" style="text-align:center;"><?= htmlspecialchars($row['battery']) ?></td>
    <td data-label="Αφορά Ελαστικά" style="text-align:center;"><?= htmlspecialchars($row['tires']) ?></td>
    <td>
        <a href="record.php?id=<?= $row['id'] ?>" title="Επεξεργασία" style="text-decoration:none;">✏️</a>
        |
        <a href="?delete=<?= $row['id'] ?>" title="Διαγραφή" onclick="return confirm('Διαγραφή εγγραφής;');" style="color:red;text-decoration:none;">❌</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</form>
</div>
<div style="text-align:center;margin-top:0.5em;">
<?php
$paramsForLinks = $_GET;
if($page>1){
    $paramsForLinks['page']=$page-1;
    echo '<a href="?'.http_build_query($paramsForLinks).'">&#9664;</a> ';
}
if($page*$perPage < $totalRecords){
    $paramsForLinks['page']=$page+1;
    echo '<a href="?'.http_build_query($paramsForLinks).'">&#9654;</a>';
}
?>
</div>
<p style="margin-top:1em;"><a href="export.php?type=excel">Export Excel</a> | <a href="export.php?type=pdf">Export PDF</a></p>
</div>
<footer>ver 1.0  (c) 2025</footer>
<script>
document.addEventListener('click',function(e){
  if(e.target.classList.contains('expand')){
     e.target.style.display='none';
     var full=e.target.nextElementSibling; if(full) full.style.display='inline';
  }else if(e.target.classList.contains('collapse')){
     var full=e.target.parentElement; if(full) full.style.display='none';
     var expand=full.previousElementSibling; if(expand) expand.style.display='inline';
  }
});
</script>
</body>
</html>
