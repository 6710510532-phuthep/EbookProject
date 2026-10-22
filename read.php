<?php
require_once 'header.php';
require_login();

$novel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM novels WHERE novel_id = ? AND status = 'active'");
$stmt->execute([$novel_id]);
$novel = $stmt->fetch();

if (!$novel) {
    echo '<div class="container pt-5"><div class="alert alert-danger text-center">ไม่พบเนื้อหานิยาย</div></div>';
    require_once 'footer.php';
    exit();
}

$is_authorized = false;
if (is_admin()) {
    $is_authorized = true;
} else {
    $r_stmt = $pdo->prepare("SELECT * FROM rentals WHERE user_id = ? AND novel_id = ? AND status = 'active' AND expire_date >= NOW() LIMIT 1");
    $r_stmt->execute([$user_id, $novel_id]);
    $active_rental = $r_stmt->fetch();
    if ($active_rental) {
        $is_authorized = true;
    }
}

if (!$is_authorized) {
    echo '<div class="container pt-5">
            <div class="card shadow border-0 rounded-4 text-center p-5 meb-detail-card">
                <i class="fa-solid fa-lock fs-1 text-warning mb-3"></i>
                <h3 class="fw-bold meb-text-main">สิทธิ์การอ่านของคุณหมดอายุแล้ว</h3>
                <p class="text-muted">โปรดเช่านิยายเพื่อเข้าอ่านเนื้อหาฉบับเต็มอีกครั้ง</p>
                <div>
                    <a href="novel.php?id=' . $novel_id . '" class="btn btn-meb-orange rounded-pill px-4 py-2 text-white fw-bold"><i class="fa-solid fa-cart-shopping me-1"></i> ไปหน้าเช่านิยาย</a>
                </div>
            </div>
          </div>';
    require_once 'footer.php';
    exit();
}
?>

<div class="container pt-2">
    <!-- Reader Toolbar -->
    <div class="meb-reader-toolbar shadow-lg rounded-pill p-2 mb-4 d-flex align-items-center justify-content-between px-4 border">
        <div class="d-flex align-items-center gap-2">
            <a href="novel.php?id=<?= $novel['novel_id'] ?>" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:36px; height:36px;"><i class="fa-solid fa-arrow-left"></i></a>
            <span class="fw-bold text-truncate ms-2 d-none d-sm-inline meb-text-main" style="max-width: 250px;"><?= sanitize($novel['title']) ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="btn-group btn-group-sm border rounded-pill p-1 meb-btn-group-bg">
                <button type="button" class="btn btn-light rounded-circle border-0 meb-font-btn" id="btnFontDecrease" style="width:32px; height:32px;"><i class="fa-solid fa-minus"></i></button>
                <span class="px-2 d-flex align-items-center fw-bold meb-text-main" id="fontSizeLabel">18px</span>
                <button type="button" class="btn btn-light rounded-circle border-0 meb-font-btn" id="btnFontIncrease" style="width:32px; height:32px;"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-light border rounded-circle theme-btn" data-theme="theme-light" title="ธีมสว่าง">☀️</button>
                <button type="button" class="btn btn-sm btn-warning rounded-circle theme-btn" data-theme="theme-sepia" title="ธีมถนอมสายตา Sepia">📜</button>
                <button type="button" class="btn btn-sm btn-dark rounded-circle theme-btn" data-theme="theme-dark" title="ธีมมืด">🌙</button>
            </div>
        </div>
    </div>

    <!-- Reader Paper Container -->
    <div class="card shadow-sm border-0 rounded-4 mb-5 overflow-hidden">
        <div class="card-body p-4 p-md-5 reader-box theme-light" id="readerBox">
            <h2 class="text-center fw-bold mb-3"><?= sanitize($novel['title']) ?></h2>
            <div class="text-center text-muted small mb-4 pb-3 border-bottom">
                <i class="fa-solid fa-feather me-1"></i>เขียนโดย: <?= sanitize($novel['author']) ?>
            </div>
            <div class="novel-text-content" id="novelContent">
                <?= nl2br(sanitize($novel['content'])) ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>