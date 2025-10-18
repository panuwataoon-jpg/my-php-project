<?php
require_once 'config.php';
checkLogin();

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header('Location: index.php');
    exit();
}

// ตรวจสอบ column image_url
$check_column = $conn->query("SHOW COLUMNS FROM equipments LIKE 'image_url'");
$has_image_column = $check_column->num_rows > 0;

// เพิ่ม/แก้ไขอุปกรณ์
if (isset($_POST['save_equipment'])) {
    $equipment_id = isset($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : 0;
    $equipment_code = trim($_POST['equipment_code']);
    $equipment_name = trim($_POST['equipment_name']);
    $type_id = (int)$_POST['type_id'];
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $quantity = (int)$_POST['quantity'];
    $available_quantity = (int)$_POST['available_quantity'];
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description']);

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($equipment_code) || empty($equipment_name) || $type_id <= 0 || $quantity < 1) {
        setAlert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', 'danger');
        header('Location: manage_equipments.php');
        exit();
    }

    // ตรวจสอบ available_quantity ไม่เกิน quantity
    if ($available_quantity > $quantity) {
        setAlert('จำนวนว่างไม่สามารถมากกว่าจำนวนทั้งหมดได้', 'danger');
        header('Location: manage_equipments.php');
        exit();
    }

    // ตรวจสอบรหัสอุปกรณ์ซ้ำ
    $check_sql = "SELECT equipment_id FROM equipments WHERE equipment_code = ? AND equipment_id != ?";
    $check_code = $conn->prepare($check_sql);
    $check_code->bind_param("si", $equipment_code, $equipment_id);
    $check_code->execute();
    $check_code->store_result();
    if ($check_code->num_rows > 0) {
        setAlert('รหัสอุปกรณ์นี้มีอยู่แล้ว กรุณาใช้รหัสอื่น', 'danger');
        $check_code->close();
        header('Location: manage_equipments.php');
        exit();
    }
    $check_code->close();

    // อัพโหลดรูปภาพ
    $image_url = '';
    $has_new_image = false;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/equipments/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = $upload_path;
                $has_new_image = true;
            } else {
                setAlert('เกิดข้อผิดพลาดในการอัพโหลดไฟล์', 'danger');
                header('Location: manage_equipments.php');
                exit();
            }
        } else {
            setAlert('ไฟล์รูปภาพไม่ถูกต้องหรือขนาดใหญ่เกินไป (สูงสุด 5MB)', 'danger');
            header('Location: manage_equipments.php');
            exit();
        }
    } elseif (isset($_POST['old_image']) && !empty($_POST['old_image'])) {
        $image_url = $_POST['old_image'];
    }

    // ✅ เพิ่ม / แก้ไข อุปกรณ์
    if ($equipment_id > 0) {
        // 🔹 UPDATE
        $sql = "UPDATE equipments SET 
            equipment_code = ?, equipment_name = ?, type_id = ?, 
            brand = ?, model = ?, quantity = ?, available_quantity = ?, 
            status = ?, description = ?";

        $params = [
            $equipment_code, $equipment_name, $type_id,
            $brand, $model, $quantity, $available_quantity,
            $status, $description
        ];
        $types = "ssissiiss";

        if ($has_image_column && $has_new_image) {
            $sql .= ", image_url = ?";
            $params[] = $image_url;
            $types .= "s";
        }

        $sql .= ", updated_at = NOW() WHERE equipment_id = ?";
        $params[] = $equipment_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            setAlert('แก้ไขอุปกรณ์เรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $stmt->error, 'danger');
        }
        $stmt->close();
    } else {
        // 🔹 INSERT (แก้ syntax ถูกต้อง)
        $columns = "equipment_code, equipment_name, type_id, brand, model, quantity, available_quantity, status, description";
        $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [
            $equipment_code, $equipment_name, $type_id,
            $brand, $model, $quantity, $available_quantity,
            $status, $description
        ];
        $types = "ssissiiss";

        if ($has_image_column) {
            $columns .= ", image_url";
            $placeholders .= ", ?";
            $params[] = $image_url;
            $types .= "s";
        }

        $sql = "INSERT INTO equipments ($columns, created_at) VALUES ($placeholders, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            setAlert('เพิ่มอุปกรณ์เรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $stmt->error, 'danger');
        }
        $stmt->close();
    }

    header('Location: manage_equipments.php');
    exit();
}

