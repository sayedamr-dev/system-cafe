<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $title = trim($_POST['title']);
    $amount = floatval($_POST['amount']);
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    if (!empty($title) && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (title, amount, date) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $title, $amount, $date);
        $stmt->execute();
        $stmt->close();

        $daily_details = "صرف: " . $title;
        $negative_amount = -$amount;
        $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
        $stmt_d->bind_param("sds", $daily_details, $negative_amount, $date);
        $stmt_d->execute();
        $stmt_d->close();
    }
    header("Location: expenses.php");
    exit();
}

if (isset($_GET['delete']) && $is_admin) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM expenses WHERE id = $id");
    header("Location: expenses.php");
    exit();
}

$result = $conn->query("SELECT * FROM expenses ORDER BY date DESC, id DESC");
include 'header.php';
?>

<h3 class="mb-4 fw-bold">إدارة المصاريف اليومية</h3>

<?php if ($is_admin): ?>
<form method="POST" class="card p-4 mb-4 shadow-sm">
    <h5 class="mb-3 text-danger">تسجيل مصروف جديد</h5>
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">بند المصروف</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">التاريخ</label>
            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-danger w-100 fw-bold">حفظ وخصم</button>
        </div>
    </div>
</form>
<?php endif; ?>

<div class="card p-3 shadow-sm">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>البند</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <?php if ($is_admin): ?><th>التحكم</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td class="text-danger fw-bold">-<?php echo $row['amount']; ?> ج.م</td>
                <td><span class="badge bg-secondary"><?php echo $row['date']; ?></span></td>
                <?php if ($is_admin): ?>
                <td>
                    <a href="expenses.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">
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