<!-- ============================================================
     HERO BANNER
============================================================ -->
<section class="hero" aria-labelledby="hero-title">
    <div class="hero__media" aria-hidden="true"></div>
    <div class="hero__overlay" aria-hidden="true"></div>

    <div class="container hero__content">
        <p class="eyebrow eyebrow--light hero__reveal hero__reveal--eyebrow">
            <span></span>
            Dịch vụ ô tô chuyên nghiệp
        </p>

        <h1 class="hero__title hero__reveal hero__reveal--title" id="hero-title">
            Chăm sóc xe toàn diện
            <span>An tâm trên mọi hành trình</span>
        </h1>

        <p class="hero__desc hero__reveal hero__reveal--desc">
            DriveHub đồng hành cùng bạn trong việc bảo dưỡng và chăm sóc ô tô,
            hướng đến sự an toàn, bền bỉ và hiệu suất vận hành tối ưu.
        </p>

        <div class="hero__actions hero__reveal hero__reveal--actions">
            <a class="btn btn--primary" href="#dich-vu">Khám phá dịch vụ</a>
            <a class="btn btn--ghost" href="<?= BASE_URL ?>?action=booking">Đặt lịch ngay</a>
        </div>

        <ul class="hero__points hero__reveal hero__reveal--points">
            <li>
                <span class="hero__point-mark" aria-hidden="true"></span>
                <span>
                    <strong>Bảo dưỡng định kỳ</strong>
                    Theo dõi tình trạng xe theo từng mốc vận hành.
                </span>
            </li>
            <li>
                <span class="hero__point-mark" aria-hidden="true"></span>
                <span>
                    <strong>Kiểm tra chuyên sâu</strong>
                    Phát hiện sớm các dấu hiệu hư hỏng cần xử lý.
                </span>
            </li>
            <li>
                <span class="hero__point-mark" aria-hidden="true"></span>
                <span>
                    <strong>Chi phí minh bạch</strong>
                    Tư vấn rõ hạng mục trước khi thực hiện.
                </span>
            </li>
        </ul>
    </div>
</section>


<!-- ============================================================
     GIỚI THIỆU DRIVEHUB
============================================================ -->
<section class="section intro" id="gioi-thieu" aria-labelledby="intro-title">
    <div class="container intro__grid">
        <div class="intro__media" data-reveal="media">
            <img
                src="<?= BASE_URL ?>assets/images/intro-garage.jpg"
                alt="Không gian gara hiện đại của DriveHub"
                loading="lazy">
        </div>

        <div class="intro__body" data-reveal>
            <p class="eyebrow"><span></span>Về DriveHub</p>

            <h2 class="section__title" id="intro-title">
                DriveHub &ndash; Đồng hành cùng chiếc xe của bạn
            </h2>

            <p class="intro__lead">
                Bảo dưỡng ô tô định kỳ không chỉ giúp xe vận hành ổn định mà còn
                góp phần quan trọng vào sự an toàn của bạn và những người trên xe.
            </p>

            <p class="intro__text">
                DriveHub hướng đến trải nghiệm dịch vụ minh bạch và thuận tiện:
                kiểm tra tình trạng xe cẩn thận, tư vấn rõ ràng và thực hiện đúng
                những hạng mục cần thiết. Mỗi chiếc xe đều được chăm sóc theo quy
                trình phù hợp, hạn chế tối đa phát sinh ngoài kế hoạch.
            </p>

            <ul class="check-list">
                <li>Kiểm tra tình trạng xe trước khi thực hiện bảo dưỡng.</li>
                <li>Quy trình làm việc rõ ràng, dễ theo dõi.</li>
                <li>Tư vấn minh bạch về hạng mục và chi phí.</li>
                <li>Ưu tiên sự an toàn và tuổi thọ của phương tiện.</li>
            </ul>

            <a class="text-link" href="#quy-trinh">
                Xem quy trình bảo dưỡng
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>
</section>


<!-- ============================================================
     DỊCH VỤ NỔI BẬT
