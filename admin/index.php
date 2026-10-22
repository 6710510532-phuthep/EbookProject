<?php
require_once '../auth.php';
require_once '../config.php';

require_admin();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $novel_id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE novels SET status = 'inactive' WHERE novel_id = ?");
    $stmt->execute([$novel_id]);
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM novels ORDER BY created_at DESC");
$stmt->execute();
$novels = $stmt->fetchAll();

$stats_stmt = $pdo->query("SELECT COUNT(*) as total_rentals, SUM(total_price) as total_income FROM rentals");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BookRent</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('site_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="meb-theme">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="index.php"><i class="fa-solid fa-sliders me-2"></i>Admin Dashboard</a>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn meb-darkmode-toggle rounded-circle me-1" id="globalDarkModeToggle">
                <i class="fa-solid fa-moon text-warning" id="darkModeIcon"></i>
            </button>
            <a href="../index.php" class="btn btn-outline-light btn-sm rounded-pill"><i class="fa-solid fa-globe me-1"></i>กลับหน้าร้าน</a>
            <a href="../logout.php" class="btn btn-secondary btn-sm rounded-pill"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white p-4 rounded-4 shadow-sm border-0">
                <h6><i class="fa-solid fa-receipt me-2"></i>รายการเช่าทั้งหมด</h6>
                <h2 class="fw-bold mb-0"><?= number_format($stats['total_rentals'] ?? 0) ?> ครั้ง</h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white p-4 rounded-4 shadow-sm border-0">
                <h6><i class="fa-solid fa-coins me-2"></i>รายได้รวมจากการเช่า</h6>
                <h2 class="fw-bold mb-0">฿<?= number_format($stats['total_income'] ?? 0, 2) ?></h2>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 meb-text-main">จัดการรายการนิยายในระบบ</h4>
        <a href="novel_form.php" class="btn btn-success rounded-pill fw-bold px-3"><i class="fa-solid fa-plus me-1"></i>เพิ่มนิยายใหม่</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden meb-detail-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table meb-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>รูปปก</th>
                            <th>ชื่อเรื่อง</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา/วัน</th>
                            <th>สถานะ</th>
                            <th class="pe-4 text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($novels as $novel): ?>
                            <tr>
                                <td class="ps-4"><?= $novel['novel_id'] ?></td>
                                <td><img src="<?= sanitize($novel['cover_img']) ?>" width="45" height="60" class="rounded-3 object-fit-cover shadow-sm" alt=""></td>
                                <td class="fw-bold meb-text-main"><?= sanitize($novel['title']) ?></td>
                                <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill"><?= sanitize($novel['category']) ?></span></td>
                                <td class="fw-bold text-danger">฿<?= number_format($novel['price_per_day'], 2) ?></td>
                                <td>
                                    <?php if ($novel['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="novel_form.php?id=<?= $novel['novel_id'] ?>" class="btn btn-sm btn-warning rounded-circle me-1"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <?php if ($novel['status'] === 'active'): ?>
                                        <a href="index.php?action=delete&id=<?= $novel['novel_id'] ?>" class="btn btn-sm btn-danger rounded-circle" onclick="return confirm('ซ่อนนิยายเรื่องนี้หรือไม่?')"><i class="fa-solid fa-eye-slash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>