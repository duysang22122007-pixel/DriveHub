<footer class="site-footer" id="lien-he">
    <span class="footer__accent" aria-hidden="true"></span>
    <span class="footer__car" aria-hidden="true"></span>
    <span class="footer__veil" aria-hidden="true"></span>

    <div class="container footer__grid">
        <div class="footer__brand">
            <?php require PATH_VIEW . 'partials/logo.php'; ?>

            <p class="footer__desc">
                DriveHub đồng hành cùng bạn trong việc bảo dưỡng và chăm sóc ô tô,
                mang đến trải nghiệm dịch vụ chuyên nghiệp, an toàn và đáng tin cậy.
            </p>

            <div class="footer__social" aria-label="Kênh liên hệ và mạng xã hội">
                <span class="footer__social-link" role="img" title="Facebook đang cập nhật" aria-label="Facebook đang cập nhật">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 8h2V4.5c-.4-.1-1.5-.2-2.8-.2-2.8 0-4.7 1.7-4.7 4.9V12H5v4h3.5v9h4.3v-9h3.4l.5-4h-3.9V9.4c0-1.2.3-1.4 1.2-1.4Z" fill="currentColor" />
                    </svg>
                </span>
                <span class="footer__social-link" role="img" title="YouTube đang cập nhật" aria-label="YouTube đang cập nhật">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21.6 7.2c-.2-1.1-1.1-2-2.2-2.2C17.5 4.6 12 4.6 12 4.6s-5.5 0-7.4.4C3.5 5.2 2.6 6.1 2.4 7.2 2 9.1 2 12 2 12s0 2.9.4 4.8c.2 1.1 1.1 2 2.2 2.2 1.9.4 7.4.4 7.4.4s5.5 0 7.4-.4c1.1-.2 2-1.1 2.2-2.2.4-1.9.4-4.8.4-4.8s0-2.9-.4-4.8Z" fill="none" stroke="currentColor" stroke-width="1.7" />
                        <path d="M10 15.5v-7l6 3.5-6 3.5Z" fill="currentColor" />
                    </svg>
                </span>
                <span class="footer__social-link" role="img" title="Zalo đang cập nhật" aria-label="Zalo đang cập nhật">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21 11.5c0 4.7-4 8.5-9 8.5-1.1 0-2.2-.2-3.2-.5L4 21l1.4-3.5C4.5 16.2 4 14.9 4 13.5 4 8.8 8 5 13 5s8 3.8 8 6.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        <path d="M9 10.5h5l-5 5h5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="footer__social-link" role="img" title="Hotline đang cập nhật" aria-label="Hotline đang cập nhật">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7.5 4.5h3l1.5 4-2 1.5c1 2.2 2.8 4 5 5l1.5-2 4 1.5v3c0 1.1-.9 2-2 2C10.8 19.5 4.5 13.2 4.5 6.5c0-1.1.9-2 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>

        <nav class="footer__col footer__col--links" aria-labelledby="footer-links-title">
            <h2 class="footer__title" id="footer-links-title">Liên kết nhanh</h2>
            <ul class="footer__links">
                <li><a href="<?= BASE_URL ?>"><span class="footer__arrow" aria-hidden="true"></span>Trang chủ</a></li>
                <li><a href="#gioi-thieu"><span class="footer__arrow" aria-hidden="true"></span>Giới thiệu</a></li>
                <li><a href="#dich-vu"><span class="footer__arrow" aria-hidden="true"></span>Dịch vụ</a></li>
                <li><a href="<?= BASE_URL ?>?action=booking"><span class="footer__arrow" aria-hidden="true"></span>Đặt lịch</a></li>
                <li><a href="<?= BASE_URL ?>?action=my-cars"><span class="footer__arrow" aria-hidden="true"></span>Lịch sử bảo dưỡng</a></li>
                <li><a href="#lien-he"><span class="footer__arrow" aria-hidden="true"></span>Liên hệ</a></li>
            </ul>
        </nav>

        <div class="footer__col footer__col--services">
            <h2 class="footer__title">Dịch vụ nổi bật</h2>
            <ul class="footer__services">
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M14.7 6.3a4 4 0 0 0-5 5L4 17l3 3 5.7-5.7a4 4 0 0 0 5-5l-2.4 2.4-2.3-2.3 2.4-2.4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Bảo dưỡng định kỳ</a>
                </li>
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3.5s6 6.3 6 10.2a6 6 0 0 1-12 0C6 9.8 12 3.5 12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <path d="M9.5 14a2.5 2.5 0 0 0 2.5 2.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Thay dầu động cơ</a>
                </li>
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <path d="M12 4v3M12 17v3M4 12h3M17 12h3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Kiểm tra hệ thống phanh</a>
                </li>
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <path d="M12 4v4M12 16v4M4 12h4M16 12h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Kiểm tra lốp và bánh xe</a>
                </li>
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2v20M4.2 7l15.6 10M19.8 7 4.2 17M8.5 4.5 12 8l3.5-3.5M8.5 19.5 12 16l3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Kiểm tra điều hòa</a>
                </li>
                <li>
                    <span class="footer__service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 8V5h6v3M6 10h12l2 3v5H4v-5l2-3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <path d="M2 13h2M20 13h2M8 18v3M16 18v3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </span>
                    <a href="#dich-vu">Kiểm tra động cơ</a>
                </li>
            </ul>
            <a class="footer__more" href="#dich-vu">Xem tất cả dịch vụ <span aria-hidden="true">&rarr;</span></a>
        </div>

        <div class="footer__col footer__col--contact">
            <h2 class="footer__title">Thông tin liên hệ</h2>
            <ul class="footer__contact">
                <li>
                    <span class="footer__contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21s6-5.3 6-10a6 6 0 0 0-12 0c0 4.7 6 10 6 10Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <circle cx="12" cy="11" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7" />
                        </svg>
                    </span>
                    <span class="footer__contact-text">
                        <strong>Địa chỉ</strong>
                        <span>13B Trịnh Văn Bô, Quận Nam Từ Liêm, Hà Nội</span>
                    </span>
                </li>
                <li>
                    <span class="footer__contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M7.5 4.5h3l1.5 4-2 1.5c1 2.2 2.8 4 5 5l1.5-2 4 1.5v3c0 1.1-.9 2-2 2C10.8 19.5 4.5 13.2 4.5 6.5c0-1.1.9-2 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="footer__contact-text">
                        <strong>Hotline</strong>
                        <span>0337085291</span>
                    </span>
                </li>
                <li>
                    <span class="footer__contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="footer__contact-text">
                        <strong>Email</strong>
                        <span>drivehub@gmail.com</span>
                    </span>
                </li>
                <li>
                    <span class="footer__contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="footer__contact-text">
                        <strong>Giờ làm việc</strong>
                        <span>08:00 - 22:00</span>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <div class="container footer__bottom">
        <p class="footer__copyright">&copy; <?= date('Y') ?> DriveHub. Bảo lưu mọi quyền.</p>
        <p class="footer__slogan">DRIVEHUB &ndash; ĐỒNG HÀNH CÙNG MỌI HÀNH TRÌNH</p>
        <ul class="footer__legal">
            <li><a href="#" data-pending="Chính sách bảo mật">Chính sách bảo mật</a></li>
            <li><a href="#" data-pending="Điều khoản sử dụng">Điều khoản sử dụng</a></li>
            <li><a href="#" data-pending="Hỗ trợ khách hàng">Hỗ trợ khách hàng</a></li>
        </ul>
    </div>
</footer>
