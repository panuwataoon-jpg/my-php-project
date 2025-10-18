<?php
require_once 'config.php';
checkLogin();

// สร้างคำขอเบิกวัสดุ
if (isset($_POST['request_material'])) {
    $material_id = (int)$_POST['material_id'];
    $quantity = (int)$_POST['quantity'];
    $purpose = trim($_POST['purpose']);
    $user_id = $_SESSION['user_id'];
    $requisition_date = date('Y-m-d H:i:s');
    
    // ตรวจสอบข้อมูล
    if ($quantity <= 0 || empty($purpose)) {
        setAlert('กรุณากรอกข้อมูลให้ครบถ้วน', 'danger');
    } else {
        $material = $conn->query("SELECT quantity FROM materials WHERE material_id = $material_id")->fetch_assoc();
        if ($material && $material['quantity'] >= $quantity) {
            $sql = "INSERT INTO material_requisition (material_id, user_id, quantity, requisition_date, purpose, status) 
                    VALUES ($material_id, $user_id, $quantity, '$requisition_date', ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $purpose);
            if ($stmt->execute()) {
                setAlert('ส่งคำขอเบิกวัสดุเรียบร้อยแล้ว รอการอนุมัติ', 'success');
            } else {
                setAlert('เกิดข้อผิดพลาด: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        } else {
            setAlert('จำนวนที่ต้องการมากกว่าสต็อกที่มี', 'danger');
        }
    }
}

// อนุมัติคำขอ (Admin/Staff)
if (isset($_POST['approve_request']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')) {
    $requisition_id = (int)$_POST['requisition_id'];
    $approved_by = $_SESSION['user_id'];
    
    $conn->begin_transaction();
    try {
        $requisition = $conn->query("SELECT mr.material_id, mr.quantity FROM material_requisition mr WHERE mr.requisition_id = $requisition_id")->fetch_assoc();
        $material = $conn->query("SELECT quantity FROM materials WHERE material_id = {$requisition['material_id']}")->fetch_assoc();
        
        if ($material && $material['quantity'] >= $requisition['quantity']) {
            $conn->query("UPDATE material_requisition SET status = 'approved', approved_by = $approved_by, approved_date = NOW() WHERE requisition_id = $requisition_id");
            $new_quantity = $material['quantity'] - $requisition['quantity'];
            $conn->query("UPDATE materials SET quantity = $new_quantity WHERE material_id = {$requisition['material_id']}");
            $conn->commit();
            setAlert('อนุมัติคำขอเรียบร้อยแล้ว', 'success');
        } else {
            $conn->rollback();
            setAlert('วัสดุมีจำนวนไม่เพียงพอ', 'danger');
        }
    } catch (Exception $e) {
        $conn->rollback();
        setAlert('เกิดข้อผิดพลาดในการอนุมัติ: ' . $e->getMessage(), 'danger');
    }
}

// ปฏิเสธคำขอ (Admin/Staff)
if (isset($_POST['reject_request']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')) {
    $requisition_id = (int)$_POST['requisition_id'];
    $approved_by = $_SESSION['user_id'];
    
    $sql = "UPDATE material_requisition SET status = 'rejected', approved_by = ?, approved_date = NOW() WHERE requisition_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $approved_by, $requisition_id);
    if ($stmt->execute()) {
        setAlert('ปฏิเสธคำขอเรียบร้อยแล้ว', 'info');
    } else {
        setAlert('เกิดข้อผิดพลาด: ' . $stmt->error, 'danger');
    }
    $stmt->close();
}

// ดึงข้อมูลวัสดุ
$materials = $conn->query("SELECT m.*, et.type_name FROM materials m 
                          LEFT JOIN equipment_types et ON m.type_id = et.type_id 
                          ORDER BY m.created_at DESC");

// ดึงข้อมูลคำขอ
$where = "WHERE 1=1";
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    $where .= " AND mr.user_id = {$_SESSION['user_id']}";
}

$requisitions = $conn->query("SELECT mr.*, m.material_name, m.unit, u.full_name, 
                              a.full_name as approver_name
                              FROM material_requisition mr 
                              JOIN materials m ON mr.material_id = m.material_id 
                              JOIN users u ON mr.user_id = u.user_id 
                              LEFT JOIN users a ON mr.approved_by = a.user_id 
                              $where ORDER BY mr.requisition_date DESC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เบิกวัสดุ - ระบบยืม-คืนอุปกรณ์</title>
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
        .material-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .material-card:hover {
            transform: translateY(-5px);
        }
        .material-image {
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            border-radius: 15px 15px 0 0;
            position: relative;
            overflow: hidden;
        }
        .material-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .low-stock {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }
        .btn-action {
            border-radius: 8px;
            font-weight: 500;
        }
        .no-image-placeholder {
            background: rgba(255,255,255,0.1);
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
        
        <!-- วัสดุ -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-box"></i> รายการวัสดุ</h5>
            </div>
            <div class="card-body">
                <?php if ($materials && $materials->num_rows > 0): ?>
                <div class="row g-4">
                    <?php 
                    $materials->data_seek(0);
                    while($material = $materials->fetch_assoc()): 
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card material-card <?php echo $material['quantity'] <= $material['min_quantity'] ? 'low-stock border-danger' : ''; ?>">
                            <?php if ($material['image_url'] && file_exists($material['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($material['image_url']); ?>" class="material-image" alt="<?php echo htmlspecialchars($material['material_name']); ?>">
                            <?php else: ?>
                            <div class="material-image no-image-placeholder">
                                <i class="bi bi-box"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($material['material_name']); ?></h6>
                                <p class="text-muted mb-2">
                                    <small><i class="bi bi-tag"></i> <?php echo htmlspecialchars($material['material_code']); ?></small>
                                </p>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">คงเหลือ:</span>
                                        <span class="badge bg-primary fs-6"><?php echo $material['quantity']; ?> <?php echo htmlspecialchars($material['unit']); ?></span>
                                    </div>
                                    <?php if ($material['quantity'] <= $material['min_quantity']): ?>
                                    <div class="alert alert-danger mt-2 mb-0 p-2" role="alert">
                                        <i class="bi bi-exclamation-triangle"></i> ใกล้หมดสต็อก
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($material['type_name']): ?>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-tag-fill"></i> <?php echo htmlspecialchars($material['type_name']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($_SESSION['role'] === 'user'): ?>
                                <?php if ($material['quantity'] > 0): ?>
                                <button class="btn btn-primary btn-action w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#requestModal<?php echo $material['material_id']; ?>">
                                    <i class="bi bi-plus-circle"></i> เบิกวัสดุ
                                </button>
                                <?php else: ?>
                                <button class="btn btn-secondary btn-action w-100" disabled>
                                    <i class="bi bi-x-circle"></i> หมดสต็อก
                                </button>
                                <?php endif; ?>
                                <?php elseif ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <div class="d-grid gap-2">
                                    <a href="manage_materials.php" class="btn btn-outline-primary btn-action btn-sm">
                                        <i class="bi bi-gear"></i> จัดการ
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Modal เบิกวัสดุ -->
                        <?php if ($_SESSION['role'] === 'user' && $material['quantity'] > 0): ?>
                        <div class="modal fade" id="requestModal<?php echo $material['material_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">เบิกวัสดุ: <?php echo htmlspecialchars($material['material_name']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="material_id" value="<?php echo $material['material_id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">จำนวนที่ต้องการ</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="quantity" min="1" max="<?php echo $material['quantity']; ?>" required>
                                                    <span class="input-group-text"><?php echo htmlspecialchars($material['unit']); ?></span>
                                                </div>
                                                <small class="text-muted">มีอยู่ <?php echo $material['quantity']; ?> <?php echo htmlspecialchars($material['unit']); ?></small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">วัตถุประสงค์ <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="purpose" rows="3" required 
                                                          placeholder="ระบุวัตถุประสงค์การใช้งาน..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button type="submit" name="request_material" class="btn btn-primary">
                                                <i class="bi bi-send"></i> ส่งคำขอ
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-boxes display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">ไม่มีข้อมูลวัสดุ</h5>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                    <a href="manage_materials.php" class="btn btn-primary btn-action">
                        <i class="bi bi-plus-circle"></i> เพิ่มวัสดุใหม่
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- คำขอเบิกวัสดุ -->
        <?php if ($requisitions && $requisitions->num_rows > 0): ?>
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> รายการคำขอเบิกวัสดุ</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>วัสดุ</th>
                                <th>ผู้ขอ</th>
                                <th>จำนวน</th>
                                <th>วันที่ขอ</th>
                                <th>วัตถุประสงค์</th>
                                <th>สถานะ</th>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <th class="text-center">จัดการ</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $requisitions->data_seek(0);
                            while($req = $requisitions->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($req['material_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($req['full_name']); ?></td>
                                <td><span class="badge bg-info"><?php echo $req['quantity']; ?> <?php echo htmlspecialchars($req['unit']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($req['requisition_date'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($req['purpose'], 0, 50)) . (strlen($req['purpose']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <?php
                                    $req_status_colors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'completed' => 'secondary'
                                    ];
                                    $req_status_text = [
                                        'pending' => 'รออนุมัติ',
                                        'approved' => 'อนุมัติแล้ว',
                                        'rejected' => 'ไม่อนุมัติ',
                                        'completed' => 'เสร็จสิ้น'
                                    ];
                                    $status = $req['status'] ?? 'pending';
                                    ?>
                                    <span class="badge bg-<?php echo $req_status_colors[$status]; ?>">
                                        <?php echo $req_status_text[$status]; ?>
                                    </span>
                                    <?php if ($req['approved_by']): ?>
                                    <br><small class="text-muted">โดย: <?php echo htmlspecialchars($req['approver_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <td class="text-center">
                                    <?php if ($req['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="requisition_id" value="<?php echo $req['requisition_id']; ?>">
                                        <button type="submit" name="approve_request" class="btn btn-success btn-sm btn-action me-1" 
                                                onclick="return confirm('อนุมัติคำขอนี้?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="submit" name="reject_request" class="btn btn-danger btn-sm btn-action" 
                                                onclick="return confirm('ปฏิเสธคำขอนี้?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>