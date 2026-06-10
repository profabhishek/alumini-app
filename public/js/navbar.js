(function () {
    const nav = document.getElementById("mainNav");
    const hamburger = document.getElementById("hamburger");
    const drawer = document.getElementById("mobileDrawer");
    const overlay = document.getElementById("drawerOverlay");
    const drawerClose = document.getElementById("drawerClose");
    const searchToggle = document.getElementById("searchToggle");
    const searchClose = document.getElementById("searchClose");
    const searchOverlay = document.getElementById("searchOverlay");
    const searchInput = searchOverlay.querySelector(".nav-search-input");

    // ── Scroll glass effect ──────────────────────────
    const onScroll = () => {
        nav.classList.toggle("scrolled", window.scrollY > 10);
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    // ── Mobile drawer ────────────────────────────────
    const openDrawer = () => {
        drawer.classList.add("open");
        overlay.classList.add("open");
        hamburger.classList.add("open");
        hamburger.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
    };
    const closeDrawer = () => {
        drawer.classList.remove("open");
        overlay.classList.remove("open");
        hamburger.classList.remove("open");
        hamburger.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
    };

    hamburger.addEventListener("click", () => {
        drawer.classList.contains("open") ? closeDrawer() : openDrawer();
    });
    drawerClose.addEventListener("click", closeDrawer);
    overlay.addEventListener("click", closeDrawer);

    // Close drawer on nav link click
    drawer.querySelectorAll(".mobile-nav-link").forEach((link) => {
        link.addEventListener("click", closeDrawer);
    });

    // ── Search overlay ───────────────────────────────
    searchToggle.addEventListener("click", () => {
        searchOverlay.classList.add("open");
        setTimeout(() => searchInput.focus(), 50);
    });
    const closeSearch = () => searchOverlay.classList.remove("open");
    searchClose.addEventListener("click", closeSearch);
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSearch();
    });

    // ── Smooth scroll for anchor links ───────────────
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", (e) => {
            const target = document.querySelector(anchor.getAttribute("href"));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: "smooth", block: "start" });
                closeDrawer();
            }
        });
    });
})();
