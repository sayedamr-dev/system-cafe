<?php
// تضمين الاتصال بقاعدة البيانات
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // التحقق من جدول المستخدمين
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($password == $row['password'] || password_verify($password, $row['password'])) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['username'] = $row['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'كلمة المرور غير صحيحة!';
            }
        } else {
            $error = 'اسم المستخدم غير موجود!';
        }
    } else {
        $error = 'خطأ في الاستعلام عن القاعدة!';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول المدير</title>
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
    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="brand-mark"><i class="fa-solid fa-mug-hot"></i></span>
                Café Elite
            </div>
            <h1 class="auth-title">تسجيل دخول المدير</h1>
            <p class="auth-sub">سجّل دخولك لإضافة البيانات وتعديلها.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center fw-semibold py-2 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label" for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="form-control" required autocomplete="username" autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">دخول</button>
                <div class="text-center">
                    <a href="index.php" class="auth-back">العودة للرئيسية (مشاهدة فقط)</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
