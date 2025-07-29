<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Βοηθητικά</title>
<style>
    body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f4f4f4;}
    header{padding:1em;background:#333;color:#fff;text-align:center;}
    footer{padding:.3em;background:#333;color:#fff;font-size:12px;text-align:left;}
    h1{text-align:center;}
    ul{list-style:none;padding:0;max-width:300px;margin:0 auto;}
    li{margin:0.5em 0;}
    ul li a{display:block;padding:0.5em;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;text-align:center;}
    header nav a{display:inline-block;margin-right:10px;color:#fff;text-decoration:none;background:none;padding:0;}
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
<h1>Βοηθητικές Λίστες</h1>
<ul>
    <li><a href="manage.php?entity=licenses">Πινακίδες Οχημάτων</a></li>
    <li><a href="manage.php?entity=employees">Υπάλληλοι</a></li>
    <li><a href="manage.php?entity=workplaces">Τόποι Εργασίας</a></li>
</ul>
<p style="text-align:center;"><a href="index.php" style="background:#ccc;color:#000;">Επιστροφή</a></p>
<footer>ver 1.0  (c) 2025</footer>
</body>
</html>
