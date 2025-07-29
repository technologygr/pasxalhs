<?php
require 'config.php';
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php');
    exit();
}
$type = isset($_GET['type']) ? $_GET['type'] : 'excel';

function fmt_date($d){
    return date('d-m-Y', strtotime($d));
}

// optional filters
$sql = 'SELECT movement_date, license, kilometers, employee, workplace, work_type, description, next_service_date, next_service_km, user_notes, battery, tires, id FROM car_jobs WHERE 1';
$params = [];
$filterKeys = [
    'f_date' => 'movement_date',
    'f_license' => 'license',
    'f_workplace' => 'workplace',
    'f_work_type' => 'work_type',
    'f_battery' => 'battery',
    'f_tires' => 'tires'
];
foreach($filterKeys as $param => $col){
    if(isset($_GET[$param]) && $_GET[$param] !== ''){
        $sql .= " AND $col=?";
        $params[] = $_GET[$param];
    }
}
$sql .= ' ORDER BY movement_date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

if($type === 'excel'){
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="records.xls"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo "<html><head><meta charset=\"UTF-8\"><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:4px;font-family:Arial,Helvetica,sans-serif;}</style></head><body>";
    echo "<table>";
    echo "<tr><th>Ημερομηνία εγγραφής</th><th>Οχημα</th><th>Χιλιόμετρα</th><th>Υπάλληλος</th><th>Τόπος</th><th>Είδος</th><th>Περιγραφή</th><th>Ημ/νία Επόμ. Service</th><th>ΧΛΜ Επόμ. Service</th><th>Σημειώσεις</th><th>Αφορά Μπαταρία</th><th>Αφορά Ελαστικά</th></tr>";
    foreach($records as $r){
        echo '<tr>';
        echo '<td>'.fmt_date($r['movement_date']).'</td>';
        echo '<td>'.htmlspecialchars($r['license']).'</td>';
        echo '<td>'.number_format($r['kilometers'],0,',','.') .'</td>';
        echo '<td>'.htmlspecialchars($r['employee']).'</td>';
        echo '<td>'.htmlspecialchars($r['workplace']).'</td>';
        echo '<td>'.htmlspecialchars($r['work_type']).'</td>';
        echo '<td>'.nl2br(htmlspecialchars($r['description'])).'</td>';
        echo '<td>'.($r['next_service_date']?fmt_date($r['next_service_date']):'').'</td>';
        echo '<td>'.($r['next_service_km']?number_format($r['next_service_km'],0,',','.'):'' ).'</td>';
        echo '<td>'.nl2br(htmlspecialchars($r['user_notes'])).'</td>';
        echo '<td>'.htmlspecialchars($r['battery']).'</td>';
        echo '<td>'.htmlspecialchars($r['tires']).'</td>';
        echo '</tr>';
    }
    echo "</table></body></html>";
    exit();
}

$width = 842; // landscape A4
$height = 595;
$margin = 20;
$rowHeight = 20;
$columns = ['Ημερομηνία εγγραφής','Οχημα','Χιλιόμετρα','Υπάλληλος','Τόπος','Είδος','Περιγραφή','Ημ/νία Επόμ. Service','ΧΛΜ Επόμ. Service','Σημειώσεις','Αφορά Μπαταρία','Αφορά Ελαστικά'];
$colCount = count($columns);
$usableWidth = $width - $margin*2;
$colWidth = $usableWidth / $colCount;

function pdf_escape($str){
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $str);
}

$rows = [];
$rows[] = $columns;
foreach($records as $r){
    $rows[] = [
        fmt_date($r['movement_date']),
        $r['license'],
        number_format($r['kilometers'],0,',','.'),
        $r['employee'],
        $r['workplace'],
        $r['work_type'],
        str_replace(["\n","\r"], ' ', $r['description']),
        $r['next_service_date']?fmt_date($r['next_service_date']):'',
        $r['next_service_km']?number_format($r['next_service_km'],0,',','.'):'',
        str_replace(["\n","\r"], ' ', $r['user_notes']),
        $r['battery'],
        $r['tires']
    ];
}

$content = "";
$y = $height - $margin - $rowHeight;

foreach($rows as $row){
    $x = $margin;
    for($i=0;$i<$colCount;$i++){
        $text = pdf_escape($row[$i]);
        $content .= "$x $y $colWidth $rowHeight re S\n"; // cell border
        $content .= "BT /F1 10 Tf ".($x+2)." ".($y+5)." Td (".$text.") Tj ET\n";
        $x += $colWidth;
    }
    $y -= $rowHeight;
}

$len = strlen($content);
$objects = [];
$objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
$objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 $width $height] /Contents 5 0 R >>\nendobj\n";
$objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
$objects[] = "5 0 obj\n<< /Length $len >>\nstream\n$content\nendstream\nendobj\n";

$out = "%PDF-1.3\n";
$offsets = [];
foreach($objects as $obj){
    $offsets[] = strlen($out);
    $out .= $obj;
}
$xref = strlen($out);
$out .= "xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
for($i=0;$i<count($offsets);$i++){
    $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
}
$out .= "trailer\n<< /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="records.pdf"');
header('Content-Length: '.strlen($out));

echo $out;
?>
