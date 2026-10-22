<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    $stmt = $pdo->prepare("UPDATE rentals SET status = 'expired' WHERE user_id = ? AND status = 'active' AND expire_date < NOW()");
    $stmt->execute([$_SESSION['user_id']]);
    
    $u_stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE user_id = ?");
    $u_stmt->execute([$_SESSION['user_id']]);
    $current_user_data = $u_stmt->fetch();
    $_SESSION['wallet_balance'] = $current_user_data['wallet_balance'] ?? 0.00;
}
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookRent - คลังนิยายและอีบุ๊กออนไลน์</title>
    <!-- Prevent Theme Flicker Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('site_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Meb-inspired & Dark Mode CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="meb-theme">

<!-- Top Announcement Banner -->
<div class="top-bar-notice py-1 text-center text-white small fw-bold">
    <i class="fa-solid fa-fire me-1"></i> โปรโมชันเช่านิยายสุดคุ้ม! สมาชิกใหม่รับฟรี Wallet 100 บาททันที
</div>

<!-- Meb-style Sticky Navbar -->
<nav class="navbar navbar-expand-lg sticky-top meb-navbar shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
      <div class="meb-logo-icon"><i class="fa-solid fa-book-open"></i></div>
      <span class="meb-brand-name">Book<span class="meb-orange-text">Rent</span></span>
      <span class="badge meb-badge-sub me-2">E-BOOK RENTAL</span>
    </a>

    <!-- Top Search Input (Meb Style) -->
    <div class="meb-nav-search d-none d-lg-flex mx-auto">
      <div class="input-group">
        <span class="input-group-text border-end-0 rounded-start-pill ps-3 text-muted meb-search-bg"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="text" id="navSearchInput" class="form-control border-start-0 rounded-end-pill px-2 meb-search-bg" placeholder="ค้นหานิยาย ชื่อผู้แต่ง หรือหมวดหมู่...">
      </div>
    </div>

    <!-- Dark Mode Toggle Button (Mobile & Desktop) -->
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn meb-darkmode-toggle rounded-circle shadow-sm" id="globalDarkModeToggle" title="สลับโหมดมืด / สว่าง">
            <i class="fa-solid fa-moon text-warning" id="darkModeIcon"></i>
        </button>

        <button class="navbar-toggler border-0 shadow-none ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-2 mt-lg-0">
        <li class="nav-item">
          <a class="nav-link fw-semibold meb-nav-link" href="index.php"><i class="fa-solid fa-house me-1 text-meb-orange"></i> หน้าแรก</a>
        </li>
        <?php if (is_logged_in()): ?>
          <li class="nav-item">
            <a href="topup.php" class="meb-coin-badge d-flex align-items-center gap-2 px-3 py-1 rounded-pill text-decoration-none">
              <i class="fa-solid fa-coins text-warning fs-5"></i>
              <span class="fw-bold meb-coin-text">฿<?= number_format($_SESSION['wallet_balance'], 2) ?></span>
              <i class="fa-solid fa-plus-circle text-meb-orange ms-1"></i>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="btn btn-meb-user rounded-pill px-3 dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <i class="fa-solid fa-circle-user fs-5 text-meb-orange"></i> <?= sanitize($_SESSION['username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 meb-dropdown-menu">
              <li><a class="dropdown-item py-2 meb-dropdown-item" href="profile.php"><i class="fa-solid fa-clock-rotate-left me-2 text-meb-orange"></i>ประวัติการเช่าอ่าน</a></li>
              <?php if (is_admin()): ?>
                <li><a class="dropdown-item py-2 text-danger fw-bold" href="admin/index.php"><i class="fa-solid fa-sliders me-2"></i>จัดการระบบ (Admin)</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 text-muted meb-dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>ออกจากระบบ</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a href="login.php" class="btn btn-outline-meb rounded-pill px-4 btn-sm fw-bold">เข้าสู่ระบบ</a>
          </li>
          <li class="nav-item">
            <a href="register.php" class="btn btn-meb-orange rounded-pill px-4 btn-sm fw-bold text-white shadow-sm">สมัครสมาชิก</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="main-wrapper py-4">