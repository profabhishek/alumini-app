(function () {
    const nav = document.getElementById("mainNav");
    const hamburger = document.getElementById("hamburger");
    const drawer = document.getElementById("mobileDrawer");
    const overlay = document.getElementById("drawerOverlay");
    const drawerClose = document.getElementById("drawerClose");
    const searchToggle = document.getElementById("searchToggle");
    const searchClose = document.getElementById("searchClose");
    const searchOverlay = document.getElementById("searchOverlay");

    // ── Scroll glass effect ──────────────────────────
    if (nav) {
        const onScroll = () =>
            nav.classList.toggle("scrolled", window.scrollY > 10);
        window.addEventListener("scroll", onScroll, { passive: true });
        onScroll();
    }

    // ── Mobile drawer ────────────────────────────────
    if (hamburger && drawer && overlay) {
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
        if (drawerClose) drawerClose.addEventListener("click", closeDrawer);
        overlay.addEventListener("click", closeDrawer);

        drawer.querySelectorAll(".mobile-nav-link").forEach((link) => {
            link.addEventListener("click", closeDrawer);
        });
    }

    // ── Search overlay ───────────────────────────────
    if (searchToggle && searchOverlay && searchClose) {
        const searchInput = searchOverlay.querySelector(".nav-search-input");
        const closeSearch = () => searchOverlay.classList.remove("open");

        searchToggle.addEventListener("click", () => {
            searchOverlay.classList.add("open");
            if (searchInput) setTimeout(() => searchInput.focus(), 50);
        });
        searchClose.addEventListener("click", closeSearch);
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") closeSearch();
        });
    }

    // ── Smooth scroll for anchor links ───────────────
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", (e) => {
            const target = document.querySelector(anchor.getAttribute("href"));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });
})();
