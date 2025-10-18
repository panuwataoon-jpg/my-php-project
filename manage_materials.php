<?php
require_once 'config.php';
checkLogin();

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header('Location: index.php');
    exit();
}

// เพิ่ม/แก้ไขวัสดุ
if (isset($_POST['save_material'])) {
    $material_id = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    $material_code = trim($_POST['material_code']);
    $material_name = trim($_POST['material_name']);
    $type_id = (int)$_POST['type_id'];
    $unit = trim($_POST['unit']);
    $quantity = (int)$_POST['quantity'];
    $min_quantity = (int)$_POST['min_quantity'];
    $description = trim($_POST['description']);
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($material_code) || empty($material_name) || $type_id <= 0 || $quantity < 0) {
        setAlert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', 'danger');
        header('Location: manage_materials.php');
        exit();
    }
    
    // ตรวจสอบรหัสวัสดุซ้ำ (ถ้าเพิ่มใหม่)
    if ($material_id == 0) {
        $check_code = $conn->prepare("SELECT material_id FROM materials WHERE material_code = ?");
        if ($check_code) {
            $check_code->bind_param("s", $material_code);
            $check_code->execute();
            $result = $check_code->get_result();
            if ($result->num_rows > 0) {
                setAlert('รหัสวัสดุนี้มีอยู่แล้ว กรุณาใช้รหัสอื่น', 'danger');
                $check_code->close();
                header('Location: manage_materials.php');
                exit();
            }
            $check_code->close();
        } else {
            setAlert('เกิดข้อผิดพลาดในการตรวจสอบรหัสวัสดุ', 'danger');
            header('Location: manage_materials.php');
            exit();
        }
    }
    
    // จัดการรูปภาพ
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/materials/';
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                setAlert('ไม่สามารถสร้างโฟลเดอร์สำหรับอัพโหลดได้', 'danger');
                header('Location: manage_materials.php');
                exit();
            }
        }
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        
        // ตรวจสอบประเภทไฟล์และขนาด
        if (!in_array($file_extension, $allowed_types)) {
            setAlert('ประเภทไฟล์ไม่ถูกต้อง กรุณาอัพโหลดไฟล์ JPG, PNG หรือ GIF เท่านั้น', 'danger');
            header('Location: manage_materials.php');
            exit();
        }
        
        if ($file_size > 5 * 1024 * 1024) { // 5MB
            setAlert('ขนาดไฟล์ใหญ่เกินไป (สูงสุด 5MB)', 'danger');
            header('Location: manage_materials.php');
            exit();
        }
        
        // สร้างชื่อไฟล์ใหม่
        $new_filename = uniqid('mat_') . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // อัพโหลดไฟล์
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $image_url = $upload_path;
        } else {
            setAlert('เกิดข้อผิดพลาดในการอัพโหลดไฟล์ กรุณาลองใหม่อีกครั้ง', 'danger');
            header('Location: manage_materials.php');
            exit();
        }
    } elseif (isset($_POST['old_image']) && !empty($_POST['old_image'])) {
        $image_url = $_POST['old_image'];
    }
    
    try {
        if ($material_id > 0) {
            // แก้ไข - UPDATE
            $sql = "UPDATE materials SET 
                    material_code = ?, material_name = ?, type_id = ?, 
                    unit = ?, quantity = ?, min_quantity = ?, description = ?, 
                    image_url = ?, updated_at = NOW()
                    WHERE material_id = ?";
            
            // ลบรูปเก่าถ้ามีรูปใหม่
            if (!empty($image_url) && isset($_POST['old_image']) && !empty($_POST['old_image']) && 
                $_POST['old_image'] !== $image_url && file_exists($_POST['old_image'])) {
                unlink($_POST['old_image']);
            }
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // 9 parameters: s s i s i i s s i
                $stmt->bind_param("ssisiissi",
                    $material_code, 
                    $material_name, 
                    $type_id, 
                    $unit, 
                    $quantity, 
                    $min_quantity, 
                    $description, 
                    $image_url, 
                    $material_id
                );
                if ($stmt->execute()) {
                    setAlert('แก้ไขวัสดุเรียบร้อยแล้ว', 'success');
                } else {
                    setAlert('เกิดข้อผิดพลาดในการแก้ไข: ' . $stmt->error, 'danger');
                }
                $stmt->close();
            } else {
                throw new Exception('ไม่สามารถเตรียมคำสั่ง SQL ได้: ' . $conn->error);
            }
        } else {
            // เพิ่มใหม่ - INSERT
            $sql = "INSERT INTO materials (material_code, material_name, type_id, unit, quantity, min_quantity, description, image_url, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('ไม่สามารถเตรียมคำสั่ง INSERT ได้: ' . $conn->error);
            }
            
            // 8 parameters: s s i s i i s s
            $stmt->bind_param("ssisiiss",
                $material_code, 
                $material_name, 
                $type_id, 
                $unit, 
                $quantity, 
                $min_quantity, 
                $description, 
                $image_url
            );
            
            if ($stmt->execute()) {
                setAlert('เพิ่มวัสดุเรียบร้อยแล้ว', 'success');
            } else {
                setAlert('เกิดข้อผิดพลาดในการเพิ่ม: ' . $stmt->error, 'danger');
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        setAlert('เกิดข้อผิดพลาด: ' . $e->getMessage(), 'danger');
    }
    
    header('Location: manage_materials.php');
    exit();
}

