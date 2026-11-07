/* ============================================================
   DRIVEHUB - TƯƠNG TÁC GIAO DIỆN
============================================================ */

document.documentElement.classList.add("js-enabled");

document.addEventListener("DOMContentLoaded", function () {
    var header = document.getElementById("site-header");
    var navToggle = document.querySelector(".nav-toggle");
    var navLinks = document.querySelectorAll(".site-nav a");
    var pendingLinks = document.querySelectorAll("[data-pending]");
    var revealItems = document.querySelectorAll("[data-reveal]");
    var desktopQuery = window.matchMedia("(min-width: 961px)");
    var reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
    var finePointerQuery = window.matchMedia("(hover: hover) and (pointer: fine)");

    function prefersReducedMotion() {
        return reducedMotionQuery.matches;
    }

    /* ---------- Sticky header ---------- */
    function updateHeader() {
        if (!header) {
            return;
        }

        header.classList.toggle("is-scrolled", window.scrollY > 12);
    }

    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    /* ---------- Menu mobile ---------- */
    function closeMenu() {
        if (!header || !navToggle) {
            return;
        }

        header.classList.remove("nav-open");
        navToggle.setAttribute("aria-expanded", "false");
        navToggle.setAttribute("aria-label", "Mở menu");
    }

    var profileMenu = document.querySelector("[data-profile-menu]");
    var profileTrigger = profileMenu ? profileMenu.querySelector(".profile-trigger") : null;
    var profileDropdown = profileMenu ? profileMenu.querySelector(".profile-dropdown") : null;
    var profileCloseTimer = null;

    function setProfileOpen(isOpen) {
        if (!profileMenu || !profileTrigger || !profileDropdown) {
            return;
        }

        window.clearTimeout(profileCloseTimer);
        profileTrigger.setAttribute("aria-expanded", isOpen ? "true" : "false");

        if (isOpen) {
            profileDropdown.hidden = false;
            window.requestAnimationFrame(function () {
                if (profileTrigger.getAttribute("aria-expanded") === "true") {
                    profileMenu.classList.add("is-open");
                }
            });
            return;
        }

        profileMenu.classList.remove("is-open");
        profileCloseTimer = window.setTimeout(function () {
            if (!profileMenu.classList.contains("is-open")) {
                profileDropdown.hidden = true;
            }
        }, prefersReducedMotion() ? 0 : 220);
    }

    if (profileTrigger && profileMenu) {
        profileTrigger.addEventListener("click", function () {
            setProfileOpen(profileTrigger.getAttribute("aria-expanded") !== "true");
        });

        document.addEventListener("pointerdown", function (event) {
            if (!profileMenu.contains(event.target)) {
                setProfileOpen(false);
            }
        });

        navLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                setProfileOpen(false);
            });
        });
    }

    if (navToggle && header) {
        navToggle.addEventListener("click", function () {
            var isOpen = header.classList.toggle("nav-open");
            navToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            navToggle.setAttribute("aria-label", isOpen ? "Đóng menu" : "Mở menu");
            setProfileOpen(false);
        });
    }

    navLinks.forEach(function (link) {
        link.addEventListener("click", closeMenu);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            if (profileTrigger && profileTrigger.getAttribute("aria-expanded") === "true") {
                setProfileOpen(false);
                profileTrigger.focus();
            }
            closeMenu();
        }
    });

    if (desktopQuery.addEventListener) {
        desktopQuery.addEventListener("change", function (event) {
            if (event.matches) {
                closeMenu();
                setProfileOpen(false);
            }
        });
    }

    /* ---------- Các liên kết chờ tích hợp route ---------- */
    pendingLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
        });
    });

    /* ---------- Hiệu ứng reveal khi cuộn ---------- */
    function setRevealDelays() {
        var groups = document.querySelectorAll(
            ".service-grid, .services-grid, .process__list, .services-process__list, .feedback-grid, .services-faq__list"
        );

        groups.forEach(function (group) {
            Array.prototype.forEach.call(group.children, function (item, index) {
                if (item.hasAttribute("data-reveal")) {
                    item.style.setProperty("--reveal-delay", Math.min(index * 80, 400) + "ms");
                }
            });
        });
    }

    setRevealDelays();

    if (prefersReducedMotion() || !("IntersectionObserver" in window)) {
        revealItems.forEach(function (item) {
            item.classList.add("is-visible");
        });
    } else if (revealItems.length) {
        var observer = new IntersectionObserver(
            function (entries, obs) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add("is-visible");
                    obs.unobserve(entry.target);
                });
            },
            { threshold: 0.14, rootMargin: "0px 0px -40px 0px" }
        );

        revealItems.forEach(function (item) {
            observer.observe(item);
        });
    }

    /* ---------- Hero spotlight và parallax ---------- */
    var hero = document.querySelector(".hero");

    if (hero && !prefersReducedMotion() && finePointerQuery.matches) {
        var heroFrame = null;
        var heroPointer = { x: 0, y: 0 };

        function renderHeroMotion() {
            var bounds = hero.getBoundingClientRect();
            var x = Math.min(Math.max((heroPointer.x - bounds.left) / bounds.width, 0), 1);
            var y = Math.min(Math.max((heroPointer.y - bounds.top) / bounds.height, 0), 1);

            hero.style.setProperty("--hero-pointer-x", (x * 100).toFixed(2) + "%");
            hero.style.setProperty("--hero-pointer-y", (y * 100).toFixed(2) + "%");
            hero.style.setProperty("--hero-media-x", ((x - 0.5) * 14).toFixed(2) + "px");
            hero.style.setProperty("--hero-media-y", ((y - 0.5) * 10).toFixed(2) + "px");
            heroFrame = null;
        }

        function resetHeroMotion() {
            if (heroFrame !== null) {
                window.cancelAnimationFrame(heroFrame);
                heroFrame = null;
            }

            hero.classList.remove("has-pointer-motion");
            hero.style.setProperty("--hero-pointer-x", "50%");
            hero.style.setProperty("--hero-pointer-y", "50%");
            hero.style.setProperty("--hero-media-x", "0px");
            hero.style.setProperty("--hero-media-y", "0px");
        }

        hero.addEventListener("pointermove", function (event) {
            heroPointer.x = event.clientX;
            heroPointer.y = event.clientY;
            hero.classList.add("has-pointer-motion");

            if (heroFrame === null) {
                heroFrame = window.requestAnimationFrame(renderHeroMotion);
            }
        }, { passive: true });

        hero.addEventListener("pointerleave", resetHeroMotion);
    }

    /* ---------- Card tilt tinh tế ---------- */
    if (!prefersReducedMotion() && finePointerQuery.matches) {
        document.querySelectorAll("[data-motion-card]").forEach(function (card) {
            var cardFrame = null;
            var cardPointer = { x: 0, y: 0 };

            function renderCardTilt() {
                var bounds = card.getBoundingClientRect();
                var x = (cardPointer.x - bounds.left) / bounds.width;
                var y = (cardPointer.y - bounds.top) / bounds.height;

                card.style.setProperty("--card-rotate-x", ((0.5 - y) * 3.2).toFixed(2) + "deg");
                card.style.setProperty("--card-rotate-y", ((x - 0.5) * 3.2).toFixed(2) + "deg");
                cardFrame = null;
            }

            function resetCardTilt() {
                if (cardFrame !== null) {
                    window.cancelAnimationFrame(cardFrame);
                    cardFrame = null;
                }

                card.style.setProperty("--card-rotate-x", "0deg");
                card.style.setProperty("--card-rotate-y", "0deg");
            }

            card.addEventListener("pointermove", function (event) {
                cardPointer.x = event.clientX;
                cardPointer.y = event.clientY;

                if (cardFrame === null) {
                    cardFrame = window.requestAnimationFrame(renderCardTilt);
                }
            }, { passive: true });

            card.addEventListener("pointerleave", resetCardTilt);
        });
    }

    /* ---------- Trạng thái gửi biểu mẫu ---------- */
    document.querySelectorAll(".booking-form, .booking-review, .workflow-form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            var submitter = event.submitter;

            if (!submitter || !submitter.matches("button")) {
                return;
            }

            submitter.classList.add("is-submitting");
            submitter.setAttribute("aria-busy", "true");
        });
    });

    /* ---------- Chuyển trang nhẹ cho liên kết nội bộ ---------- */
    if (!prefersReducedMotion()) {
        var isNavigating = false;

        document.addEventListener("click", function (event) {
            var link = event.target.closest("a[href]");

            if (
                !link ||
                event.defaultPrevented ||
                event.button !== 0 ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey ||
                link.target ||
                link.hasAttribute("download") ||
                isNavigating
            ) {
                return;
            }

            var targetUrl = new URL(link.href, window.location.href);
            var currentUrl = new URL(window.location.href);
            var isSamePageAnchor = targetUrl.pathname === currentUrl.pathname
                && targetUrl.search === currentUrl.search
                && targetUrl.hash !== "";

            if (targetUrl.origin !== currentUrl.origin || isSamePageAnchor) {
                return;
            }

            isNavigating = true;
            event.preventDefault();
            document.body.classList.add("is-page-leaving");

            window.setTimeout(function () {
                window.location.assign(targetUrl.href);
            }, 160);
        });

        window.addEventListener("pageshow", function () {
            document.body.classList.remove("is-page-leaving");
            isNavigating = false;
        });
    }
});
