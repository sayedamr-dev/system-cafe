<?php
include 'db.php';
session_start();
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// 1. إضافة صنف جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item']) && $is_admin) {
    $item_name = trim($_POST['item_name']);
    $quantity = floatval($_POST['quantity']);
    $price = floatval($_POST['price']);

    if (!empty($item_name)) {
        $stmt = $conn->prepare("INSERT INTO inventory (item_name, quantity, price) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $item_name, $quantity, $price);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: inventory.php");
    exit();
}

// 2. حذف صنف
if (isset($_GET['delete']) && $is_admin) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM inventory WHERE id = $id");
    header("Location: inventory.php");
    exit();
}

$result = $conn->query("SELECT * FROM inventory ORDER BY id DESC");
include 'header.php';
?>

<h3 class="mb-4 fw-bold">إدارة المخزن</h3>

<?php if ($is_admin): ?>
<form method="POST" class="card p-4 mb-4 shadow-sm">
    <h5 class="mb-3 text-primary">إضافة صنف جديد للمخزن</h5>
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">اسم الصنف</label>
            <input type="text" name="item_name" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">الكمية</label>
            <input type="number" step="0.01" name="quantity" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">السعر</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="add_item" class="btn btn-primary w-100 fw-bold">إضافة</button>
        </div>
    </div>
</form>
<?php endif; ?>

<div class="card p-3 shadow-sm">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>اسم الصنف</th>
                <th>الكمية</th>
                <th>السعر</th>
                <?php if ($is_admin): ?><th>التحكم</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($row['item_name']); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['price']; ?> ج.م</td>
                <?php if ($is_admin): ?>
                <td>
                    <a href="inventory.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">
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