<?php
require 'config.php';
session_start();
if(!isset($_SESSION['logged_in'])){header('Location: index.php');exit();}

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$firstDay = mktime(0,0,0,$month,1,$year);
$daysInMonth = date('t',$firstDay);
$months_gr = ['', 'Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'];

$stmt = $pdo->prepare('SELECT id,movement_date,next_service_date,license FROM car_jobs WHERE (MONTH(movement_date)=? AND YEAR(movement_date)=?) OR (next_service_date IS NOT NULL AND MONTH(next_service_date)=? AND YEAR(next_service_date)=?)');
$stmt->execute([$month,$year,$month,$year]);
$records = $services = [];
while($r=$stmt->fetch()){
    $dayMove = (int)date('j',strtotime($r['movement_date']));
    $records[$dayMove][] = ['id'=>$r['id'],'license'=>$r['license']];
    if($r['next_service_date']){
        $dayServ = (int)date('j',strtotime($r['next_service_date']));
        $services[$dayServ][] = ['id'=>$r['id'],'license'=>$r['license']];
    }
}
$prevMonth = $month-1;$prevYear=$year;if($prevMonth<1){$prevMonth=12;$prevYear--;}
$nextMonth = $month+1;$nextYear=$year;if($nextMonth>12){$nextMonth=1;$nextYear++;}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ημερολόγιο</title>
<style>
body{font-family:Arial,sans-serif;margin:0;padding:0;display:flex;min-height:100vh;flex-direction:column;}
main{flex:1;}
header{padding:1em;background:#333;color:#fff;text-align:center;}
footer{padding:.3em;background:#333;color:#fff;font-size:12px;text-align:left;}
table{border-collapse:collapse;width:100%;}
th,td{border:1px solid #ccc;padding:5px;height:80px;vertical-align:top;width:14.28%;}
th{background:#f0f0f0;}
@media(max-width:600px){th,td{height:auto;font-size:12px;}}
nav a{margin-right:10px;}
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
<main>
<h2 style="text-align:center;"><?= mb_strtoupper($months_gr[$month], 'UTF-8').' '. $year ?></h2>
<div style="text-align:center;margin-bottom:1em;">
<a href="?month=<?=$prevMonth?>&year=<?=$prevYear?>">&laquo;</a>
<a href="?month=<?=$nextMonth?>&year=<?=$nextYear?>">&raquo;</a>
</div>
<table>
<tr><th>Δευ</th><th>Τρι</th><th>Τετ</th><th>Πεμ</th><th>Παρ</th><th>Σαβ</th><th>Κυρ</th></tr>
<?php
$dayOfWeek=date('N',$firstDay);echo '<tr>';for($i=1;$i<$dayOfWeek;$i++)echo '<td></td>';
$d=1;$i=$dayOfWeek;
while($d<=$daysInMonth){
    if($i==8){echo '</tr><tr>';$i=1;}
    echo '<td><strong>'.$d.'</strong><br>';
    if(isset($records[$d])){
        foreach($records[$d] as $rec){
            echo '<a href="record.php?id='.$rec['id'].'">'.htmlspecialchars($rec['license']).'</a><br>';
        }
    }
    if(isset($services[$d])){
        foreach($services[$d] as $rec){
            echo '<span style="color:green;font-weight:bold;">'.htmlspecialchars($rec['license']).'</span><br>';
        }
    }
    echo '</td>';
    $d++;$i++;}
for(;$i<=7;$i++)echo '<td></td>';
?>
</tr>
</table>
</main>
<footer>ver 1.0  (c) 2025</footer>
</body>
</html>
