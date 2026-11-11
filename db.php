<?php
$host = "localhost";
$dbname = "motor_shop_db";
$username = "root";
$password = ""; // Default XAMPP password is usually blank.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed. Make sure MySQL is running and import database.sql.");
}
?>