============================================================ -->
<section class="section services" id="dich-vu" aria-labelledby="services-title">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><span></span>Dịch vụ nổi bật</p>
            <h2 class="section__title" id="services-title">
                Chăm sóc xe toàn diện, đúng nhu cầu
            </h2>
            <p class="section__desc">
                Các dịch vụ bảo dưỡng và kiểm tra được DriveHub thực hiện theo
                quy trình kỹ thuật, phù hợp với tình trạng thực tế của từng xe.
            </p>
        </header>

        <div class="service-grid">
            <!-- Bảo dưỡng định kỳ -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-maintenance.jpg"
                         alt="Xe được nâng kiểm tra tại gara DriveHub"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.5 5.5a6 6 0 0 0-7.8 7.8L5 21l6 6 7.7-7.7a6 6 0 0 0 7.8-7.8l-3.8 3.8-4.2-4.2 3.8-3.8Z" />
                            <path d="M8 24l-2 2" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Bảo dưỡng định kỳ</h3>
                    <p class="service-card__desc">
                        Kiểm tra tổng thể theo định kỳ, giúp xe vận hành ổn định và
                        hạn chế hư hỏng.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Bảo dưỡng định kỳ">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            <!-- Thay dầu động cơ -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-oil.jpg"
                         alt="Kỹ thuật viên thay dầu động cơ"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4s8 9 8 14a8 8 0 0 1-16 0c0-5 8-14 8-14Z" />
                            <path d="M12 19a4 4 0 0 0 4 4" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Thay dầu động cơ</h3>
                    <p class="service-card__desc">
                        Thay dầu đúng chủng loại, giúp động cơ bền bỉ và hoạt động êm ái.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Thay dầu động cơ">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            <!-- Kiểm tra hệ thống phanh -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-brake.jpg"
                         alt="Kỹ thuật viên kiểm tra hệ thống phanh"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="16" cy="16" r="10" />
                            <circle cx="16" cy="16" r="4" />
                            <path d="M16 6v4M16 22v4M6 16h4M22 16h4" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Kiểm tra hệ thống phanh</h3>
                    <p class="service-card__desc">
                        Kiểm tra má phanh, đĩa phanh và dầu phanh để đảm bảo khả năng
                        dừng an toàn.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Kiểm tra hệ thống phanh">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            <!-- Kiểm tra lốp và bánh xe -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-tire.jpg"
                         alt="Bánh xe và cụm phanh được kiểm tra"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="16" cy="16" r="10" />
                            <circle cx="16" cy="16" r="5" />
                            <path d="M16 11v10M11 16h10M13 13l6 6M19 13l-6 6" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Kiểm tra lốp và bánh xe</h3>
                    <p class="service-card__desc">
                        Kiểm tra độ mòn, áp suất và cân bằng bánh xe, tăng độ ổn định
                        khi di chuyển.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Kiểm tra lốp và bánh xe">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            <!-- Kiểm tra ắc quy -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-battery.jpg"
                         alt="Kiểm tra ắc quy trong khoang động cơ"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="10" width="20" height="14" rx="2" />
                            <path d="M10 10V7h5v3M19 10V7h3v3M11 17h4M13 15v4M20 15v4M18.5 17h3" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Kiểm tra ắc quy</h3>
                    <p class="service-card__desc">
                        Đo tình trạng ắc quy, đảm bảo khả năng khởi động và nguồn điện
                        ổn định.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Kiểm tra ắc quy">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            <!-- Kiểm tra điều hòa ô tô -->
            <article class="service-card" data-reveal data-motion-card>
                <div class="service-card__media">
                    <img src="<?= BASE_URL ?>assets/images/service-ac.jpg"
                         alt="Cửa gió điều hòa trên xe ô tô"
                         loading="lazy">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4v24M6 10l20 12M26 10L6 22" />
                            <path d="M13 6l3 3 3-3M13 26l3-3 3 3M6 13l4 1-1-4M26 19l-4-1 1 4M26 13l-4 1 1-4M6 19l4-1-1 4" />
                        </svg>
                    </span>
                </div>
                <div class="service-card__body">
                    <h3 class="service-card__title">Kiểm tra điều hòa ô tô</h3>
                    <p class="service-card__desc">
                        Kiểm tra hệ thống làm mát, gas và lọc gió để duy trì không gian
                        thoải mái.
                    </p>
                    <a class="service-card__link" href="#" data-pending="Chi tiết dịch vụ Kiểm tra điều hòa ô tô">
                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>
        </div>
    </div>
