<?php
require_once '../auth.php';
require_once '../config.php';

require_admin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$novel = [
    'title' => '',
    'author' => '',
    'category' => 'Fantasy',
    'cover_img' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500&q=80',
    'synopsis' => '',
    'content' => '',
    'price_per_day' => '10.00',
    'status' => 'active'
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM novels WHERE novel_id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if ($existing) {
        $novel = $existing;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        die("CSRF Validation Failed");
    }

    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $category = sanitize($_POST['category']);
    $cover_img = sanitize($_POST['cover_img']);
    $synopsis = sanitize($_POST['synopsis']);
    $content = sanitize($_POST['content']);
    $price_per_day = floatval($_POST['price_per_day']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE novels SET title=?, author=?, category=?, cover_img=?, synopsis=?, content=?, price_per_day=?, status=? WHERE novel_id=?");
        $stmt->execute([$title, $author, $category, $cover_img, $synopsis, $content, $price_per_day, $status, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO novels (title, author, category, cover_img, synopsis, content, price_per_day, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $category, $cover_img, $synopsis, $content, $price_per_day, $status]);
    }

    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? 'แก้ไขนิยาย' : 'เพิ่มนิยายใหม่' ?></title>
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

<div class="container my-5" style="max-width: 800px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 meb-detail-card">
        <h4 class="fw-bold mb-4 border-bottom pb-3 meb-text-main"><?= $id > 0 ? 'แก้ไขข้อมูลนิยาย ID: ' . $id : 'เพิ่มนิยายใหม่' ?></h4>
        <form action="novel_form.php?id=<?= $id ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="mb-3">
                <label class="form-label fw-bold meb-text-main">ชื่อเรื่อง</label>
                <input type="text" name="title" class="form-control meb-input" value="<?= sanitize($novel['title']) ?>" required>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold meb-text-main">ผู้แต่ง</label>
                    <input type="text" name="author" class="form-control meb-input" value="<?= sanitize($novel['author']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold meb-text-main">หมวดหมู่</label>
                    <input type="text" name="category" class="form-control meb-input" value="<?= sanitize($novel['category']) ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold meb-text-main">ราคา/วัน (บาท)</label>
                    <input type="number" step="0.50" name="price_per_day" class="form-control meb-input" value="<?= $novel['price_per_day'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold meb-text-main">สถานะ</label>
                    <select name="status" class="form-select meb-input">
                        <option value="active" <?= $novel['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $novel['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold meb-text-main">URL รูปปก</label>
                <input type="text" name="cover_img" class="form-control meb-input" value="<?= sanitize($novel['cover_img']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold meb-text-main">เรื่องย่อ</label>
                <textarea name="synopsis" class="form-control meb-input" rows="3" required><?= sanitize($novel['synopsis']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold meb-text-main">เนื้อหานิยาย</label>
                <textarea name="content" class="form-control meb-input" rows="8" required><?= sanitize($novel['content']) ?></textarea>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <a href="index.php" class="btn btn-outline-secondary rounded-pill">ยกเลิก</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>