<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// جلب إحصائيات سريعة للرئيسية
$today = date('Y-m-d');
$current_month = date('Y-m');

// إيراد اليوم
$res_day = $conn->query("SELECT SUM(amount) as total FROM daily WHERE date = '$today'");
$today_revenue = $res_day->fetch_assoc()['total'] ?? 0;

// إيراد الشهر
$res_month = $conn->query("SELECT SUM(amount) as total FROM daily WHERE DATE_FORMAT(date, '%Y-%m') = '$current_month'");
$month_revenue = $res_month->fetch_assoc()['total'] ?? 0;

// إجمالي ديون العملاء المتبقية
$res_debts = $conn->query("SELECT SUM(remaining_amount) as total FROM customer_debts WHERE remaining_amount > 0");
$total_debts = $res_debts->fetch_assoc()['total'] ?? 0;

include 'header.php';
?>

<div class="row g-4 align-items-start mb-4">
    <div class="col-lg-5">
        <h1 class="page-title">أهلاً بك في <?php echo $cafe_name; ?></h1>
        <p class="page-sub mb-4">ملخص حسابات اليوم، وتحته أقسام النظام.</p>

        <div class="ticket-wrap">
            <section class="ticket" aria-label="ملخص الحسابات">
                <header class="ticket-head">
                    <div class="ticket-brand"><?php echo $cafe_name; ?></div>
                    <div class="ticket-meta"><?php echo date('Y-m-d'); ?></div>
                </header>
                <dl class="ticket-lines">
                    <div class="ticket-line is-primary">
                        <dt>إيراد الخزنة اليوم</dt>
                        <span class="leader" aria-hidden="true"></span>
                        <dd><?php echo number_format($today_revenue, 2); ?> ج.م</dd>
                    </div>
                    <div class="ticket-line">
                        <dt>إيرادات الشهر</dt>
                        <span class="leader" aria-hidden="true"></span>
                        <dd><?php echo number_format($month_revenue, 2); ?> ج.م</dd>
                    </div>
                    <div class="ticket-line is-owed">
                        <dt>ديون العملاء المتبقية</dt>
                        <span class="leader" aria-hidden="true"></span>
                        <dd><?php echo number_format($total_debts, 2); ?> ج.م</dd>
                    </div>
                </dl>
                <footer class="ticket-foot">
                    <?php echo $is_admin ? 'مسجّل دخول كمدير' : 'وضع المشاهدة فقط'; ?>
                </footer>
            </section>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="row g-3">
            <?php
            $shortcuts = [
                ['daily.php',          'fa-cash-register',        'الحركة اليومية', 'تسجيل الإيرادات ومتابعة سجل كل يوم'],
                ['expenses.php',       'fa-receipt',              'المصاريف',       'تسجيل المصروفات اليومية'],
                ['debts.php',          'fa-hand-holding-dollar',  'الديون',         'ديون العملاء والمبالغ المدفوعة'],
                ['inventory.php',      'fa-boxes-stacked',        'المخزن',         'الأصناف والكميات والأسعار'],
                ['suppliers.php',      'fa-truck-field',          'الموردين',       'حسابات الموردين والأقساط'],
                ['monthly_report.php', 'fa-chart-column',         'التقرير الشهري', 'صافي الشهر وتفاصيل كل يوم'],
            ];
            foreach ($shortcuts as $s): ?>
                <div class="col-sm-6">
                    <a class="shortcut" href="<?php echo $s[0]; ?>">
                        <span class="shortcut-icon"><i class="fa-solid <?php echo $s[1]; ?>"></i></span>
                        <span>
                            <span class="shortcut-title"><?php echo $s[2]; ?></span>
                            <span class="shortcut-desc"><?php echo $s[3]; ?></span>
                        </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$is_admin): ?>
            <div class="mt-4">
                <a href="login.php" class="btn btn-primary px-4"><i class="fa-solid fa-right-to-bracket me-2"></i> تسجيل الدخول كمدير</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
