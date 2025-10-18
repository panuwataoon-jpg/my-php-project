<?php
require_once 'config.php';
checkLogin();

// ===== เพิ่มอุปกรณ์ใหม่ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $equipment_name = $conn->real_escape_string($_POST['equipment_name']);
    $equipment_code = $conn->real_escape_string($_POST['equipment_code']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $model = $conn->real_escape_string($_POST['model']);
    $type_id = (int)$_POST['type_id'];
    $quantity = (int)$_POST['quantity'];
    $description = $conn->real_escape_string($_POST['description']);
    $image_url = '';

    // อัปโหลดรูป
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allow) && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    $sql = "INSERT INTO equipments (equipment_code, equipment_name, type_id, brand, model, quantity, available_quantity, description, image_url)
            VALUES ('$equipment_code', '$equipment_name', $type_id, '$brand', '$model', $quantity, $quantity, '$description', '$image_url')";

    if ($conn->query($sql)) {
        echo "<script>alert('เพิ่มอุปกรณ์สำเร็จ!'); location.href='".$_SERVER['PHP_SELF']."';</script>";
        exit();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: {$conn->error}');</script>";
    }
}

// ====== ฟิลเตอร์เดือน/ปี ======
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$export        = $_GET['export'] ?? '';

$start_date = "$current_year-" . str_pad($current_month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
$end_date   = date('Y-m-t 23:59:59', strtotime($start_date));

$where = "WHERE b.borrow_date BETWEEN '$start_date' AND '$end_date'";
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    $where .= " AND b.user_id = {$_SESSION['user_id']}";
}

// ====== ดึงข้อมูลการยืม ======
$sql = "
SELECT b.*, e.equipment_name, e.equipment_code, e.brand, e.model, e.image_url,
       u.full_name, checker.full_name AS checker_name
FROM borrowing b
JOIN equipments e ON b.equipment_id = e.equipment_id
JOIN users u ON b.user_id = u.user_id
LEFT JOIN users checker ON b.checked_by = checker.user_id
$where
ORDER BY b.borrow_date DESC
";
$borrowings = $conn->query($sql);

// ====== สถิติ ======
$stats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status = 'borrowed') AS borrowed,
        SUM(status = 'pending_return') AS pending,
        SUM(status = 'returned') AS returned,
        SUM(status = 'overdue') AS overdue
    FROM borrowing 
    WHERE borrow_date BETWEEN '$start_date' AND '$end_date'
")->fetch_assoc();

// ====== ดึงประเภทอุปกรณ์ ======
$types = $conn->query("SELECT * FROM equipment_types ORDER BY type_name");

// ====== ส่งออก CSV ======
if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=borrowing_report_{$current_year}_{$current_month}.csv");
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($out, ['รหัส', 'ชื่ออุปกรณ์', 'ยี่ห้อ', 'รุ่น', 'ผู้ยืม', 'จำนวน', 'วันที่ยืม', 'กำหนดคืน', 'วันที่คืน', 'สถานะ']);
    while ($r = $borrowings->fetch_assoc()) {
        fputcsv($out, [
            $r['equipment_code'], $r['equipment_name'], $r['brand'], $r['model'],
            $r['full_name'], $r['quantity'], $r['borrow_date'], $r['expected_return_date'],
            $r['actual_return_date'] ?: '-', $r['status']
        ]);
    }
    fclose($out);
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานการยืม-คืนอุปกรณ์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
 <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9ff;
        }
        .badge-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .btn-action {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-box-seam"></i> ระบบยืม-คืนอุปกรณ์
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipments.php">
                            <i class="bi bi-laptop"></i> อุปกรณ์
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="borrowing.php">
                            <i class="bi bi-arrow-left-right"></i> ยืม-คืน
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="materials.php">
                            <i class="bi bi-box"></i> เบิกวัสดุ
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_user.php">
                            <i class="bi bi-people"></i> ผู้ใช้
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="borrow_report.php">
                            <i class="bi bi-people"></i> รายงานการยืม-คืนอุปกรณ์
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> โปรไฟล์</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="container py-4">
    <h3 class="mb-3">รายงานการยืม-คืนอุปกรณ์ (<?php echo "$current_month / $current_year"; ?>)</h3>

    <!-- ตัวกรอง -->
    <form class="row g-2 mb-3">
        <div class="col-auto">
            <select name="month" class="form-select">
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?=$m==$current_month?'selected':''?>><?=$m?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <input type="number" name="year" value="<?=$current_year?>" class="form-control" style="width:100px">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">แสดงผล</button>
            <a href="?month=<?=$current_month?>&year=<?=$current_year?>&export=csv" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> ส่งออก CSV
            </a>
        </div>
    </form>

    <!-- ปุ่มเพิ่ม -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>รายการยืม-คืน</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> เพิ่มอุปกรณ์ & เบิกวัสดุ
        </button>
    </div>

    <!-- สถิติ -->
    <div class="card p-3 mb-3">
        <div>ทั้งหมด: <?=$stats['total']?> | 
            กำลังยืม: <?=$stats['borrowed']?> | 
            รอตรวจสอบ: <?=$stats['pending']?> | 
            คืนแล้ว: <?=$stats['returned']?> | 
            เกินกำหนด: <?=$stats['overdue']?>
        </div>
    </div>

    <!-- ตาราง -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ภาพ</th><th>รหัส</th><th>ชื่ออุปกรณ์</th><th>ผู้ยืม</th>
                    <th>วันที่ยืม</th><th>กำหนดคืน</th><th>วันที่คืน</th><th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
            <?php if($borrowings->num_rows): while($r=$borrowings->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($r['image_url'] && file_exists($r['image_url'])): ?>
                            <img src="<?=$r['image_url']?>" width="60" height="60" class="rounded">
                        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                    </td>
                    <td><?=$r['equipment_code']?></td>
                    <td><?=$r['equipment_name']?></td>
                    <td><?=$r['full_name']?></td>
                    <td><?=$r['borrow_date']?></td>
                    <td><?=$r['expected_return_date']?></td>
                    <td><?=$r['actual_return_date'] ?: '-'?></td>
                    <td>
                        <?php
                        $map = [
                            'borrowed' => 'กำลังยืม',
                            'pending_return' => 'รอตรวจสอบ',
                            'returned' => 'คืนแล้ว',
                            'overdue' => 'เกินกำหนด'
                        ];
                        echo $map[$r['status']] ?? $r['status'];
                        ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal เพิ่มอุปกรณ์ -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">เพิ่มอุปกรณ์ & เบิกวัสดุ</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">ชื่ออุปกรณ์</label>
              <input name="equipment_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">รหัส</label>
              <input name="equipment_code" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ประเภท</label>
              <select name="type_id" class="form-select" required>
                <option value="">-- เลือกประเภท --</option>
                <?php while($t=$types->fetch_assoc()): ?>
                    <option value="<?=$t['type_id']?>"><?=$t['type_name']?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ยี่ห้อ</label>
              <input name="brand" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">รุ่น</label>
              <input name="model" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">จำนวน</label>
              <input type="number" name="quantity" min="1" value="1" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold">รายละเอียดเพิ่มเติม</label>
              <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">รูปภาพ</label>
              <input type="file" name="image" class="form-control" accept="image/*">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" name="add_equipment" class="btn btn-success">
            <i class="bi bi-save"></i> บันทึก
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
