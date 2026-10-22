<?php
require_once 'header.php';
require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        die("CSRF Token Verification Failed.");
    }

    $amount = floatval($_POST['amount'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($amount <= 0) {
        $error = "กรุณาระบุจำนวนเงินที่ถูกต้อง";
    } elseif (!isset($_FILES['slip_img']) || $_FILES['slip_img']['error'] !== UPLOAD_ERR_OK) {
        $error = "กรุณาแนบไฟล์สลีปการโอนเงิน";
    } else {
        $file = $_FILES['slip_img'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $error = "ขนาดไฟล์สลีปต้องไม่เกิน 5 MB";
        } else {
            $upload_dir = __DIR__ . '/uploads/slips/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = 'slip_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $slip_db_path = 'uploads/slips/' . $new_filename;

                // Insert into topups table (status = pending)
                $stmt = $pdo->prepare("INSERT INTO topups (user_id, amount, slip_img, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$user_id, $amount, $slip_db_path]);

                $message = "ส่งสลีปการเติมเงิน ฿" . number_format($amount, 2) . " เรียบร้อยแล้ว! ระบบกำลังรอแอดมินตรวจสอบ";
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกไฟล์สลีป";
            }
        }
    }
}
?>

<div class="container pt-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden meb-detail-card">
                <div class="card-header bg-meb-gradient text-white fw-bold fs-5 py-3 text-center border-0">
                    <i class="fa-solid fa-wallet me-2"></i>เติมเงินเข้า Wallet (แจ้งสลีปชำระเงิน)
                </div>
                <div class="card-body p-4">

                    <?php if ($message): ?>
                        <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                            <div><?= $message ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 py-2 mb-3"><?= $error ?></div>
                    <?php endif; ?>

                    <!-- Simulated Bank / PromptPay Transfer Box -->
                    <div class="p-3 bg-light rounded-4 border text-center mb-4 meb-synopsis-box">
                        <small class="text-muted fw-bold d-block mb-1"><i class="fa-solid fa-qrcode text-meb-orange me-1"></i> สแกนชำระเงินผ่าน พร้อมเพย์ / ธนาคาร</small>
                        <div class="bg-white d-inline-block p-2 rounded-3 border shadow-sm my-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=PROMPTPAY_0812345678" width="150" height="150" alt="PromptPay QR">
                        </div>
                        <div class="small meb-text-main">
                            <div><strong>ธนาคารกสิกรไทย (K-Bank)</strong></div>
                            <div class="fs-5 fw-bold text-meb-orange my-1">123-4-56789-0</div>
                            <div class="text-muted">ชื่อบัญชี: บจก. บุ๊คเรนท์ นิยายออนไลน์</div>
                        </div>
                    </div>

                    <form action="topup.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold meb-text-main">1. เลือกจำนวนเงินที่ต้องการเติม:</label>
                            <div class="row g-2 mb-3">
                                <div class="col-4"><button type="button" class="btn btn-outline-meb w-100 rounded-pill topup-amount-btn" data-val="100">฿100</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-meb w-100 rounded-pill topup-amount-btn" data-val="300">฿300</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-meb w-100 rounded-pill topup-amount-btn" data-val="500">฿500</button></div>
                            </div>
                            <input type="number" step="0.01" min="1" name="amount" id="topupAmountInput" class="form-control form-control-lg rounded-3 meb-input" placeholder="ระบุจำนวนเงิน (บาท)" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold meb-text-main"><i class="fa-solid fa-file-invoice-dollar text-meb-orange me-1"></i>2. แนบรูปภาพสลีปการโอนเงิน:</label>
                            <input type="file" name="slip_img" id="slipInput" class="form-control meb-input" accept="image/*" required>
                            <small class="text-muted mt-1 d-block">รองรับไฟล์ JPG, PNG, WEBP (ขนาดไม่เกิน 5 MB)</small>
                            <div id="slipPreviewBox" class="mt-2 text-center d-none">
                                <img id="slipPreviewImg" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 200px;" alt="Slip Preview">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-meb-orange btn-lg w-100 rounded-pill fw-bold text-white shadow-sm">
                            <i class="fa-solid fa-upload me-1"></i> ยืนยันการแจ้งชำระเงิน
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="profile.php" class="small text-decoration-none text-meb-orange"><i class="fa-solid fa-clock-rotate-left me-1"></i> ดูประวัติการเติมเงินและสถานะสลีป</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('topupAmountInput');
    document.querySelectorAll('.topup-amount-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            amountInput.value = this.getAttribute('data-val');
        });
    });

    const slipInput = document.getElementById('slipInput');
    const slipPreviewBox = document.getElementById('slipPreviewBox');
    const slipPreviewImg = document.getElementById('slipPreviewImg');

    if (slipInput) {
        slipInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    slipPreviewImg.src = e.target.result;
                    slipPreviewBox.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                slipPreviewBox.classList.add('d-none');
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>