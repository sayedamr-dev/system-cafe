<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_debt']) && $is_admin) {
    $customer_name = trim($_POST['customer_name']);
    $new_total = floatval($_POST['total_amount']);
    $new_paid = floatval($_POST['paid_amount'] ?? 0);
    $description = $_POST['description'];
    $date = date('Y-m-d');

    $check_stmt = $conn->prepare("SELECT id, total_amount, paid_amount, remaining_amount FROM customer_debts WHERE customer_name = ? AND remaining_amount > 0 LIMIT 1");
    $check_stmt->bind_param("s", $customer_name);
    $check_stmt->execute();
    $result_check = $check_stmt->get_result();

    if ($result_check->num_rows > 0) {
        $old_row = $result_check->fetch_assoc();
        $old_id = $old_row['id'];
        
        $updated_total = $old_row['total_amount'] + $new_total;
        $updated_paid = $old_row['paid_amount'] + $new_paid;
        $updated_remaining = $updated_total - $updated_paid;
        $updated_status = ($updated_remaining <= 0) ? 'خالص' : (($updated_paid > 0) ? 'سداد جزئي' : 'معلق');
        $updated_desc = "تراكم ديون جديدة ($new_total): " . $description;

        $update_stmt = $conn->prepare("UPDATE customer_debts SET total_amount = ?, paid_amount = ?, remaining_amount = ?, description = ?, status = ?, date = ? WHERE id = ?");
        $update_stmt->bind_param("dddsssi", $updated_total, $updated_paid, $updated_remaining, $updated_desc, $updated_status, $date, $old_id);
        $update_stmt->execute();
        $update_stmt->close();

        if ($new_paid > 0) {
            $daily_desc = "سداد جزئي من دين العميل: " . $customer_name;
            $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
            $stmt_d->bind_param("sds", $daily_desc, $new_paid, $date);
            $stmt_d->execute();
            $stmt_d->close();
        }
    } else {
        $remaining_amount = $new_total - $new_paid;
        $status = ($remaining_amount <= 0) ? 'خالص' : (($new_paid > 0) ? 'سداد جزئي' : 'معلق');

        $insert_stmt = $conn->prepare("INSERT INTO customer_debts (customer_name, total_amount, paid_amount, remaining_amount, description, date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sddddss", $customer_name, $new_total, $new_paid, $remaining_amount, $description, $date, $status);
        $insert_stmt->execute();
        $insert_stmt->close();

        if ($new_paid > 0) {
            $daily_desc = "سداد دفعة من دين العميل: " . $customer_name;
            $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
            $stmt_d->bind_param("sds", $daily_desc, $new_paid, $date);
            $stmt_d->execute();
            $stmt_d->close();
        }
    }
    $check_stmt->close();
    header("Location: debts.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_debt']) && $is_admin) {
    $id = intval($_POST['id']);
    $customer_name = $_POST['customer_name'];
    $total_amount = floatval($_POST['total_amount']);
    $paid_amount = floatval($_POST['paid_amount']);
    $remaining_amount = $total_amount - $paid_amount;
    $description = $_POST['description'];
    $status = ($remaining_amount <= 0) ? 'خالص' : (($paid_amount > 0) ? 'سداد جزئي' : 'معلق');
    $pay_date = date('Y-m-d');

    $stmt = $conn->prepare("UPDATE customer_debts SET customer_name = ?, total_amount = ?, paid_amount = ?, remaining_amount = ?, description = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sddddsi", $customer_name, $total_amount, $paid_amount, $remaining_amount, $description, $status, $id);
    $stmt->execute();
    $stmt->close();

    if ($paid_amount > 0) {
        $daily_desc = "تسوية/سداد ديون العميل: " . $customer_name;
        $stmt_d = $conn->prepare("INSERT INTO daily (details, amount, date) VALUES (?, ?, ?)");
        $stmt_d->bind_param("sds", $daily_desc, $paid_amount, $pay_date);
        $stmt_d->execute();
        $stmt_d->close();
    }
    header("Location: debts.php");
    exit();
}

if (isset($_GET['delete']) && $is_admin) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM customer_debts WHERE id = $id");
    header("Location: debts.php");
    exit();
}

$edit_row = null;
if (isset($_GET['edit']) && $is_admin) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM customer_debts WHERE id = $edit_id");
    if ($edit_result->num_rows > 0) {
        $edit_row = $edit_result->fetch_assoc();
    }
}

$result = $conn->query("SELECT * FROM customer_debts ORDER BY id DESC");
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fa-solid fa-file-invoice-dollar text-warning me-2"></i> إدارة ديون العملاء</h2>
        <p class="text-muted mb-0">متابعة حسابات الديون والمدفوعات بدقة</p>
    </div>
</div>

<?php if ($is_admin): ?>
<div class="card p-4 mb-4">
    <h5 class="mb-3 text-warning fw-bold">
        <?php echo $edit_row ? 'تعديل وسداد دين العميل (# ' . $edit_row['id'] . ')' : 'تسجيل دين جديد / طلب جديد'; ?>
    </h5>
    <form method="POST">
        <?php if ($edit_row): ?>
            <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted">اسم العميل</label>
                <input type="text" name="customer_name" class="form-control" value="<?php echo $edit_row ? htmlspecialchars($edit_row['customer_name']) : ''; ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">المبلغ الإجمالي</label>
                <input type="number" step="0.01" name="total_amount" class="form-control" value="<?php echo $edit_row ? $edit_row['total_amount'] : ''; ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">المبلغ المدفوع (الواصل للخزنة)</label>
                <input type="number" step="0.01" name="paid_amount" class="form-control" value="<?php echo $edit_row ? $edit_row['paid_amount'] : '0'; ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <?php if ($edit_row): ?>
                    <button type="submit" name="update_debt" class="btn btn-success w-100 fw-bold">تحديث وسداد</button>
                <?php else: ?>
                    <button type="submit" name="add_debt" class="btn btn-primary w-100 fw-bold">حفظ الدين</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label text-muted">ملاحظات / وصف</label>
            <textarea name="description" class="form-control" rows="2"><?php echo $edit_row ? htmlspecialchars($edit_row['description']) : ''; ?></textarea>
        </div>
        <?php if ($edit_row): ?>
            <div class="mt-3">
                <a href="debts.php" class="btn btn-outline-light btn-sm">إلغاء التعديل</a>
            </div>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>اسم العميل</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <?php if ($is_admin): ?><th>التحكم</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td class="fw-bold text-white"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo $row['total_amount']; ?> ج.م</td>
                    <td class="text-success fw-bold"><?php echo $row['paid_amount']; ?> ج.م</td>
                    <td class="text-danger fw-bold"><?php echo $row['remaining_amount']; ?> ج.م</td>
                    <td>
                        <span class="badge bg-<?php echo ($row['status'] == 'خالص') ? 'success text-dark' : (($row['status'] == 'سداد جزئي') ? 'warning text-dark' : 'secondary'); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td><span class="text-muted small"><?php echo $row['date']; ?></span></td>
                    <?php if ($is_admin): ?>
                    <td>
                        <a href="debts.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning text-dark fw-bold me-1">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="debts.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>