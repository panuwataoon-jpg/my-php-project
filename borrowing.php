<?php
require_once 'config.php';
checkLogin();

// ✅ ส่งคำขอคืนอุปกรณ์ (User)
if (isset($_POST['request_return'])) {
    $borrow_id = (int)$_POST['borrow_id'];
    $return_notes = escape($_POST['return_notes']);
    $return_request_date = date('Y-m-d H:i:s');
    
    $conn->query("UPDATE borrowing SET 
                 status = 'pending_return',
                 return_request_date = '$return_request_date',
                 return_notes = '$return_notes'
                 WHERE borrow_id = $borrow_id");
    
    setAlert('ส่งคำขอคืนอุปกรณ์แล้ว รอเจ้าหน้าที่ตรวจสอบ', 'success');
}

// ✅ อนุมัติการคืนอุปกรณ์ (Admin/Staff)
if (isset($_POST['approve_return']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')) {
    $borrow_id = (int)$_POST['borrow_id'];
    $condition = escape($_POST['condition']);
    $actual_return_date = date('Y-m-d H:i:s');
    $checked_by = $_SESSION['user_id'];
    
    $borrow = $conn->query("SELECT * FROM borrowing WHERE borrow_id = $borrow_id")->fetch_assoc();
    
    if ($borrow) {
        $conn->query("UPDATE borrowing SET 
                     status = 'returned', 
                     actual_return_date = '$actual_return_date',
                     condition_on_return = '$condition',
                     checked_by = $checked_by
                     WHERE borrow_id = $borrow_id");
        
        $equipment = $conn->query("SELECT * FROM equipments WHERE equipment_id = {$borrow['equipment_id']}")->fetch_assoc();
        $new_available = $equipment['available_quantity'] + $borrow['quantity'];
        $conn->query("UPDATE equipments SET available_quantity = $new_available WHERE equipment_id = {$borrow['equipment_id']}");
        
        setAlert('อนุมัติการคืนอุปกรณ์เรียบร้อยแล้ว', 'success');
    }
}

// ✅ ดึงข้อมูลการยืม
$where = "WHERE 1=1";
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    $where .= " AND b.user_id = {$_SESSION['user_id']}";
}

$status_filter = isset($_GET['status']) ? escape($_GET['status']) : '';
if ($status_filter) {
    $where .= " AND b.status = '$status_filter'";
}

$borrowings = $conn->query("SELECT b.*, e.equipment_name, e.equipment_code, u.full_name,
                           checker.full_name as checker_name
                           FROM borrowing b 
                           JOIN equipments e ON b.equipment_id = e.equipment_id 
                           JOIN users u ON b.user_id = u.user_id 
                           LEFT JOIN users checker ON b.checked_by = checker.user_id
                           $where ORDER BY b.borrow_date DESC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการยืม-คืน - ระบบยืม-คืนอุปกรณ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9ff;
        }
        .btn-action {
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>
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

    <div class="container-fluid mt-4">
        <?php showAlert(); ?>
        
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col"><h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> รายการยืม-คืนอุปกรณ์</h5></div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex gap-2">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="">ทุกสถานะ</option>
                                <option value="borrowed" <?= $status_filter == 'borrowed' ? 'selected' : ''; ?>>กำลังยืม</option>
                                <option value="pending_return" <?= $status_filter == 'pending_return' ? 'selected' : ''; ?>>รอตรวจสอบการคืน</option>
                                <option value="returned" <?= $status_filter == 'returned' ? 'selected' : ''; ?>>คืนแล้ว</option>
                                <option value="overdue" <?= $status_filter == 'overdue' ? 'selected' : ''; ?>>เกินกำหนด</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>รหัส</th>
                                <th>ชื่ออุปกรณ์</th>
                                <th>ผู้ยืม</th>
                                <th>จำนวน</th>
                                <th>วันที่ยืม</th>
                                <th>กำหนดคืน</th>
                                <th>สภาพ</th>
                                <th>ผู้ตรวจสอบ</th>
                                <th>สถานะ</th>
                                <th class="text-center">อนุมัติการคืน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($borrowings->num_rows > 0): while($borrow = $borrowings->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $borrow['equipment_code']; ?></strong></td>
                                <td><?= $borrow['equipment_name']; ?></td>
                                <td><?= $borrow['full_name']; ?></td>
                                <td><span class="badge bg-info"><?= $borrow['quantity']; ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($borrow['borrow_date'])); ?></td>
                                <td><?= date('d/m/Y', strtotime($borrow['expected_return_date'])); ?></td>
                                <td>
                                    <?php
                                    $c_text = ['good'=>'ดี','need_repair'=>'ต้องซ่อม','damaged'=>'ชำรุด'];
                                    $c_color = ['good'=>'success','need_repair'=>'warning','damaged'=>'danger'];
                                    echo $borrow['condition_on_return']
                                        ? "<span class='badge bg-{$c_color[$borrow['condition_on_return']]}'>{$c_text[$borrow['condition_on_return']]}</span>"
                                        : "<span class='text-muted'>-</span>";
                                    ?>
                                </td>
                                <td><?= $borrow['checker_name'] ?: '<span class="text-muted">-</span>'; ?></td>
                                <td>
                                    <?php
                                    $s_text = ['borrowed'=>'กำลังยืม','pending_return'=>'รอตรวจสอบ','returned'=>'คืนแล้ว','overdue'=>'เกินกำหนด'];
                                    $s_color = ['borrowed'=>'primary','pending_return'=>'warning','returned'=>'success','overdue'=>'danger'];
                                    ?>
                                    <span class="badge bg-<?= $s_color[$borrow['status']]; ?>"><?= $s_text[$borrow['status']]; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($borrow['status'] === 'borrowed' && $_SESSION['role'] === 'user' && $_SESSION['user_id'] == $borrow['user_id']): ?>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal<?= $borrow['borrow_id']; ?>">
                                            <i class="bi bi-box-arrow-in-down"></i> ส่งคำขอคืน
                                        </button>
                                    <?php elseif ($borrow['status'] === 'pending_return' && ($_SESSION['role']==='admin'||$_SESSION['role']==='staff')): ?>
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal<?= $borrow['borrow_id']; ?>">
                                            <i class="bi bi-check-circle"></i> ตรวจสอบ
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal ส่งคำขอคืน (User) -->
                            <div class="modal fade" id="returnModal<?= $borrow['borrow_id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-box-arrow-in-down"></i> ส่งคำขอคืน</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="borrow_id" value="<?= $borrow['borrow_id']; ?>">
                                                <div class="alert alert-info">
                                                    <strong><?= $borrow['equipment_name']; ?></strong><br>
                                                    <small>รหัส: <?= $borrow['equipment_code']; ?></small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">หมายเหตุเพิ่มเติม</label>
                                                    <textarea class="form-control" name="return_notes" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" name="request_return" class="btn btn-warning">
                                                    <i class="bi bi-send"></i> ส่งคำขอคืน
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal ตรวจสอบ (Admin/Staff) -->
                            <?php if ($_SESSION['role']==='admin' || $_SESSION['role']==='staff'): ?>
                            <div class="modal fade" id="approveModal<?= $borrow['borrow_id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-clipboard-check"></i> ตรวจสอบการคืน</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="borrow_id" value="<?= $borrow['borrow_id']; ?>">
                                                <div class="alert alert-warning">
                                                    <strong><?= $borrow['equipment_name']; ?></strong> (<?= $borrow['equipment_code']; ?>)<br>
                                                    <small>ผู้ยืม: <?= $borrow['full_name']; ?></small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">สภาพอุปกรณ์</label>
                                                    <select class="form-select" name="condition" required>
                                                        <option value="good">ดี - ไม่มีความเสียหาย</option>
                                                        <option value="need_repair">ต้องซ่อม - มีความเสียหายเล็กน้อย</option>
                                                        <option value="damaged">ชำรุด - เสียหายมาก</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                <button type="submit" name="approve_return" class="btn btn-success">
                                                    <i class="bi bi-check-circle"></i> อนุมัติการคืน
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endwhile; else: ?>
                            <tr><td colspan="10" class="text-center py-5"><i class="bi bi-inbox" style="font-size:3rem;color:#ccc;"></i><p class="text-muted mt-2">ไม่มีข้อมูล</p></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
