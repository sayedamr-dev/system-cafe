<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_month = date('Y-m');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_daily']) && $is_admin) {
    $amount = floatval($_POST['amount']);
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $details = "إيراد مبيعات يومية";

    if ($amount != 0) {
        $stmt = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $details, $amount, $date);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: daily.php?date=" . $date);
    exit();
}

if (isset($_GET['delete']) && $is_admin) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM daily WHERE id = $id");
    header("Location: daily.php");
    exit();
}

// حسابات الإيرادات
$stmt_sales = $conn->prepare("SELECT SUM(amount) as total_sales FROM daily WHERE date = ?");
$stmt_sales->bind_param("s", $selected_date);
$stmt_sales->execute();
$total_sales_day = $stmt_sales->get_result()->fetch_assoc()['total_sales'] ?? 0;
$stmt_sales->close();

$stmt_month = $conn->prepare("SELECT SUM(amount) as month_total FROM daily WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt_month->bind_param("s", $current_month);
$stmt_month->execute();
$total_month_revenue = $stmt_month->get_result()->fetch_assoc()['month_total'] ?? 0;
$stmt_month->close();

$result = $conn->query("SELECT * FROM daily ORDER BY date DESC, id DESC");
include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h3 class="fw-bold">سجل الحركة اليومية والتقارير</h3>
    </div>
    <div class="col-md-4 text-end">
        <div class="card bg-dark text-white p-2 shadow-sm">
            <small>إيرادات شهر (<?php echo $current_month; ?>):</small>
            <h5 class="fw-bold text-warning mb-0"><?php echo number_format($total_month_revenue, 2); ?> ج.م</h5>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<form method="POST" class="card p-4 mb-4 shadow-sm">
    <h5 class="mb-3 text-primary">تسجيل إيراد يومي جديد</h5>
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="مثال: 1000">
        </div>
        <div class="col-md-4">
            <label class="form-label">التاريخ</label>
            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" name="add_daily" class="btn btn-primary w-100 fw-bold">إضافة للخزنة</button>
        </div>
    </div>
</form>
<?php endif; ?>

<div class="card p-3 shadow-sm">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>التفاصيل</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <?php if ($is_admin): ?><th>التحكم</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['details']); ?></td>
                <td class="fw-bold text-<?php echo ($row['amount'] >= 0) ? 'success' : 'danger'; ?>">
                    <?php echo $row['amount']; ?> ج.م
                </td>
                <td><span class="badge bg-secondary"><?php echo $row['date']; ?></span></td>
                <?php if ($is_admin): ?>
                <td>
                    <a href="daily.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">
                        <i class="fa-solid fa-trash"></i> حذف
                    </a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>