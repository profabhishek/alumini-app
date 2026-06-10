(function () {
    "use strict";

    /* ── DOM REFS ──────────────────────────────────────────── */
    const header = document.getElementById("commHeader");
    const sidebar = document.getElementById("commSidebar");
    const hamburger = document.getElementById("sidebarToggle");
    const overlay = document.getElementById("sidebarOverlay");
    const userMenu = document.getElementById("userMenuToggle");
    const userDropdown = document.getElementById("userDropdown");

    if (header) {
        const onScroll = () => {
            header.classList.toggle("scrolled", window.scrollY > 8);
        };
        window.addEventListener("scroll", onScroll, { passive: true });
        onScroll();
    }

    /* ── 2. SIDEBAR OPEN / CLOSE ───────────────────────────── */
    function openSidebar() {
        sidebar.classList.add("open");
        overlay.classList.add("active");
        hamburger.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        sidebar.classList.remove("open");
        overlay.classList.remove("active");
        hamburger.classList.remove("active");
        document.body.style.overflow = "";
    }

    if (hamburger) {
        hamburger.addEventListener("click", () => {
            sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
        });
    }

    /* Close sidebar when overlay is clicked */
    if (overlay) {
        overlay.addEventListener("click", closeSidebar);
    }

    /* Close sidebar on Escape key */
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeSidebar();
            closeUserMenu();
        }
    });

    /* Re-open sidebar if window resizes back to desktop */
    window.addEventListener("resize", () => {
        if (window.innerWidth > 980) {
            closeSidebar(); // reset mobile state; sidebar shows via CSS at desktop
            document.body.style.overflow = "";
        }
    });

    /* ── 3. USER DROPDOWN ──────────────────────────────────── */
    function openUserMenu() {
        userMenu.classList.add("open");
        if (userDropdown) userDropdown.style.display = "block";
    }

    function closeUserMenu() {
        userMenu.classList.remove("open");
        if (userDropdown) userDropdown.style.display = "";
    }

    if (userMenu) {
        userMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            userMenu.classList.contains("open")
                ? closeUserMenu()
                : openUserMenu();
        });
    }

    /* Close dropdown when clicking anywhere outside */
    document.addEventListener("click", (e) => {
        if (userMenu && !userMenu.contains(e.target)) {
            closeUserMenu();
        }
    });

    /* ── 4. EXPANDABLE NAV ITEMS (chevron toggle) ──────────── */
    const expandableItems = document.querySelectorAll(
        ".sidebar-nav__item--expandable",
    );

    expandableItems.forEach((item) => {
        item.addEventListener("click", (e) => {
            e.preventDefault(); // these are # links; prevent jump

            const isExpanded = item.classList.contains("expanded");

            // Close all others first (accordion behaviour)
            expandableItems.forEach((other) => {
                if (other !== item) {
                    other.classList.remove("expanded");
                    const sub = other.nextElementSibling;
                    if (sub && sub.classList.contains("sidebar-submenu")) {
                        sub.classList.remove("open");
                    }
                }
            });

            // Toggle clicked item
            item.classList.toggle("expanded", !isExpanded);

            // If a .sidebar-submenu immediately follows, open it too
            const submenu = item.nextElementSibling;
            if (submenu && submenu.classList.contains("sidebar-submenu")) {
                submenu.classList.toggle("open", !isExpanded);
            }
        });
    });

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
        .classList.contains("open");
    // Close all first
    keys.forEach((k) => {
        document.getElementById("kids-" + k).classList.remove("open");
        document.getElementById("chev-" + k).classList.remove("open");
    });
    // Open clicked one if it was closed
    if (!isOpen) {
        document.getElementById("kids-" + key).classList.add("open");
        document.getElementById("chev-" + key).classList.add("open");
    }
}