</section>


<!-- ============================================================
     QUY TRÌNH BẢO DƯỠNG
============================================================ -->
<section class="section process" id="quy-trinh" aria-labelledby="process-title">
    <div class="container">
        <header class="section-head section-head--center" data-reveal>
            <p class="eyebrow eyebrow--light"><span></span>Quy trình bảo dưỡng</p>
            <h2 class="section__title section__title--light" id="process-title">
                Rõ ràng từng bước, an tâm mỗi hành trình
            </h2>
            <p class="section__desc section__desc--light">
                DriveHub thực hiện bảo dưỡng theo quy trình năm bước, giúp khách hàng
                nắm rõ tình trạng xe và phương án xử lý trước khi tiến hành.
            </p>
        </header>

        <ol class="process__list">
            <li class="process__step" data-reveal>
                <span class="process__num">01</span>
                <h3>Tiếp nhận thông tin xe</h3>
                <p>Ghi nhận thông tin xe, tình trạng hiện tại và yêu cầu của khách hàng.</p>
            </li>
            <li class="process__step" data-reveal>
                <span class="process__num">02</span>
                <h3>Kiểm tra tình trạng xe</h3>
                <p>Kiểm tra tổng thể các hệ thống để xác định tình trạng thực tế.</p>
            </li>
            <li class="process__step" data-reveal>
                <span class="process__num">03</span>
                <h3>Tư vấn phương án bảo dưỡng</h3>
                <p>Trao đổi rõ hạng mục, phương án và chi phí dự kiến trước khi thực hiện.</p>
            </li>
            <li class="process__step" data-reveal>
                <span class="process__num">04</span>
                <h3>Tiến hành bảo dưỡng</h3>
                <p>Thực hiện các hạng mục đã thống nhất theo đúng quy trình kỹ thuật.</p>
            </li>
            <li class="process__step" data-reveal>
                <span class="process__num">05</span>
                <h3>Kiểm tra và bàn giao xe</h3>
                <p>Kiểm tra lại kết quả, vệ sinh và bàn giao xe cho khách hàng.</p>
            </li>
        </ol>
    </div>
</section>


<!-- ============================================================
     ĐÁNH GIÁ KHÁCH HÀNG
     Dự án chưa có đánh giá thật nên nội dung dưới đây là mẫu minh họa.
============================================================ -->
<section class="section feedback" id="danh-gia" aria-labelledby="feedback-title">
    <div class="container">
        <header class="section-head section-head--center" data-reveal>
            <p class="eyebrow"><span></span>Đánh giá khách hàng</p>
            <h2 class="section__title" id="feedback-title">
                Trải nghiệm được đặt lên hàng đầu
            </h2>
            <p class="section__desc">
                Khu vực đánh giá đang chờ dữ liệu thực tế từ khách hàng. Nội dung
                bên dưới chỉ mang tính minh họa cho giao diện.
            </p>
        </header>

        <div class="feedback-grid">
            <figure class="feedback-card" data-reveal>
                <span class="feedback-card__badge">Nội dung minh họa</span>
                <blockquote>
                    Quy trình bảo dưỡng được giải thích rõ ràng, tôi nắm được các
                    hạng mục trước khi thực hiện.
                </blockquote>
                <figcaption>Đánh giá mẫu &ndash; sẽ thay thế bằng phản hồi thật</figcaption>
            </figure>

            <figure class="feedback-card" data-reveal>
                <span class="feedback-card__badge">Nội dung minh họa</span>
                <blockquote>
                    Không gian gara gọn gàng, việc kiểm tra xe được thực hiện cẩn
                    thận.
                </blockquote>
                <figcaption>Đánh giá mẫu &ndash; sẽ thay thế bằng phản hồi thật</figcaption>
            </figure>

            <figure class="feedback-card" data-reveal>
                <span class="feedback-card__badge">Nội dung minh họa</span>
                <blockquote>
                    Tôi đánh giá cao sự minh bạch trong tư vấn và cách theo dõi
                    tình trạng xe.
                </blockquote>
                <figcaption>Đánh giá mẫu &ndash; sẽ thay thế bằng phản hồi thật</figcaption>
            </figure>
        </div>
    </div>
</section>
