<?php
require 'config.php';
session_start();
if(!isset($_SESSION['logged_in'])){header('Location: index.php');exit();}

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$firstDay = mktime(0,0,0,$month,1,$year);
$daysInMonth = date('t',$firstDay);

$stmt = $pdo->prepare('SELECT id,movement_date,license FROM car_jobs WHERE MONTH(movement_date)=? AND YEAR(movement_date)=?');
$stmt->execute([$month,$year]);
$records = [];
while($r=$stmt->fetch()){
    $day = (int)date('j',strtotime($r['movement_date']));
    $records[$day][] = $r;
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
body{font-family:Arial,sans-serif;margin:0;padding:1em;}
table{border-collapse:collapse;width:100%;}
th,td{border:1px solid #ccc;padding:5px;height:80px;vertical-align:top;}
th{background:#f0f0f0;}
@media(max-width:600px){th,td{height:auto;font-size:12px;}}
nav a{margin-right:10px;}
</style>
</head>
<body>
<nav><a href="index.php">Αρχική</a></nav>
<h2 style="text-align:center;"><?= sprintf('%02d/%04d',$month,$year) ?></h2>
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
    echo '</td>';
    $d++;$i++;}
for(;$i<=7;$i++)echo '<td></td>';
?>
</tr>
</table>
</body>
</html>
