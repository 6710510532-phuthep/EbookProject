<?php
require_once 'auth.php';
require_once 'config.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        die("CSRF Token Verification Failed.");
    }

    $novel_id = intval($_POST['novel_id'] ?? 0);
    $rent_days = intval($_POST['rent_days'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($novel_id <= 0 || $rent_days <= 0) {
        header("Location: index.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM novels WHERE novel_id = ? AND status = 'active'");
    $stmt->execute([$novel_id]);
    $novel = $stmt->fetch();

    if (!$novel) {
        die("ไม่พบนิยายในระบบ");
    }

    $total_price = $novel['price_per_day'] * $rent_days;

    $u_stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE user_id = ?");
    $u_stmt->execute([$user_id]);
    $user = $u_stmt->fetch();

    if ($user['wallet_balance'] < $total_price) {
        $_SESSION['error'] = "ยอดเงินคงเหลือไม่พอสำหรับการเช่า";
        header("Location: novel.php?id=" . $novel_id);
        exit();
    }

    $pdo->beginTransaction();
    try {
        $deduct_stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE user_id = ?");
        $deduct_stmt->execute([$total_price, $user_id]);

        $expire_date = date('Y-m-d H:i:s', strtotime("+$rent_days days"));
        $rent_stmt = $pdo->prepare("INSERT INTO rentals (user_id, novel_id, rent_days, total_price, rent_date, expire_date, status) VALUES (?, ?, ?, ?, NOW(), ?, 'active')");
        $rent_stmt->execute([$user_id, $novel_id, $rent_days, $total_price, $expire_date]);

        $pdo->commit();
        header("Location: read.php?id=" . $novel_id);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("เกิดข้อผิดพลาด: " . $e->getMessage());
    }
}
?>