// ลบวัสดุ
if (isset($_POST['delete_material'])) {
    $material_id = (int)$_POST['material_id'];
    
    // ตรวจสอบว่ามีการเบิกอยู่หรือไม่
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM material_requisition WHERE material_id = ? AND status IN ('pending', 'approved')");
    if ($check_stmt) {
        $check_stmt->bind_param("i", $material_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $check_stmt->close();
        
        if ($count > 0) {
            setAlert('ไม่สามารถลบได้ เนื่องจากมีคำขอเบิกวัสดุที่ยังไม่เสร็จสิ้น', 'danger');
            header('Location: manage_materials.php');
            exit();
        }
    }
    
    // ดึงข้อมูลรูปภาพ
    $img_stmt = $conn->prepare("SELECT image_url FROM materials WHERE material_id = ?");
    if ($img_stmt) {
        $img_stmt->bind_param("i", $material_id);
        $img_stmt->execute();
        $result = $img_stmt->get_result();
        $material = $result->fetch_assoc();
        $img_stmt->close();
        
        if ($material && $material['image_url'] && file_exists($material['image_url'])) {
            unlink($material['image_url']);
        }
    }
    
    // ลบข้อมูล
    $delete_stmt = $conn->prepare("DELETE FROM materials WHERE material_id = ?");
    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $material_id);
        if ($delete_stmt->execute()) {
            setAlert('ลบวัสดุเรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาดในการลบ: ' . $delete_stmt->error, 'danger');
        }
        $delete_stmt->close();
    }
    
    header('Location: manage_materials.php');
    exit();
}