// ดึงข้อมูลอุปกรณ์ทั้งหมด
$select_fields = $has_image_column ? "e.*, et.type_name" : "e.equipment_id, e.equipment_code, e.equipment_name, e.type_id, e.brand, e.model, e.quantity, e.available_quantity, e.status, e.description, et.type_name";
$equipments_query = "SELECT $select_fields FROM equipments e 
                    LEFT JOIN equipment_types et ON e.type_id = et.type_id 
                    ORDER BY e.created_at DESC";
$equipments = $conn->query($equipments_query);
$types = $conn->query("SELECT * FROM equipment_types ORDER BY type_name");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการอุปกรณ์ - ระบบยืม-คืนอุปกรณ์</title>
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
        .equipment-image-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .btn-action {
            border-radius: 8px;
            font-weight: 500;
        }
        .status-badge {
            font-size: 0.8em;
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
                        <a class="nav-link" href="index.php">
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> จัดการ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="manage_equipments.php">จัดการอุปกรณ์</a></li>
                            <li><a class="dropdown-item" href="manage_materials.php">จัดการวัสดุ</a></li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="manage_user.php">จัดการผู้ใช้</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php showAlert(); ?>
        
        <?php if (!$has_image_column): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            ระบบตรวจพบว่าไม่มี column <code>image_url</code> ในตาราง <code>equipments</code>
            <br>กรุณารัน SQL: <code>ALTER TABLE equipments ADD COLUMN image_url VARCHAR(255) DEFAULT NULL;</code>
            <br>เพื่อให้สามารถอัพโหลดรูปภาพได้
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="bi bi-laptop"></i> จัดการอุปกรณ์</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#equipmentModal" onclick="resetForm()">
                            <i class="bi bi-plus-circle"></i> เพิ่มอุปกรณ์
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">รูป</th>
                                <th width="12%">รหัส</th>
                                <th width="25%">ชื่ออุปกรณ์</th>
                                <th width="12%">ยี่ห้อ</th>
                                <th width="12%">รุ่น</th>
                                <th width="10%">ประเภท</th>
                                <th width="10%">ว่าง/ทั้งหมด</th>
                                <th width="8%">สถานะ</th>
                                <th width="8%" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($equipments && $equipments->num_rows > 0):
                                while($eq = $equipments->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                    // ตรวจสอบ image_url อย่างปลอดภัย
                                    $image_url = ($has_image_column && isset($eq['image_url']) && !empty($eq['image_url']) && file_exists($eq['image_url'])) 
                                               ? $eq['image_url'] : null;
                                    if ($image_url): ?>
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                             class="equipment-image-preview" 
                                             alt="<?php echo htmlspecialchars($eq['equipment_name']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="equipment-image-preview bg-secondary d-flex align-items-center justify-content-center text-white d-none">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="equipment-image-preview bg-secondary d-flex align-items-center justify-content-center text-white">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($eq['equipment_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($eq['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($eq['brand'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($eq['model'] ?? ''); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($eq['type_name'] ?? 'ไม่ระบุ'); ?></span></td>
                                <td>
                                    <strong><?php echo $eq['available_quantity']; ?></strong>/<?php echo $eq['quantity']; ?>
                                    <?php if ($eq['available_quantity'] == 0): ?>
                                        <span class="badge bg-danger ms-1">หมด</span>
                                    <?php endif; ?>
                                </td>
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
                                    $status_key = $eq['status'] ?? 'available';
                                    $color = $status_colors[$status_key] ?? 'secondary';
                                    $text = $status_text[$status_key] ?? 'ไม่ทราบ';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?> status-badge"><?php echo $text; ?></span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-1" onclick='editEquipment(<?php echo json_encode($eq); ?>)' title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="equipment_id" value="<?php echo $eq['equipment_id']; ?>">
                                        <button type="submit" name="delete_equipment" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('ยืนยันการลบอุปกรณ์? ข้อมูลจะไม่สามารถกู้คืนได้')" title="ลบ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="<?php echo $has_image_column ? '9' : '8'; ?>" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox"></i> ยังไม่มีข้อมูลอุปกรณ์
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มอุปกรณ์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="equipment_id" id="equipment_id">
                        <input type="hidden" name="old_image" id="old_image">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">รหัสอุปกรณ์ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="equipment_code" id="equipment_code" required maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ชื่ออุปกรณ์ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="equipment_name" id="equipment_name" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">ประเภท <span class="text-danger">*</span></label>
                                <select class="form-select" name="type_id" id="type_id" required>
                                    <option value="">เลือกประเภท</option>
                                    <?php 
                                    if ($types):
                                        $types->data_seek(0);
                                        while($type = $types->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                                    <?php 
                                        endwhile; 
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">ยี่ห้อ</label>
                                <input type="text" class="form-control" name="brand" id="brand" maxlength="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">รุ่น</label>
                                <input type="text" class="form-control" name="model" id="model" maxlength="100">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">จำนวนทั้งหมด <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">จำนวนว่าง <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="available_quantity" id="available_quantity" min="0" required>
                                <div class="form-text">ต้องไม่มากกว่าจำนวนทั้งหมด</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">สถานะ</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="available">พร้อมใช้</option>
                                    <option value="borrowed">ถูกยืม</option>
                                    <option value="maintenance">ซ่อมบำรุง</option>
                                    <option value="damaged">ชำรุด</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">รูปภาพ</label>
                            <?php if ($has_image_column): ?>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*" onchange="previewImage(this)">
                            <div class="form-text">ขนาดไฟล์ไม่เกิน 5MB (JPG, PNG, GIF) - สามารถเว้นว่างได้</div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> ไม่สามารถอัพโหลดรูปภาพได้ เนื่องจากไม่มี column image_url
                            </div>
                            <?php endif; ?>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">รายละเอียด</label>
                            <textarea class="form-control" name="description" id="description" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" name="save_equipment" class="btn btn-primary">
                            <i class="bi bi-save"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('modalTitle').textContent = 'เพิ่มอุปกรณ์';
            document.getElementById('equipment_id').value = '';
            document.getElementById('equipment_code').value = '';
            document.getElementById('equipment_name').value = '';
            document.getElementById('type_id').value = '';
            document.getElementById('brand').value = '';
            document.getElementById('model').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('available_quantity').value = '';
            document.getElementById('status').value = 'available';
            document.getElementById('description').value = '';
            document.getElementById('old_image').value = '';
            document.getElementById('image').value = '';
            document.getElementById('imagePreview').innerHTML = '';
        }
        
        function editEquipment(data) {
            document.getElementById('modalTitle').textContent = 'แก้ไขอุปกรณ์';
            document.getElementById('equipment_id').value = data.equipment_id || '';
            document.getElementById('equipment_code').value = data.equipment_code || '';
            document.getElementById('equipment_name').value = data.equipment_name || '';
            document.getElementById('type_id').value = data.type_id || '';
            document.getElementById('brand').value = data.brand || '';
            document.getElementById('model').value = data.model || '';
            document.getElementById('quantity').value = data.quantity || '';
            document.getElementById('available_quantity').value = data.available_quantity || '';
            document.getElementById('status').value = data.status || 'available';
            document.getElementById('description').value = data.description || '';
            document.getElementById('old_image').value = data.image_url || '';
            
            // Preview รูปภาพเก่า
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = '';
            if (data.image_url && data.image_url.trim() !== '') {
                imagePreview.innerHTML = 
                    '<div class="mb-2"><img src="' + data.image_url + '" class="img-thumbnail" style="max-width: 200px;" ' +
                    'onerror="this.parentNode.innerHTML=\'<small class=\\"text-muted\\">ไม่สามารถแสดงรูปภาพได้</small>\'">' +
                    '<small class="text-muted d-block mt-1">อัพโหลดไฟล์ใหม่เพื่อแทนที่</small></div>';
            }
            
            new bootstrap.Modal(document.getElementById('equipmentModal')).show();
        }
        
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('ขนาดไฟล์ใหญ่เกิน 5MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = 
                        '<div class="mb-2"><img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;">' +
                        '<small class="text-muted d-block mt-1">อัพโหลดไฟล์ใหม่แล้ว</small></div>';
                };
                reader.readAsDataURL(file);
            }
        }
        
        // ตรวจสอบ available_quantity เมื่อเปลี่ยน quantity
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            const availableInput = document.getElementById('available_quantity');
            
            if (quantityInput && availableInput) {
                quantityInput.addEventListener('input', function() {
                    const quantity = parseInt(this.value) || 0;
                    const available = parseInt(availableInput.value) || 0;
                    if (available > quantity) {
                        availableInput.value = quantity;
                    }
                });
                
                availableInput.addEventListener('input', function() {
                    const quantity = parseInt(quantityInput.value) || 0;
                    const available = parseInt(this.value) || 0;
                    if (available > quantity) {
                        this.value = quantity;
                    }
                });
            }
        });
    </script>
</body>
</html>