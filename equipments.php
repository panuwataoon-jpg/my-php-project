<?php
require_once 'config.php';
checkLogin();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ค้นหาและกรอง
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? (int)$_GET['type'] : 0;

$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $search_param = "%$search%";
    $where .= " AND (e.equipment_name LIKE ? OR e.equipment_code LIKE ? OR e.brand LIKE ? OR e.model LIKE ?)";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss";
}

if ($type_filter > 0) {
    $where .= " AND e.type_id = ?";
    $params[] = $type_filter;
    $types .= (empty($types) ? "" : "i") . "i";
}

$sql = "SELECT e.*, et.type_name FROM equipments e 
        LEFT JOIN equipment_types et ON e.type_id = et.type_id 
        $where ORDER BY e.created_at DESC";

$stmt = $conn->prepare($sql);
$result = false;

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    error_log("SQL Error in equipments.php: " . $conn->error);
}

$types_query = $conn->query("SELECT * FROM equipment_types ORDER BY type_name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อุปกรณ์ - ระบบยืม-คืนอุปกรณ์</title>
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
        .search-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .equipment-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            height: 100%;
        }
        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .equipment-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
            position: relative;
        }
        .equipment-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .badge-available {
            background: #43e97b;
            color: white;
        }
        .btn-action {
            border-radius: 8px;
            font-weight: 500;
        }
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
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
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="equipments.php">
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
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> จัดการ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="manage_equipments.php">จัดการอุปกรณ์</a></li>
                            <li><a class="dropdown-item" href="manage_materials.php">จัดการวัสดุ</a></li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="manage_users.php">จัดการผู้ใช้</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
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
        
        <!-- ค้นหา -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">ค้นหา</label>
                    <input type="text" class="form-control" name="search" placeholder="ชื่ออุปกรณ์, รหัส, ยี่ห้อ, รุ่น..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">ประเภท</label>
                    <select class="form-select" name="type">
                        <option value="">ทั้งหมด</option>
                        <?php 
                        if ($types_query):
                            $types_query->data_seek(0);
                            while($type = $types_query->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $type['type_id']; ?>" <?php echo $type_filter == $type['type_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php 
                            endwhile; 
                        endif;
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-action">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <?php if (!empty($search) || $type_filter > 0): ?>
                        <a href="equipments.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> ล้างการค้นหา
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
<?php
        $total_equipments = $conn->query("SELECT COUNT(*) as count FROM equipments")->fetch_assoc()['count'];
        $available_equipments = $conn->query("SELECT SUM(available_quantity) as count FROM equipments WHERE status = 'available'")->fetch_assoc()['count'] ?? 0;
        ?>
        <?php if ($total_equipments > 0): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-laptop"></i> อุปกรณ์ทั้งหมด</h5>
                        <h2><?php echo $total_equipments; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-check-circle"></i> พร้อมใช้งาน</h5>
                        <h2><?php echo $available_equipments; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <br>
        <!-- รายการอุปกรณ์ -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while($equipment = $result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card equipment-card h-100">
                        <?php if ($equipment['image_url'] && file_exists($equipment['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($equipment['image_url']); ?>" class="equipment-image" alt="<?php echo htmlspecialchars($equipment['equipment_name']); ?>">
                        <?php else: ?>
                        <div class="equipment-image">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($equipment['equipment_name']); ?></h6>
                                <?php if ($equipment['available_quantity'] > 0): ?>
                                    <span class="badge badge-available fs-6">ว่าง: <?php echo $equipment['available_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6">เต็ม</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted mb-2">
                                <small><i class="bi bi-tag"></i> <?php echo htmlspecialchars($equipment['equipment_code']); ?></small>
                            </p>
                            <?php if (!empty($equipment['brand'])): ?>
                            <p class="mb-1"><small><strong>ยี่ห้อ:</strong> <?php echo htmlspecialchars($equipment['brand']); ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($equipment['model'])): ?>
                            <p class="mb-2"><small><strong>รุ่น:</strong> <?php echo htmlspecialchars($equipment['model']); ?></small></p>
                            <?php endif; ?>
                            <p class="mb-3 flex-grow-1">
                                <small><span class="badge bg-info"><?php echo htmlspecialchars($equipment['type_name'] ?? 'ไม่ระบุ'); ?></span></small>
                            </p>
                            <div class="d-grid gap-2 mt-auto">
                                <?php if ($_SESSION['role'] === 'user'): ?>
                                    <?php if ($equipment['available_quantity'] > 0 && $equipment['status'] === 'available'): ?>
                                        <a href="borrow_equipment.php?id=<?php echo $equipment['equipment_id']; ?>" class="btn btn-primary btn-action">
                                            <i class="bi bi-cart-plus"></i> ยืมอุปกรณ์
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-action" disabled>
                                            <i class="bi bi-x-circle"></i> ไม่ว่าง
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                    <a href="manage_equipments.php" class="btn btn-outline-primary btn-action btn-sm">
                                        <i class="bi bi-gear"></i> จัดการ
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-action" disabled>
                                        <i class="bi bi-lock"></i> ต้องการสิทธิ์
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <i class="bi bi-inbox display-1"></i>
                <h5 class="mt-3">ไม่พบข้อมูลอุปกรณ์</h5>
                <p class="text-muted">กรุณาลองค้นหาใหม่หรือติดต่อผู้ดูแลระบบ</p>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <a href="manage_equipments.php" class="btn btn-primary btn-action">
                    <i class="bi bi-plus-circle"></i> เพิ่มอุปกรณ์ใหม่
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- สถิติ -->
     

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>