<?php
require 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS car_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_date DATE NOT NULL,
    license VARCHAR(20) NOT NULL,
    kilometers INT NOT NULL,
    employee VARCHAR(100) NOT NULL,
    workplace VARCHAR(50) NOT NULL,
    work_type VARCHAR(100) NOT NULL,
    description TEXT
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$pdo->exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$pdo->exec($sql);
if (!$pdo->query('SELECT COUNT(*) FROM licenses')->fetchColumn()) {
    $pdo->exec("INSERT INTO licenses (name) VALUES ('11111'),('222222'),('333333')");
}

$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$pdo->exec($sql);
if (!$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn()) {
    $pdo->exec("INSERT INTO employees (name) VALUES ('Υπάλληλος 1'),('Υπάλληλος 2'),('Υπάλληλος 3')");
}

$sql = "CREATE TABLE IF NOT EXISTS workplaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$pdo->exec($sql);
if (!$pdo->query('SELECT COUNT(*) FROM workplaces')->fetchColumn()) {
    $pdo->exec("INSERT INTO workplaces (name) VALUES ('Εντός ΔΑΑΘ'),('Εξωτερικό Συνεργείο'),('ΥΤΕΒΕ')");
}

echo "Tables created or already exist.";
?>
