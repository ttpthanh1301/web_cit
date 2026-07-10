</main>
<footer class="club-footer pt-5 pb-4 mt-auto">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom border-white border-opacity-10">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <img src="assets/images/cit/logoclb.png" alt="Logo CIT" width="36" height="36" class="footer-logo" decoding="async" loading="lazy">
                    CIT Club
                </div>
                <p class="mb-3 footer-desc">
                    Câu lạc bộ Công nghệ Thông tin CIT — nơi sinh viên đam mê công nghệ cùng học hỏi, thực hành và kết nối.
                </p>
                <div class="footer-social d-flex gap-2">
                    <a href="https://www.facebook.com/clbcongnghe.cit" target="_blank" rel="noopener" aria-label="Facebook CIT Club" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                </div>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1">
                <h6 class="footer-heading text-white fw-bold mb-3">Liên kết</h6>
                <ul class="footer-list list-unstyled mb-0">
                    <li><a href="index.php" class="footer-link">Trang chủ</a></li>
                    <li><a href="about.php" class="footer-link">Giới thiệu</a></li>
                    <li><a href="activities.php" class="footer-link">Hoạt động</a></li>
                    <li><a href="index.php#gallery" class="footer-link">Album ảnh</a></li>
                    <li><a href="contact.php" class="footer-link">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-heading text-white fw-bold mb-3">Tham gia</h6>
                <ul class="footer-list list-unstyled mb-0">
                    <li><a href="recruitment.php" class="footer-link">Đơn tuyển thành viên</a></li>
                    <li><a href="admin/login.php" class="footer-link">Cổng quản trị</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="footer-heading text-white fw-bold mb-3">Liên hệ</h6>
                <ul class="footer-contact list-unstyled mb-0">
                    <li><i class="bi bi-geo-alt me-2 footer-accent"></i>Trường Đại học Thương mại</li>
                    <li>
                        <a href="https://www.facebook.com/clbcongnghe.cit" target="_blank" rel="noopener" class="footer-link">
                            <i class="bi bi-facebook me-2 footer-accent"></i>fb.com/clbcongnghe.cit
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="footer-link">
                            <i class="bi bi-chat-dots me-2 footer-accent"></i>Trang liên hệ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 pt-4">
            <p class="footer-legal mb-0">
                &copy; <?= date('Y') ?> CIT Club. Mọi quyền được bảo lưu.
            </p>
            <a href="recruitment.php" class="btn btn-club btn-sm px-4">
                <i class="bi bi-arrow-right-circle me-1"></i>Ứng tuyển ngay
            </a>
        </div>
    </div>
</footer>
    <!-- Script defer: không block render trang -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>

    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= e((string) $script) ?>" defer></script>
    <?php endforeach; ?>
    <?php 
    require_once __DIR__ . '/editable.php';
    if (is_admin()): 
    ?>
        <script src="assets/js/editable.js" defer></script>
    <?php endif; ?>
</body>
</html>
