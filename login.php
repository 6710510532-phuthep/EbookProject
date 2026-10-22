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
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
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
                    <h3 class="fw-bold text-center meb-text-main mb-4"><i class="fa-solid fa-right-to-bracket text-meb-orange me-2"></i>เข้าสู่ระบบ</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 rounded-3"><?= $error ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label meb-text-main">ชื่อผู้ใช้ หรือ อีเมล</label>
                            <input type="text" name="username" class="form-control rounded-3 meb-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label meb-text-main">รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control rounded-3 meb-input" required>
                        </div>
                        <button type="submit" class="btn btn-meb-orange btn-lg w-100 rounded-pill fw-bold text-white shadow-sm">เข้าสู่ระบบ</button>
                    </form>
                    <div class="text-center mt-4">
                        <span class="text-muted">ยังไม่มีบัญชี?</span> <a href="register.php" class="fw-bold text-meb-orange">สมัครสมาชิกที่นี่</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>