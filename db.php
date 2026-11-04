<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'cafe_db';

$conn = new mysqli($host, $user, $password, $dbname);

// التأكد من عدم وجود أخطاء في الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ضبط الترميز لغة عربية
$conn->set_charset("utf8");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>