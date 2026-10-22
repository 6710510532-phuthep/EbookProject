<?php
require_once 'header.php';

$novel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM novels WHERE novel_id = ? AND status = 'active'");
$stmt->execute([$novel_id]);
$novel = $stmt->fetch();

if (!$novel) {
    echo '<div class="container pt-5"><div class="alert alert-danger text-center rounded-4 shadow-sm">ไม่พบข้อมูลนิยายที่คุณต้องการ</div></div>';
    require_once 'footer.php';
    exit();
}

$has_active_rental = false;
$rental_data = null;

if (is_logged_in()) {
    $r_stmt = $pdo->prepare("SELECT * FROM rentals WHERE user_id = ? AND novel_id = ? AND status = 'active' AND expire_date > NOW() ORDER BY expire_date DESC LIMIT 1");
    $r_stmt->execute([$_SESSION['user_id'], $novel_id]);
    $rental_data = $r_stmt->fetch();
    if ($rental_data) {
        $has_active_rental = true;
    }
}
?>

<div class="container pt-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">หน้าแรก</a></li>
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted"><?= sanitize($novel['category']) ?></a></li>
        <li class="breadcrumb-item active meb-text-main fw-bold" aria-current="page"><?= sanitize($novel['title']) ?></li>
      </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 meb-detail-card mb-4">
        <div class="row g-4">
            <div class="col-md-4 col-lg-3 text-center">
                <div class="meb-detail-cover-box shadow rounded-4 overflow-hidden position-relative">
                    <img src="<?= sanitize($novel['cover_img']) ?>" class="img-fluid w-100 object-fit-cover" alt="<?= sanitize($novel['title']) ?>">
                    <span class="meb-rental-tag shadow-sm"><i class="fa-solid fa-key me-1"></i>นิยายเช่า</span>
                </div>
            </div>
            <div class="col-md-8 col-lg-9">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-meb-light text-meb-orange fw-bold px-3 py-2 rounded-pill"><?= sanitize($novel['category']) ?></span>
                    <span class="text-warning small"><i class="fa-solid fa-star"></i> <?= number_format($novel['rating'] ?? 4.8, 1) ?> (รีวิวจากผู้เช่าจริง)</span>
                </div>
                
                <h2 class="fw-bold meb-text-main mb-2"><?= sanitize($novel['title']) ?></h2>
                <p class="text-muted mb-3"><i class="fa-solid fa-pen-nib me-1 text-meb-orange"></i>ประพันธ์โดย: <strong class="meb-text-main"><?= sanitize($novel['author']) ?></strong></p>
                
                <div class="meb-synopsis-box p-3 rounded-4 mb-4">
                    <h6 class="fw-bold meb-text-main mb-2"><i class="fa-solid fa-align-left text-meb-orange me-2"></i>เรื่องย่อ</h6>
                    <p class="card-text text-secondary mb-0 leading-relaxed small" style="line-height:1.8;"><?= nl2br(sanitize($novel['synopsis'])) ?></p>
                </div>

                <?php if ($has_active_rental): ?>
                    <div class="card border-0 bg-success-subtle p-4 rounded-4 text-success border-success">
                        <h5 class="fw-bold d-flex align-items-center gap-2 mb-2"><i class="fa-solid fa-circle-check fs-4"></i> คุณกำลังเช่านิยายเรื่องนี้อยู่</h5>
                        <p class="mb-3 text-dark small">หมดอายุการเช่า: <strong><?= date('d/m/Y H:i', strtotime($rental_data['expire_date'])) ?></strong></p>
                        <a href="read.php?id=<?= $novel['novel_id'] ?>" class="btn btn-meb-orange btn-lg rounded-pill fw-bold text-white shadow"><i class="fa-solid fa-book-open me-2"></i>เปิดอ่านนิยายฉบับเต็ม</a>
                    </div>
                <?php else: ?>
                    <!-- Rental Calculator Box (Meb Style) -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden meb-calc-card">
                        <div class="card-header bg-meb-gradient text-white fw-bold py-3 px-4">
                            <i class="fa-solid fa-calculator me-2"></i> คำนวณราคาเช่าตามจำนวนวัน
                        </div>
                        <div class="card-body p-4">
                            <form action="rent.php" method="POST" id="rentForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="novel_id" value="<?= $novel['novel_id'] ?>">
                                <input type="hidden" id="pricePerDay" value="<?= $novel['price_per_day'] ?>">

                                <div class="row align-items-center mb-3">
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold d-block">อัตราค่าเช่ารายวัน:</span>
                                        <span class="fs-4 fw-bold text-meb-orange">฿<?= number_format($novel['price_per_day'], 2) ?> <small class="fs-6 text-muted fw-normal">/วัน</small></span>
                                    </div>
                                    <div class="col-sm-6 mt-2 mt-sm-0">
                                        <label for="rentDays" class="form-label fw-bold mb-1 meb-text-main">ระยะเวลาเช่า:</label>
                                        <select name="rent_days" id="rentDays" class="form-select form-select-lg rounded-3 meb-input">
                                            <option value="1">1 วัน</option>
                                            <option value="3" selected>3 วัน (ยอดนิยม)</option>
                                            <option value="7">7 วัน</option>
                                            <option value="15">15 วัน</option>
                                            <option value="30">30 วัน (คุ้มที่สุด)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="p-3 rounded-4 text-center mb-4 meb-calc-display border border-meb-orange-subtle">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">ยอดชำระสุทธิ</small>
                                    <div class="fs-2 fw-bold text-meb-orange" id="totalPriceDisplay">฿0.00</div>
                                </div>

                                <?php if (is_logged_in()): ?>
                                    <button type="submit" class="btn btn-meb-orange btn-lg w-100 rounded-pill fw-bold text-white shadow"><i class="fa-solid fa-key me-2"></i>ยืนยันการเช่าอ่านทันที</button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-secondary btn-lg w-100 rounded-pill fw-bold"><i class="fa-solid fa-lock me-2"></i>เข้าสู่ระบบเพื่อทำการเช่า</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>