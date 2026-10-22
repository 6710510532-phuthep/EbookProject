<?php
require_once 'header.php';

$stmt = $pdo->prepare("SELECT * FROM novels WHERE status = 'active' ORDER BY created_at DESC");
$stmt->execute();
$novels = $stmt->fetchAll();

$cat_stmt = $pdo->query("SELECT DISTINCT category FROM novels WHERE status = 'active'");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container">
    <!-- Hero Banner Carousel / Promo Banner (Meb Style) -->
    <div class="meb-hero-banner p-4 p-md-5 rounded-4 shadow-sm mb-4 position-relative overflow-hidden">
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7 text-white">
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 mb-2 fw-bold"><i class="fa-solid fa-crown text-warning me-1"></i> อันดับ 1 ร้านเช่านิยายออนไลน์</span>
                <h1 class="display-5 fw-bold mb-2">คลังนิยายยอดฮิต <br>เช่าอ่านได้ทุกที่ ทุกเวลา</h1>
                <p class="mb-4 text-white-75 fs-5">ประหยัดสูงสุด 80% กับระบบเช่าอ่านรายวัน เลือกระยะเวลาได้เองทันที</p>
                <a href="#novelListSection" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-meb-orange shadow"><i class="fa-solid fa-angle-down me-1"></i> เลือกอ่านเลย</a>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&q=80" class="img-fluid rounded-4 shadow-lg hero-floating-book" alt="Book Banner">
            </div>
        </div>
        <div class="banner-bg-shape"></div>
    </div>

    <!-- Category Tabs (Meb Market Style Pills) -->
    <div class="category-scroll-wrapper mb-4">
        <div class="d-flex gap-2 pb-2" id="categoryFilters">
            <button class="btn meb-cat-btn active" data-category="all"><i class="fa-solid fa-border-all me-1"></i> ทั้งหมด</button>
            <?php foreach ($categories as $cat): ?>
                <button class="btn meb-cat-btn" data-category="<?= sanitize($cat) ?>">📚 <?= sanitize($cat) ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Section Header -->
    <div class="d-flex justify-content-between align-items-center mb-3" id="novelListSection">
        <h4 class="fw-bold mb-0 meb-section-title"><i class="fa-solid fa-fire text-meb-orange me-2"></i>นิยายแนะนำมาแรง</h4>
        <span class="text-muted small">พบ <?= count($novels) ?> เรื่อง</span>
    </div>

    <!-- Novel Grid (Meb Bookshelf Card Layout) -->
    <div class="row g-3 g-md-4" id="novelGrid">
        <?php if (count($novels) > 0): ?>
            <?php foreach ($novels as $novel): ?>
                <div class="col-6 col-md-4 col-lg-3 novel-item" data-title="<?= mb_strtolower($novel['title']) ?>" data-author="<?= mb_strtolower($novel['author']) ?>" data-category="<?= sanitize($novel['category']) ?>">
                    <div class="card meb-book-card border-0 h-100 shadow-sm rounded-4">
                        <div class="meb-cover-container">
                            <img src="<?= sanitize($novel['cover_img']) ?>" class="card-img-top meb-book-cover" alt="<?= sanitize($novel['title']) ?>">
                            <span class="meb-rental-tag"><i class="fa-solid fa-key me-1"></i>เช่าอ่าน</span>
                            <div class="meb-card-hover-overlay">
                                <a href="novel.php?id=<?= $novel['novel_id'] ?>" class="btn btn-meb-orange btn-sm rounded-pill px-3 text-white fw-bold"><i class="fa-solid fa-eye me-1"></i> อ่านเรื่องย่อ</a>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <span class="text-meb-orange small fw-bold mb-1"><?= sanitize($novel['category']) ?></span>
                            <h6 class="card-title fw-bold meb-card-title text-truncate mb-1"><?= sanitize($novel['title']) ?></h6>
                            <p class="text-muted small text-truncate mb-2"><i class="fa-solid fa-pen-nib me-1"></i><?= sanitize($novel['author']) ?></p>

                            <!-- Rating & Reviews -->
                            <div class="d-flex align-items-center gap-1 mb-2 small text-warning">
                                <i class="fa-solid fa-star"></i>
                                <span class="fw-bold meb-rating-text"><?= number_format($novel['rating'] ?? 4.8, 1) ?></span>
                                <span class="text-muted ms-1">(เช่าแล้ว 100+)</span>
                            </div>

                            <div class="mt-auto pt-2 meb-card-footer-border d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-muted d-block" style="font-size:10px;">ค่าเช่าเริ่มต้น</small>
                                    <span class="meb-price-text fw-bold fs-6">฿<?= number_format($novel['price_per_day'], 2) ?></span> <small class="text-muted" style="font-size:11px;">/วัน</small>
                                </div>
                                <a href="novel.php?id=<?= $novel['novel_id'] ?>" class="btn btn-outline-meb btn-sm rounded-pill px-3 fw-bold">เช่าเลย</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-book-open fs-1 mb-3 text-secondary"></i>
                <p>ยังไม่มีรายการนิยายในขณะนี้</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>