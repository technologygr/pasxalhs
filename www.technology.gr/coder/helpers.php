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
    body{font-family:Arial,sans-serif;margin:0;padding:1em;background:#f4f4f4;}
    h1{text-align:center;}
    ul{list-style:none;padding:0;max-width:300px;margin:0 auto;}
    li{margin:0.5em 0;}
    a{display:block;padding:0.5em;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;text-align:center;}
</style>
</head>
<body>
<h1>Βοηθητικές Λίστες</h1>
<ul>
    <li><a href="manage.php?entity=licenses">Πινακίδες Οχημάτων</a></li>
    <li><a href="manage.php?entity=employees">Υπάλληλοι</a></li>
    <li><a href="manage.php?entity=workplaces">Τόποι Εργασίας</a></li>
</ul>
<p style="text-align:center;"><a href="index.php" style="background:#ccc;color:#000;">Επιστροφή</a></p>
</body>
</html>
