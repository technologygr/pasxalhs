<?php
require 'config.php';
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php');
    exit();
}
$type = isset($_GET['type']) ? $_GET['type'] : 'excel';

$stmt = $pdo->query('SELECT movement_date, license, kilometers, employee, workplace, work_type, description FROM car_jobs ORDER BY movement_date DESC, id DESC');
$records = $stmt->fetchAll();

if($type === 'excel'){
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="records.xls"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo "<html><head><meta charset=\"UTF-8\"><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:4px;font-family:Arial,Helvetica,sans-serif;}</style></head><body>";
    echo "<table>";
    echo "<tr><th>Ημερομηνία</th><th>Πινακίδα</th><th>Χιλιόμετρα</th><th>Υπάλληλος</th><th>Τόπος</th><th>Είδος</th><th>Περιγραφή</th></tr>";
    foreach($records as $r){
        echo '<tr>';
        echo '<td>'.htmlspecialchars($r['movement_date']).'</td>';
        echo '<td>'.htmlspecialchars($r['license']).'</td>';
        echo '<td>'.htmlspecialchars($r['kilometers']).'</td>';
        echo '<td>'.htmlspecialchars($r['employee']).'</td>';
        echo '<td>'.htmlspecialchars($r['workplace']).'</td>';
        echo '<td>'.htmlspecialchars($r['work_type']).'</td>';
        echo '<td>'.nl2br(htmlspecialchars($r['description'])).'</td>';
        echo '</tr>';
    }
    echo "</table></body></html>";
    exit();
}

$width = 842; // landscape A4
$height = 595;
$margin = 20;
$rowHeight = 20;
$columns = ['Ημερομηνία','Πινακίδα','Χιλιόμετρα','Υπάλληλος','Τόπος','Είδος','Περιγραφή'];
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
        $r['movement_date'],
        $r['license'],
        $r['kilometers'],
        $r['employee'],
        $r['workplace'],
        $r['work_type'],
        str_replace(["\n","\r"], ' ', $r['description'])
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
