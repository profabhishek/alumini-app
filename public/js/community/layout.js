(function () {
    "use strict";

    /* ── DOM REFS ──────────────────────────────────────────── */
    const header = document.getElementById("commHeader");
    const sidebar = document.getElementById("commSidebar");
    const hamburger = document.getElementById("sidebarToggle");
    const overlay = document.getElementById("sidebarOverlay");
    const userMenu = document.getElementById("userMenuToggle");
    const userDropdown = document.getElementById("userDropdown");

    /* ── SIDEBAR FUNCTIONS ─────────────────────────────────── */
    function openSidebar() {
        if (!sidebar || !overlay || !hamburger) return;
        sidebar.classList.add("open");
        overlay.classList.add("active");
        hamburger.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        if (!sidebar || !overlay || !hamburger) return;
        sidebar.classList.remove("open");
        overlay.classList.remove("active");
        hamburger.classList.remove("active");
        document.body.style.overflow = "";
    }

    /* ── USER MENU FUNCTIONS ───────────────────────────────── */
    function openUserMenu() {
        if (!userMenu) return;
        userMenu.classList.add("open");
        if (userDropdown) userDropdown.style.display = "block";
    }

    function closeUserMenu() {
        if (!userMenu) return;
        userMenu.classList.remove("open");
        if (userDropdown) userDropdown.style.display = "";
    }

    /* ── HEADER SCROLL ─────────────────────────────────────── */
    if (header) {
        const onScroll = () => {
            header.classList.toggle("scrolled", window.scrollY > 8);
        };
        window.addEventListener("scroll", onScroll, { passive: true });
        onScroll();
    }

    /* ── SIDEBAR LISTENERS ─────────────────────────────────── */
    if (hamburger) {
        hamburger.addEventListener("click", () => {
            sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener("click", closeSidebar);
    }

    window.addEventListener("resize", () => {
        if (window.innerWidth > 980) {
            closeSidebar();
            document.body.style.overflow = "";
        }
    });

    /* ── ESCAPE KEY ────────────────────────────────────────── */
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeSidebar();
            closeUserMenu();
        }
    });

    /* ── USER MENU LISTENERS ───────────────────────────────── */
    if (userMenu) {
        userMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            userMenu.classList.contains("open")
                ? closeUserMenu()
                : openUserMenu();
        });
    }

    document.addEventListener("click", (e) => {
        if (userMenu && !userMenu.contains(e.target)) {
            closeUserMenu();
        }
    });

    /* ── EXPANDABLE NAV ────────────────────────────────────── */
    const expandableItems = document.querySelectorAll(
        ".sidebar-nav__item--expandable",
    );

    expandableItems.forEach((item) => {
        item.addEventListener("click", (e) => {
            e.preventDefault();
            const isExpanded = item.classList.contains("expanded");

            expandableItems.forEach((other) => {
                if (other !== item) {
                    other.classList.remove("expanded");
                    const sub = other.nextElementSibling;
                    if (sub && sub.classList.contains("sidebar-submenu")) {
                        sub.classList.remove("open");
                    }
                }
            });

            item.classList.toggle("expanded", !isExpanded);
            const submenu = item.nextElementSibling;
            if (submenu && submenu.classList.contains("sidebar-submenu")) {
                submenu.classList.toggle("open", !isExpanded);
            }
        });
    });

    /* ── ACTIVE NAV ITEM ───────────────────────────────────── */
    const currentPath = window.location.pathname;
    document.querySelectorAll(".sidebar-nav__item").forEach((item) => {
        const href = item.getAttribute("href");
        if (href && href !== "#" && currentPath.endsWith(href)) {
            item.classList.add("active");
        }
    });
})();

function toggleSidebarMenu(key) {
    const keys = ["event", "job", "story"];
    const isOpen = document
        .getElementById("kids-" + key)
        ?.classList.contains("open");

    keys.forEach((k) => {
        document.getElementById("kids-" + k)?.classList.remove("open");
        document.getElementById("chev-" + k)?.classList.remove("open");
    });

    if (!isOpen) {
        document.getElementById("kids-" + key)?.classList.add("open");
        document.getElementById("chev-" + key)?.classList.add("open");
    }
}
