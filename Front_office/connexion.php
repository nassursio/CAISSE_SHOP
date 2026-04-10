<?php
$pdo = new PDO('mysql:host=localhost;dbname=caisse_shop;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);