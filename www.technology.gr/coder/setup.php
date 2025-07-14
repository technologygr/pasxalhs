<?php
require 'config.php';

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch();
}

// main records table
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
    if (!columnExists($pdo, 'car_jobs', $col)) {
        $pdo->exec("ALTER TABLE car_jobs ADD $def");
    }
}

// helper tables
$pdo->exec("CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if (!$pdo->query('SELECT COUNT(*) FROM licenses')->fetchColumn()) {
    $pdo->exec("INSERT INTO licenses (name) VALUES ('11111'),('222222'),('333333')");
}

$pdo->exec("CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if (!$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn()) {
    $pdo->exec("INSERT INTO employees (name) VALUES ('Υπάλληλος 1'),('Υπάλληλος 2'),('Υπάλληλος 3')");
}

$pdo->exec("CREATE TABLE IF NOT EXISTS workplaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if (!$pdo->query('SELECT COUNT(*) FROM workplaces')->fetchColumn()) {
    $pdo->exec("INSERT INTO workplaces (name) VALUES ('Εντός ΔΑΑΘ'),('Εξωτερικό Συνεργείο'),('ΥΤΕΒΕ')");
}

echo 'Setup complete.';
?>
