<?php
require_once 'config.php';
checkLogin();
checkAdmin();

if (isset($_POST['save_user'])) {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $username = escape($_POST['username']);
    $full_name = escape($_POST['full_name']);
    $email = escape($_POST['email']);
    $phone = escape($_POST['phone']);
    $role = escape($_POST['role']);
    
    if ($user_id > 0) {
        // แก้ไข
        $sql = "UPDATE users SET 
                username = '$username',
                full_name = '$full_name',
                email = '$email',
                phone = '$phone',
                role = '$role'";
        
        // ถ้ามีการเปลี่ยนรหัสผ่าน
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];  // เก็บรหัสผ่านแบบธรรมดา
            $sql .= ", password = '$password'";
        }
        
        $sql .= " WHERE user_id = $user_id";
        
        if ($conn->query($sql)) {
            setAlert('แก้ไขผู้ใช้เรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $conn->error, 'danger');
        }
    } else {
        // เพิ่มใหม่
        $password = $_POST['password'];  // เก็บรหัสผ่านแบบธรรมดา
        $sql = "INSERT INTO users (username, password, full_name, email, phone, role) 
                VALUES ('$username', '$password', '$full_name', '$email', '$phone', '$role')";
        
        if ($conn->query($sql)) {
            setAlert('เพิ่มผู้ใช้เรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $conn->error, 'danger');
        }
    }
}

// ลบผู้ใช้
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    if ($user_id == $_SESSION['user_id']) {
        setAlert('ไม่สามารถลบผู้ใช้ที่กำลังใช้งานอยู่', 'danger');
    } else {
        if ($conn->query("DELETE FROM users WHERE user_id = $user_id")) {
            setAlert('ลบผู้ใช้เรียบร้อยแล้ว', 'success');
        } else {
            setAlert('เกิดข้อผิดพลาด: ' . $conn->error, 'danger');
        }
    }
}

// ดึงข้อมูล
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ - ระบบยืม-คืนอุปกรณ์</title>
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
                            <li><a class="dropdown-item" href="manage_materials.php">จัดการวัสดุ</a></li>
                            <li><a class="dropdown-item active" href="manage_users.php">จัดการผู้ใช้</a></li>
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
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
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
                        <h5 class="mb-0"><i class="bi bi-people"></i> จัดการผู้ใช้</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
                            <i class="bi bi-plus-circle"></i> เพิ่มผู้ใช้
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">ชื่อผู้ใช้</th>
                                <th width="20%">ชื่อ-นามสกุล</th>
                                <th width="20%">อีเมล</th>
                                <th width="15%">เบอร์โทร</th>
                                <th width="12%">บทบาท</th>
                                <th width="13%">สร้างเมื่อ</th>
                                <th width="5%" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $user['username']; ?></strong></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['phone']; ?></td>
                                <td>
                                    <?php
                                    $role_colors = ['admin' => 'danger', 'staff' => 'warning', 'user' => 'primary'];
                                    $role_text = ['admin' => 'ผู้ดูแลระบบ', 'staff' => 'เจ้าหน้าที่', 'user' => 'ผู้ใช้ทั่วไป'];
                                    ?>
                                    <span class="badge bg-<?php echo $role_colors[$user['role']]; ?>">
                                        <?php echo $role_text[$user['role']]; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-1" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('ยืนยันการลบผู้ใช้?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">เพิ่มผู้ใช้</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">รหัสผ่าน <span class="text-danger" id="pwd-required">*</span></label>
                                <input type="password" class="form-control" name="password" id="password">
                                <small class="text-muted" id="pwd-hint">ใส่เฉพาะเมื่อต้องการเปลี่ยนรหัสผ่าน</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" id="full_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">อีเมล</label>
                                <input type="email" class="form-control" name="email" id="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เบอร์โทร</label>
                                <input type="text" class="form-control" name="phone" id="phone">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">บทบาท <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="role" required>
                                <option value="user">ผู้ใช้ทั่วไป</option>
                                <option value="staff">เจ้าหน้าที่</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" name="save_user" class="btn btn-primary">
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
            document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้';
            document.getElementById('user_id').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('full_name').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('role').value = 'user';
            document.getElementById('password').required = true;
            document.getElementById('pwd-required').style.display = 'inline';
            document.getElementById('pwd-hint').style.display = 'none';
        }
        
        function editUser(data) {
            document.getElementById('modalTitle').textContent = 'แก้ไขผู้ใช้';
            document.getElementById('user_id').value = data.user_id;
            document.getElementById('username').value = data.username;
            document.getElementById('password').value = '';
            document.getElementById('full_name').value = data.full_name;
            document.getElementById('email').value = data.email || '';
            document.getElementById('phone').value = data.phone || '';
            document.getElementById('role').value = data.role;
            document.getElementById('password').required = false;
            document.getElementById('pwd-required').style.display = 'none';
            document.getElementById('pwd-hint').style.display = 'block';
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }
    </script>
</body>
</html>