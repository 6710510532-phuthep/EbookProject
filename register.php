<?php
require_once 'header.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        die("CSRF Validation Failed");
    }

    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        if ($password !== $password_confirm) {
            $error = "รหัสผ่านไม่ตรงกัน";
        } else {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins_stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, wallet_balance) VALUES (?, ?, ?, 'user', 100.00)");
                $ins_stmt->execute([$username, $email, $hash]);
                header("Location: login.php");
                exit();
            }
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<div class="container pt-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 meb-detail-card">
                <div class="card-body">
                    <h3 class="fw-bold text-center meb-text-main mb-4"><i class="fa-solid fa-user-plus text-meb-orange me-2"></i>สมัครสมาชิก</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 rounded-3"><?= $error ?></div>
                    <?php endif; ?>
                    <form action="register.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label meb-text-main">ชื่อผู้ใช้ (Username)</label>
                            <input type="text" name="username" class="form-control rounded-3 meb-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label meb-text-main">อีเมล (Email)</label>
                            <input type="email" name="email" class="form-control rounded-3 meb-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label meb-text-main">รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control rounded-3 meb-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label meb-text-main">ยืนยันรหัสผ่าน</label>
                            <input type="password" name="password_confirm" class="form-control rounded-3 meb-input" required>
                        </div>
                        <button type="submit" class="btn btn-meb-orange btn-lg w-100 rounded-pill fw-bold text-white shadow-sm">ลงทะเบียน</button>
                    </form>
                    <div class="text-center mt-4">
                        <span class="text-muted">มีบัญชีอยู่แล้ว?</span> <a href="login.php" class="fw-bold text-meb-orange">เข้าสู่ระบบ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>