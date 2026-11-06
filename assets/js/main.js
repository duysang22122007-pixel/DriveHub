/* ============================================================
   DRIVEHUB - TƯƠNG TÁC GIAO DIỆN TRANG CHỦ
============================================================ */

document.documentElement.classList.add("js-enabled");

document.addEventListener("DOMContentLoaded", function () {
    var header = document.getElementById("site-header");
    var navToggle = document.querySelector(".nav-toggle");
    var navLinks = document.querySelectorAll(".site-nav a");
    var pendingLinks = document.querySelectorAll("[data-pending]");
    var revealItems = document.querySelectorAll("[data-reveal]");
    var desktopQuery = window.matchMedia("(min-width: 961px)");

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

    if (navToggle && header) {
        navToggle.addEventListener("click", function () {
            var isOpen = header.classList.toggle("nav-open");
            navToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            navToggle.setAttribute("aria-label", isOpen ? "Đóng menu" : "Mở menu");
        });
    }

    navLinks.forEach(function (link) {
        link.addEventListener("click", closeMenu);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeMenu();
        }
    });

    if (desktopQuery.addEventListener) {
        desktopQuery.addEventListener("change", function (event) {
            if (event.matches) {
                closeMenu();
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
    if ("IntersectionObserver" in window && revealItems.length) {
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
    } else {
        revealItems.forEach(function (item) {
            item.classList.add("is-visible");
        });
    }
});
