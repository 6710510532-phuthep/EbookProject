<?php
require_once 'header.php';
require_login();

$user_id = $_SESSION['user_id'];

if (isset($_GET['action']) && $_GET['action'] === 'hide' && isset($_GET['rental_id'])) {
    $rental_id = intval($_GET['rental_id']);
    $stmt = $pdo->prepare("UPDATE rentals SET status = 'hidden' WHERE rental_id = ? AND user_id = ?");
    $stmt->execute([$rental_id, $user_id]);
    header("Location: profile.php");
    exit();
}

$u_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

$r_stmt = $pdo->prepare("
    SELECT r.*, n.title, n.cover_img 
    FROM rentals r 
    JOIN novels n ON r.novel_id = n.novel_id 
    WHERE r.user_id = ? AND r.status != 'hidden' 
    ORDER BY r.rent_date DESC
");
$r_stmt->execute([$user_id]);
$rentals = $r_stmt->fetchAll();
?>

<div class="container pt-2">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 meb-detail-card">
                <div class="avatar-circle mx-auto mb-3 text-white fs-1 fw-bold d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 90px; height: 90px; background: linear-gradient(135deg, #f97316, #ea580c);">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold meb-text-main mb-1"><?= sanitize($user['username']) ?></h4>
                <p class="text-muted small mb-3"><?= sanitize($user['email']) ?></p>
                <span class="badge bg-meb-light text-meb-orange fw-bold px-3 py-1 mb-3">สถานะ: <?= strtoupper($user['role']) ?></span>
                <hr class="meb-hr">
                <div class="d-flex justify-content-between align-items-center px-2 mb-3">
                    <span class="text-muted">ยอดเงินคงเหลือ:</span>
                    <span class="fs-4 fw-bold text-meb-orange">฿<?= number_format($user['wallet_balance'], 2) ?></span>
                </div>
                <a href="topup.php" class="btn btn-meb-orange w-100 rounded-pill fw-bold text-white py-2 shadow-sm"><i class="fa-solid fa-plus me-1"></i> เติมเงิน Wallet</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden meb-detail-card">
                <div class="card-header bg-transparent py-3 px-4 border-0 fw-bold fs-5 meb-text-main d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-meb-orange"></i> ประวัติการเช่านิยายของคุณ
                </div>
                <div class="card-body p-0">
                    <?php if (count($rentals) > 0): ?>
                        <div class="table-responsive">
                            <table class="table meb-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">นิยาย</th>
                                        <th>ระยะเวลา</th>
                                        <th>ราคารวม</th>
                                        <th>เวลาคงเหลือ</th>
                                        <th class="pe-4 text-end">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $item): ?>
                                        <?php $is_active = ($item['status'] === 'active' && strtotime($item['expire_date']) > time()); ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?= sanitize($item['cover_img']) ?>" width="45" height="60" class="rounded-3 object-fit-cover shadow-sm" alt="">
                                                    <span class="fw-bold meb-text-main"><?= sanitize($item['title']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= $item['rent_days'] ?> วัน</td>
                                            <td class="fw-bold text-meb-orange">฿<?= number_format($item['total_price'], 2) ?></td>
                                            <td>
                                                <?php if ($is_active): ?>
                                                    <span class="badge bg-success-subtle text-success rounded-pill mb-1">กำลังเช่า</span><br>
                                                    <small class="text-danger fw-bold countdown-timer" data-expire="<?= $item['expire_date'] ?>">คำนวณเวลา...</small>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">หมดอายุ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <?php if ($is_active): ?>
                                                    <a href="read.php?id=<?= $item['novel_id'] ?>" class="btn btn-sm btn-meb-orange rounded-pill px-3 text-white fw-bold"><i class="fa-solid fa-book-open me-1"></i> อ่านเลย</a>
                                                <?php else: ?>
                                                    <a href="profile.php?action=hide&rental_id=<?= $item['rental_id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('ซ่อนรายการนี้หรือไม่?')" title="ซ่อนประวัติ"><i class="fa-solid fa-trash"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-receipt fs-1 mb-2 text-secondary"></i>
                            <p>ยังไม่มีประวัติการเช่าหนังสือ</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>