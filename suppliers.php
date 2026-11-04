<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier']) && $is_admin) {
    $supplier_name = trim($_POST['supplier_name']);
    $phone = trim($_POST['phone']);
    $total_debt = floatval($_POST['total_debt'] ?? 0);
    $paid_amount = floatval($_POST['paid_amount'] ?? 0);
    $remaining_amount = $total_debt - $paid_amount;
    $description = trim($_POST['description']);
    $date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, phone, total_debt, paid_amount, remaining_amount, description, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdddss", $supplier_name, $phone, $total_debt, $paid_amount, $remaining_amount, $description, $date);
    $stmt->execute();
    $stmt->close();

    if ($paid_amount > 0) {
        $daily_details = "دفعة جزئية للمورد: " . $supplier_name;
        $negative_amount = -$paid_amount;
        $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
        $stmt_d->bind_param("sds", $daily_details, $negative_amount, $date);
        $stmt_d->execute();
        $stmt_d->close();
    }
    header("Location: suppliers.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_installment']) && $is_admin) {
    $supplier_id = intval($_POST['supplier_id']);
    $supplier_name = $_POST['supplier_name'];
    $installment = floatval($_POST['installment']);
    $pay_date = !empty($_POST['pay_date']) ? $_POST['pay_date'] : date('Y-m-d');

    if ($installment > 0) {
        $res = $conn->query("SELECT total_debt, paid_amount FROM suppliers WHERE id = $supplier_id");
        if ($row = $res->fetch_assoc()) {
            $new_paid = $row['paid_amount'] + $installment;
            $new_remaining = $row['total_debt'] - $new_paid;

            $stmt_up = $conn->prepare("UPDATE suppliers SET paid_amount = ?, remaining_amount = ? WHERE id = ?");
            $stmt_up->bind_param("ddi", $new_paid, $new_remaining, $supplier_id);
            $stmt_up->execute();
            $stmt_up->close();

            $daily_details = "سداد قسط للمورد: " . $supplier_name;
            $negative_amount = -$installment;
            $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
            $stmt_d->bind_param("sds", $daily_details, $negative_amount, $pay_date);
            $stmt_d->execute();
            $stmt_d->close();
        }
    }
    header("Location: suppliers.php");
    exit();
}

if (isset($_GET['delete']) && $is_admin) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM suppliers WHERE id = $id");
    header("Location: suppliers.php");
    exit();
}

$result = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h3 class="fw-bold"><i class="fa-solid fa-truck-field"></i> إدارة الموردين والأقساط</h3>
        <p class="text-muted">متابعة حسابات الموردين والأقساط</p>
    </div>
</div>

<?php if ($is_admin): ?>
<form method="POST" class="card p-4 mb-4 shadow-sm">
    <h5 class="mb-3 text-dark fw-bold">إضافة حساب مورد جديد</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">اسم المورد</label>
            <input type="text" name="supplier_name" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">رقم الهاتف</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">إجمالي الدين</label>
            <input type="number" step="0.01" name="total_debt" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">المدفوع مقدماً</label>
            <input type="number" step="0.01" name="paid_amount" class="form-control" value="0">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="add_supplier" class="btn btn-dark w-100 fw-bold">حفظ المورد</button>
        </div>
    </div>
</form>
<?php endif; ?>

<div class="card p-3 shadow-sm border-0">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>اسم المورد</th>
                <th>إجمالي الحساب</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
                <?php if ($is_admin): ?>
                <th>دفع قسط</th>
                <th>التحكم</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($row['supplier_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></small></td>
                <td><?php echo number_format($row['total_debt'], 2); ?> ج.م</td>
                <td class="text-success fw-bold"><?php echo number_format($row['paid_amount'], 2); ?> ج.م</td>
                <td class="text-danger fw-bold"><?php echo number_format($row['remaining_amount'], 2); ?> ج.م</td>
                
                <?php if ($is_admin): ?>
                <td>
                    <form method="POST" class="d-inline-flex gap-1 align-items-center">
                        <input type="hidden" name="supplier_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="supplier_name" value="<?php echo htmlspecialchars($row['supplier_name']); ?>">
                        <input type="number" step="0.01" name="installment" class="form-control form-control-sm" placeholder="المبلغ" style="width: 80px;" required>
                        <input type="date" name="pay_date" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" style="width: 110px;">
                        <button type="submit" name="pay_installment" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-plus"></i></button>
                    </form>
                </td>
                <td>
                    <a href="suppliers.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>