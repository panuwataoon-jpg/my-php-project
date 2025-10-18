<?php
require_once 'config.php';
checkLogin();

// สถิติ
$total_equipment = $conn->query("SELECT COUNT(*) as count FROM equipments")->fetch_assoc()['count'];
$borrowed = $conn->query("SELECT COUNT(*) as count FROM borrowing WHERE status IN ('borrowed', 'pending_return')")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pending_returns = $conn->query("SELECT COUNT(*) as count FROM borrowing WHERE status = 'pending_return'")->fetch_assoc()['count'];

// ข้อมูลอุปกรณ์
$equipments = $conn->query("SELECT e.*, et.type_name FROM equipments e 
                           LEFT JOIN equipment_types et ON e.type_id = et.type_id 
                           ORDER BY e.created_at DESC LIMIT 10");

// ข้อมูลการยืมล่าสุด
$recent_borrows = $conn->query("SELECT b.*, e.equipment_name, u.full_name 
                               FROM borrowing b 
                               JOIN equipments e ON b.equipment_id = e.equipment_id 
                               JOIN users u ON b.user_id = u.user_id 
                               ORDER BY b.borrow_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก - ระบบยืม-คืนอุปกรณ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
<body>
    <!-- Navbar -->
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
        
        <!-- สถิติ -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card stat-card-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">อุปกรณ์ทั้งหมด</h6>
                            <h2 class="mb-0 mt-2"><?php echo $total_equipment; ?></h2>
                        </div>
                        <i class="bi bi-laptop" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">กำลังยืม</h6>
                            <h2 class="mb-0 mt-2"><?php echo $borrowed; ?></h2>
                        </div>
                        <i class="bi bi-arrow-left-right" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">ผู้ใช้ทั้งหมด</h6>
                            <h2 class="mb-0 mt-2"><?php echo $total_users; ?></h2>
                        </div>
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">รอตรวจสอบการคืน</h6>
                            <h2 class="mb-0 mt-2"><?php echo $pending_returns; ?></h2>
                        </div>
                        <i class="bi bi-clock-history" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- อุปกรณ์ล่าสุด -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-laptop"></i> อุปกรณ์ล่าสุด</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ชื่ออุปกรณ์</th>
                                        <th>ประเภท</th>
                                        <th>ว่าง/ทั้งหมด</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $equipments->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $row['equipment_code']; ?></strong></td>
                                        <td><?php echo $row['equipment_name']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $row['type_name']; ?></span></td>
                                        <td><strong><?php echo $row['available_quantity']; ?></strong> / <?php echo $row['quantity']; ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'available' => 'success',
                                                'borrowed' => 'warning',
                                                'maintenance' => 'secondary',
                                                'damaged' => 'danger'
                                            ];
                                            $status_text = [
                                                'available' => 'พร้อมใช้',
                                                'borrowed' => 'ถูกยืม',
                                                'maintenance' => 'ซ่อมบำรุง',
                                                'damaged' => 'ชำรุด'
                                            ];
                                            ?>
                                            <span class="badge badge-status bg-<?php echo $status_colors[$row['status']]; ?>">
                                                <?php echo $status_text[$row['status']]; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="equipments.php" class="btn btn-primary btn-action">
                            <i class="bi bi-eye"></i> ดูทั้งหมด
                        </a>
                    </div>
                </div>
            </div>

            <!-- การยืมล่าสุด -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> การยืมล่าสุด</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while($borrow = $recent_borrows->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo $borrow['equipment_name']; ?></h6>
                                    <?php
                                    $borrow_status_colors = [
                                        'borrowed' => 'primary',
                                        'pending_return' => 'warning',
                                        'returned' => 'success',
                                        'overdue' => 'danger'
                                    ];
                                    $borrow_status_text = [
                                        'borrowed' => 'กำลังยืม',
                                        'pending_return' => 'รอตรวจสอบ',
                                        'returned' => 'คืนแล้ว',
                                        'overdue' => 'เกินกำหนด'
                                    ];
                                    ?>
                                    <small><span class="badge bg-<?php echo $borrow_status_colors[$borrow['status']]; ?>">
                                        <?php echo $borrow_status_text[$borrow['status']]; ?>
                                    </span></small>
                                </div>
                                <p class="mb-1"><small><i class="bi bi-person"></i> <?php echo $borrow['full_name']; ?></small></p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo date('d/m/Y', strtotime($borrow['borrow_date'])); ?>
                                </small>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="borrowing.php" class="btn btn-primary btn-action">
                            <i class="bi bi-list"></i> ดูทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>