// ดึงข้อมูล
$materials = $conn->query("SELECT m.*, et.type_name FROM materials m 
                          LEFT JOIN equipment_types et ON m.type_id = et.type_id 
                          ORDER BY m.created_at DESC");

$types_result = $conn->query("SELECT * FROM equipment_types ORDER BY type_name");

// ตรวจสอบว่ามีประเภทหรือไม่
if (!$types_result || $types_result->num_rows == 0) {
    setAlert('ยังไม่มีประเภทวัสดุ กรุณาเพิ่มประเภทก่อน', 'warning');
}
$types = $types_result;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการวัสดุ - ระบบยืม-คืนอุปกรณ์</title>
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
        .material-image-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
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
                            <li><a class="dropdown-item" href="manage_equipments.php">จัดการอุปกรณ์</a></li>
                            <li><a class="dropdown-item active" href="manage_materials.php">จัดการวัสดุ</a></li>
                            <li><a class="dropdown-item" href="manage_user.php">จัดการผู้ใช้</a></li>
                        </ul>
                    </li>
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
        
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="bi bi-box"></i> จัดการวัสดุ</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#materialModal" onclick="resetForm()">
                            <i class="bi bi-plus-circle"></i> เพิ่มวัสดุ
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
                                <th width="25%">ชื่อวัสดุ</th>
                                <th width="15%">ประเภท</th>
                                <th width="12%">คงเหลือ</th>
                                <th width="10%">หน่วย</th>
                                <th width="10%">จำนวนขั้นต่ำ</th>
                                <th width="8%" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($materials && $materials->num_rows > 0):
                                $materials->data_seek(0);
                                while($mat = $materials->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <?php if ($mat['image_url'] && file_exists($mat['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($mat['image_url']); ?>" class="material-image-preview" alt="<?php echo htmlspecialchars($mat['material_name']); ?>">
                                    <?php else: ?>
                                        <div class="material-image-preview bg-secondary d-flex align-items-center justify-content-center text-white">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($mat['material_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($mat['material_name']); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($mat['type_name'] ?? 'ไม่ระบุ'); ?></span></td>
                                <td>
                                    <strong><?php echo $mat['quantity']; ?></strong>
                                    <?php if ($mat['quantity'] <= $mat['min_quantity']): ?>
                                        <span class="badge bg-danger ms-2">ใกล้หมด</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($mat['unit']); ?></td>
                                <td><?php echo $mat['min_quantity']; ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-1" onclick='editMaterial(<?php echo json_encode($mat); ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="material_id" value="<?php echo $mat['material_id']; ?>">
                                        <button type="submit" name="delete_material" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('ยืนยันการลบวัสดุ? ข้อมูลจะไม่สามารถกู้คืนได้')">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox"></i> ยังไม่มีข้อมูลวัสดุ
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
    <div class="modal fade" id="materialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มวัสดุ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="material_id" id="material_id">
                        <input type="hidden" name="old_image" id="old_image">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">รหัสวัสดุ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="material_code" id="material_code" required maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ชื่อวัสดุ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="material_name" id="material_name" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">หน่วย <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="unit" id="unit" placeholder="ชิ้น, อัน, เมตร..." required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">จำนวนคงเหลือ <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="0" required value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">จำนวนขั้นต่ำ <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="min_quantity" id="min_quantity" min="0" required value="0">
                                <small class="text-muted">เตือนเมื่อวัสดุเหลือน้อยกว่าจำนวนนี้</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">รูปภาพ</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*" onchange="previewImage(this)">
                            <div class="form-text">ขนาดไฟล์ไม่เกิน 5MB (JPG, PNG, GIF)</div>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">รายละเอียด</label>
                            <textarea class="form-control" name="description" id="description" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" name="save_material" class="btn btn-primary">
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
            document.getElementById('modalTitle').textContent = 'เพิ่มวัสดุ';
            document.getElementById('material_id').value = '';
            document.getElementById('material_code').value = '';
            document.getElementById('material_name').value = '';
            document.getElementById('type_id').value = '';
            document.getElementById('unit').value = '';
            document.getElementById('quantity').value = '0';
            document.getElementById('min_quantity').value = '0';
            document.getElementById('description').value = '';
            document.getElementById('old_image').value = '';
            document.getElementById('image').value = '';
            document.getElementById('imagePreview').innerHTML = '';
        }
        
        function editMaterial(data) {
            document.getElementById('modalTitle').textContent = 'แก้ไขวัสดุ';
            document.getElementById('material_id').value = data.material_id || '';
            document.getElementById('material_code').value = data.material_code || '';
            document.getElementById('material_name').value = data.material_name || '';
            document.getElementById('type_id').value = data.type_id || '';
            document.getElementById('unit').value = data.unit || '';
            document.getElementById('quantity').value = data.quantity || '0';
            document.getElementById('min_quantity').value = data.min_quantity || '0';
            document.getElementById('description').value = data.description || '';
            document.getElementById('old_image').value = data.image_url || '';
            
            if (data.image_url) {
                document.getElementById('imagePreview').innerHTML = 
                    '<img src="' + data.image_url + '" class="img-thumbnail" style="max-width: 200px;">';
            } else {
                document.getElementById('imagePreview').innerHTML = '';
            }
            
            new bootstrap.Modal(document.getElementById('materialModal')).show();
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
                    preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;">';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>