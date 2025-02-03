<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=industrial_properties",
        "root",
        "1",
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
    );
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} 