<?php
require 'config.php';

echo "Ξεκίνημα setup...<br>\n";

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch();
}

// main records table
echo "Δημιουργία πίνακα car_jobs...<br>\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS car_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_date DATE NOT NULL,
    license VARCHAR(20) NOT NULL,
    kilometers INT NOT NULL,
    employee VARCHAR(100) NOT NULL,
    workplace VARCHAR(50) NOT NULL,
    work_type VARCHAR(100) NOT NULL,
    description TEXT
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Πίνακας car_jobs ΟΚ.<br>\n";

$extraColumns = [
    'next_service_date DATE DEFAULT NULL',
    'next_service_km INT DEFAULT NULL',
    'user_notes TEXT',
    "battery VARCHAR(3) NOT NULL DEFAULT 'ΟΧΙ'",
    "tires VARCHAR(3) NOT NULL DEFAULT 'ΟΧΙ'"
];
foreach ($extraColumns as $def) {
    preg_match('/^([^ ]+)/', $def, $m);
    $col = $m[1];
    echo "Έλεγχος στήλης $col...<br>\n";
    if (!columnExists($pdo, 'car_jobs', $col)) {
        $pdo->exec("ALTER TABLE car_jobs ADD $def");
        echo "Προστέθηκε η στήλη $col.<br>\n";
    } else {
        echo "Η στήλη $col υπάρχει ήδη.<br>\n";
    }
}

// helper tables
$pdo->exec("CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Έλεγχος πίνακα licenses...<br>\n";
if (!$pdo->query('SELECT COUNT(*) FROM licenses')->fetchColumn()) {
    $pdo->exec("INSERT INTO licenses (name) VALUES ('11111'),('222222'),('333333')");
    echo "Προστέθηκαν default licenses.<br>\n";
} else {
    echo "Ο πίνακας licenses έχει ήδη δεδομένα.<br>\n";
}

$pdo->exec("CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Έλεγχος πίνακα employees...<br>\n";
if (!$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn()) {
    $pdo->exec("INSERT INTO employees (name) VALUES ('Υπάλληλος 1'),('Υπάλληλος 2'),('Υπάλληλος 3')");
    echo "Προστέθηκαν default employees.<br>\n";
} else {
    echo "Ο πίνακας employees έχει ήδη δεδομένα.<br>\n";
}

$pdo->exec("CREATE TABLE IF NOT EXISTS workplaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Έλεγχος πίνακα workplaces...<br>\n";
if (!$pdo->query('SELECT COUNT(*) FROM workplaces')->fetchColumn()) {
    $pdo->exec("INSERT INTO workplaces (name) VALUES ('Εντός ΔΑΑΘ'),('Εξωτερικό Συνεργείο'),('ΥΤΕΒΕ')");
    echo "Προστέθηκαν default workplaces.<br>\n";
} else {
    echo "Ο πίνακας workplaces έχει ήδη δεδομένα.<br>\n";
}

$pdo->exec("CREATE TABLE IF NOT EXISTS work_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Έλεγχος πίνακα work_types...<br>\n";
if (!$pdo->query('SELECT COUNT(*) FROM work_types')->fetchColumn()) {
    $pdo->exec("INSERT INTO work_types (name) VALUES
        ('Εκτακτη βλάβη'),
        ('Προγραμματισμένο Service'),
        ('Προγραμματισμένος έλεγχος'),
        ('Αλλο γεγονός')");
    echo "Προστέθηκαν default work types.<br>\n";
} else {
    echo "Ο πίνακας work_types έχει ήδη δεδομένα.<br>\n";
}

echo "Setup complete.<br>\n";
?>
