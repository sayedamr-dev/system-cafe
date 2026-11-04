<?php
include 'db.php';
session_start();

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 1. جلب صافي إيرادات الشهر المختار
$stmt_total = $conn->prepare("SELECT SUM(amount) as net_revenue FROM daily WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt_total->bind_param("s", $selected_month);
$stmt_total->execute();
$net_revenue = $stmt_total->get_result()->fetch_assoc()['net_revenue'] ?? 0;
$stmt_total->close();

// 2. جلب الأيام المسجلة في الشهر مرتبة تنازلياً مع إجمالي كل يوم
$stmt_days = $conn->prepare("SELECT date, SUM(amount) as daily_total FROM daily WHERE DATE_FORMAT(date, '%Y-%m') = ? GROUP BY date ORDER BY date DESC");
$stmt_days->bind_param("s", $selected_month);
$stmt_days->execute();
$days_result = $stmt_days->get_result();

include 'header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fa-solid fa-calendar-days"></i> التقرير الشهري للإيرادات</h3>
        <p class="text-muted">متابعة الأيام، صافي الخزنة، واستعراض تفاصيل كل يوم بضغطة زر</p>
    </div>
    <div class="col-md-6 text-md-end">
        <form method="GET" class="d-inline-flex gap-2 align-items-center bg-white p-2 rounded shadow-sm">
            <label class="form-label mb-0 fw-bold">اختر الشهر:</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?php echo $selected_month; ?>">
            <button type="submit" class="btn btn-primary btn-sm fw-bold">عرض</button>
        </form>
    </div>
</div>

<!-- بطاقة صافي إيرادات الشهر -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-dark text-white p-4 shadow-sm border-0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="text-white-50 mb-1">صافي خزنة وأرباح شهر: <span class="text-warning"><?php echo $selected_month; ?></span></h5>
                    <h2 class="fw-bold text-<?php echo ($net_revenue >= 0) ? 'success' : 'danger'; ?> mb-0">
                        <?php echo number_format($net_revenue, 2); ?> ج.م
                    </h2>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <i class="fa-solid fa-wallet fa-3x text-warning opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- جدول ملخص أيام الشهر -->
<div class="card p-3 shadow-sm border-0">
    <h5 class="fw-bold mb-3 text-secondary">ملخص أيام الشهر (التاريخ يظهر مرة واحدة لكل يوم)</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>صافي اليوم</th>
                    <th>الإجراءات (التفاصيل والطباعة)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($days_result->num_rows > 0): ?>
                    <?php 
                    $counter = 1;
                    while($row = $days_result->fetch_assoc()): 
                        $day_date = $row['date'];
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td>
                            <span class="badge bg-secondary fs-6 px-3 py-2"><i class="fa-regular fa-calendar"></i> <?php echo $day_date; ?></span>
                        </td>
                        <td class="fw-bold fs-5 text-<?php echo ($row['daily_total'] >= 0) ? 'success' : 'danger'; ?>">
                            <?php echo number_format($row['daily_total'], 2); ?> ج.م
                        </td>
                        <td>
                            <!-- زرار التفاصيل (بيفتح تفاصيل اليوم ده في مودال أو يظهر تحته) -->
                            <button class="btn btn-sm btn-info text-white fw-bold px-3" type="button" data-bs-toggle="collapse" data-bs-target="#dayDetails<?php echo str_replace('-', '', $day_date); ?>">
                                <i class="fa-solid fa-list"></i> تفاصيل
                            </button>

                            <!-- زرار البرنت المخصص لليوم ده -->
                            <button class="btn btn-sm btn-dark fw-bold px-3" onclick="printDay('<?php echo $day_date; ?>')">
                                <i class="fa-solid fa-print"></i> برنت
                            </button>
                        </td>
                    </tr>
                    
                    <!-- صف منسدل (Collapse) بيعرض تفاصيل الحركات الخاصة باليوم ده لما المستخدم يدوس تفاصيل -->
                    <tr>
                        <td colspan="4" class="p-0 border-0">
                            <div class="collapse bg-light p-3 border-bottom" id="dayDetails<?php echo str_replace('-', '', $day_date); ?>">
                                <h6 class="fw-bold text-primary mb-2">حركات يوم: <?php echo $day_date; ?></h6>
                                <table class="table table-sm table-bordered bg-white mb-0">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>البيان / التفاصيل</th>
                                            <th>المبلغ</th>
                                            <th>النوع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // استعلام لجلب تفاصيل اليوم ده لوحده
                                        $stmt_sub = $conn->prepare("SELECT * FROM daily WHERE date = ?");
                                        $stmt_sub->bind_param("s", $day_date);
                                        $stmt_sub->execute();
                                        $sub_result = $stmt_sub->get_result();
                                        while($sub_row = $sub_result->fetch_assoc()):
                                            $is_exp = ($sub_row['amount'] < 0);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($sub_row['details']); ?></td>
                                            <td class="fw-bold text-<?php echo $is_exp ? 'danger' : 'success'; ?>">
                                                <?php echo number_format($sub_row['amount'], 2); ?> ج.م
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $is_exp ? 'danger' : 'success'; ?>">
                                                    <?php echo $is_exp ? 'مصروف' : 'إيراد'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php 
                                        endwhile;
                                        $stmt_sub->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">لا توجد حركات مسجلة في هذا الشهر حتى الآن.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- كود JavaScript بسيط لطباعة تفاصيل يوم معين عند الضغط على زر البرنت -->
<script>
function printDay(dayDate) {
    let detailRow = document.getElementById('dayDetails' + dayDate.replaceAll('-', ''));
    if (detailRow) {
        let originalContent = document.body.innerHTML;
        let printContent = `
            <div dir="rtl" style="font-family: Tahoma; padding: 20px;">
                <h2 style="text-align: center;">تقرير حركات يوم: ${dayDate}</h2>
                <hr>
                ${detailRow.innerHTML}
            </div>
        `;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // إعادة تحميل الصفحة عشان تترتب واجهة المستخدم تاني
    }
}
</script>

<?php 
$stmt_days->close();
include 'footer.php'; 
?>