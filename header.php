<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// اسم الكافيه — غيّره من هنا فقط وهيتغير في كل الصفحات
$cafe_name = 'Café Elite';

// الصفحة الحالية لتمييزها في القائمة
$current_page = basename($_SERVER['PHP_SELF']);
$nav_items = [
    'index.php'          => 'الرئيسية',
    'daily.php'          => 'الحركة اليومية',
    'expenses.php'       => 'المصاريف',
    'debts.php'          => 'الديون',
    'inventory.php'      => 'المخزن',
    'suppliers.php'      => 'الموردين',
    'monthly_report.php' => 'التقرير الشهري',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cafe_name; ?> - نظام الإدارة</title>
    <!-- تطبيق الثيم المحفوظ قبل الرسم لتفادي الوميض -->
    <script>
        (function () {
            try {
                var t = localStorage.getItem('cafe_theme') || 'dark';
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo @filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="brand-mark"><i class="fa-solid fa-mug-hot"></i></span>
                <?php echo $cafe_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="فتح القائمة">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php foreach ($nav_items as $file => $label): ?>
                        <li class="nav-item">
                            <a class="nav-link<?php echo ($current_page === $file) ? ' active' : ''; ?>"
                               href="<?php echo $file; ?>"<?php echo ($current_page === $file) ? ' aria-current="page"' : ''; ?>><?php echo $label; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-2">
                    <li class="nav-item">
                        <button id="themeToggleBtn" class="theme-btn" type="button" title="تغيير الوضع" aria-label="تغيير الوضع">
                            <i class="fa-solid fa-moon" id="themeIcon"></i>
                        </button>
                    </li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <span class="badge bg-warning px-3 py-2">المدير</span>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger btn-sm" href="logout.php">خروج</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm px-3" href="login.php">دخول المدير</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mb-5">
