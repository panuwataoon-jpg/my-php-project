<?php
require_once 'config.php';
checkLogin();

$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$equipment = $conn->query("SELECT e.*, et.type_name FROM equipments e 
                          LEFT JOIN equipment_types et ON e.type_id = et.type_id 
                          WHERE e.equipment_id = $equipment_id")->fetch_assoc();

if (!$equipment) {
    header('Location: equipments.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $quantity = (int)$_POST['quantity'];
    $borrow_date = date('Y-m-d H:i:s');
    $expected_return_date = escape($_POST['expected_return_date']) . ' 23:59:59';
    $notes = escape($_POST['notes']);
    
    if ($quantity <= 0 || $quantity > $equipment['available_quantity']) {
        setAlert('จำนวนไม่ถูกต้อง', 'danger');
    } else {
        // บันทึกการยืม
        $sql = "INSERT INTO borrowing (equipment_id, user_id, borrow_date, expected_return_date, quantity, notes, status) 
                VALUES ($equipment_id, $user_id, '$borrow_date', '$expected_return_date', $quantity, '$notes', 'borrowed')";
        
        if ($conn->query($sql)) {
            // อัพเดทจำนวนอุปกรณ์ที่ว่าง
            $new_available = $equipment['available_quantity'] - $quantity;
            $conn->query("UPDATE equipments SET available_quantity = $new_available WHERE equipment_id = $equipment_id");
            
            setAlert('ยืมอุปกรณ์สำเร็จ', 'success');
            header('Location: borrowing.php');
            exit();
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $conn->error, 'danger');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืมอุปกรณ์ - <?php echo $equipment['equipment_name']; ?></title>
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
        .equipment-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
        }
        .equipment-preview i {
            font-size: 5rem;
            margin-bottom: 20px;
        }
        .btn-action {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-box-seam"></i> ระบบยืม-คืนอุปกรณ์
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="equipments.php">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php showAlert(); ?>
        
        <div class="row">
            <div class="col-md-5">
                <div class="equipment-preview">
                    <i class="bi bi-laptop"></i>
                    <h3><?php echo $equipment['equipment_name']; ?></h3>
                    <p class="mb-0"><?php echo $equipment['equipment_code']; ?></p>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-info-circle"></i> รายละเอียดอุปกรณ์</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ประเภท:</strong></td>
                                <td><?php echo $equipment['type_name']; ?></td>
                            </tr>
                            <?php if ($equipment['brand']): ?>
                            <tr>
                                <td><strong>ยี่ห้อ:</strong></td>
                                <td><?php echo $equipment['brand']; ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($equipment['model']): ?>
                            <tr>
                                <td><strong>รุ่น:</strong></td>
                                <td><?php echo $equipment['model']; ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>จำนวนทั้งหมด:</strong></td>
                                <td><?php echo $equipment['quantity']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>ว่าง:</strong></td>
                                <td><span class="badge bg-success"><?php echo $equipment['available_quantity']; ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-cart-plus"></i> แบบฟอร์มยืมอุปกรณ์</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ผู้ยืม</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['full_name']; ?>" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">จำนวนที่ต้องการยืม <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="quantity" min="1" max="<?php echo $equipment['available_quantity']; ?>" value="1" required>
                                <small class="text-muted">สามารถยืมได้สูงสุด <?php echo $equipment['available_quantity']; ?> ชิ้น</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">วันที่คาดว่าจะคืน <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="expected_return_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">หมายเหตุ / วัตถุประสงค์</label>
                                <textarea class="form-control" name="notes" rows="4" placeholder="ระบุวัตถุประสงค์การใช้งาน..."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-action">
                                    <i class="bi bi-check-circle"></i> ยืนยันการยืม
                                </button>
                                <a href="equipments.php" class="btn btn-outline-secondary btn-action">
                                    <i class="bi bi-x-circle"></i> ยกเลิก
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>