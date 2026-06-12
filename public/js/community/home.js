/* public/js/community/home.js */
(function () {
    "use strict";

    /* ----------------------------------------------------------
       REVEAL ON SCROLL
    ---------------------------------------------------------- */
    const revealEls = document.querySelectorAll(".reveal");

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add("show");
                    }, i * 80);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 },
    );

    revealEls.forEach((el) => observer.observe(el));

    /* ----------------------------------------------------------
       MOBILE SIDEBAR TOGGLE
    ---------------------------------------------------------- */
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("commSidebar");
    const overlay = document.getElementById("sidebarOverlay");

    function openSidebar() {
        sidebar.classList.add("open");
        overlay.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        sidebar.classList.remove("open");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
    }

    if (sidebarToggle) sidebarToggle.addEventListener("click", openSidebar);
    if (overlay) overlay.addEventListener("click", closeSidebar);

    /* ----------------------------------------------------------
       USER DROPDOWN (click-based for accessibility)
    ---------------------------------------------------------- */
    const userMenu = document.getElementById("userMenuToggle");
    const userDropdown = document.getElementById("userDropdown");

    if (userMenu) {
        userMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            userMenu.classList.toggle("open");
        });

        document.addEventListener("click", () => {
            userMenu.classList.remove("open");
        });
    }

    /* ----------------------------------------------------------
       EXPANDABLE SIDEBAR NAV ITEMS (chevron)
    ---------------------------------------------------------- */
    document
        .querySelectorAll(".sidebar-nav__item--expandable")
        .forEach((item) => {
            item.addEventListener("click", function (e) {
                e.preventDefault();
                const chevron = this.querySelector(".nav-chevron");
                const isOpen = this.dataset.open === "true";
                this.dataset.open = isOpen ? "false" : "true";
                if (chevron)
                    chevron.style.transform = isOpen ? "" : "rotate(90deg)";
            });
        });

    /* ----------------------------------------------------------
       STICKY HEADER SHADOW ON SCROLL
    ---------------------------------------------------------- */
    const header = document.getElementById("commHeader");

    if (header) {
        window.addEventListener(
            "scroll",
            () => {
                header.style.boxShadow =
                    window.scrollY > 10
                        ? "0 2px 16px rgba(0,0,0,0.18)"
                        : "none";
            },
            { passive: true },
        );
    }
})();
