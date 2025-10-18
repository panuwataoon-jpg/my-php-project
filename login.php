<?php
require_once 'config.php';

// ถ้า config.php ยังไม่มี session_start() ให้แน่ใจว่าเรียกก่อนใช้งาน $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // สมมติว่า escape() อยู่ใน config.php และใช้ htmlspecialchars/real_escape_string
    $username = escape($_POST['username']);
    $password = $_POST['password']; // รับรหัสเป็น plain text

    // ระวัง SQL injection — ถ้า escape() ทำงานถูกต้องก็พอใช้ได้
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // เปลี่ยนจาก password_verify(...) เป็นการเปรียบเทียบแบบตรง ๆ (plain text)
        if ($password === $user['password']) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'รหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'ไม่พบชื่อผู้ใช้';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<!-- (ส่วนหัว HTML เหมือนเดิม) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบยืม-คืนอุปกรณ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    /* (CSS เหมือนเดิม) */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container { max-width: 450px; margin: 0 auto; }
    .login-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
    .login-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
    .login-header i { font-size: 60px; margin-bottom: 15px; }
    .login-body { padding: 40px 30px; }
    .form-control { border-radius: 10px; padding: 12px 15px; border: 2px solid #e0e0e0; }
    .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
    .btn-login { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; padding: 12px; font-weight: 600; color: white; width: 100%; transition: transform 0.2s; }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
    .input-group-text { background: #f8f9fa; border: 2px solid #e0e0e0; border-right: none; border-radius: 10px 0 0 10px; }
    .input-group .form-control { border-left: none; border-radius: 0 10px 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="bi bi-box-seam"></i>
                    <h3 class="mb-0">ระบบยืม-คืนอุปกรณ์</h3>
                    <p class="mb-0">และเบิกวัสดุ</p>
                </div>
                <div class="login-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อผู้ใช้</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" class="form-control" name="username" required autofocus placeholder="กรอกชื่อผู้ใช้">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" name="password" required placeholder="กรอกรหัสผ่าน">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                        </button>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> ทดสอบระบบ: admin / admin123
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
