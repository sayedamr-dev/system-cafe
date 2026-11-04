<?php
include 'db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل اليومية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="index.php" class="btn btn-secondary mb-3">العودة للرئيسية</a>
        <h2>تفاصيل الحساب اليومي</h2>
        <div class="card p-4 shadow-sm bg-white mt-3">
            <p class="text-muted">هنا يتم عرض تفاصيل الورديات وحركة المبيعات التفصيلية اليومية.</p>
        </div>
    </div>
</body>
</html>