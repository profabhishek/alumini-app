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
       LIKE TOGGLE
    ---------------------------------------------------------- */
    document.querySelectorAll(".like-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const liked = this.dataset.liked === "true";
            const countEl = this.querySelector(".action-count");
            const count = parseInt(countEl.textContent) || 0;

            this.dataset.liked = liked ? "false" : "true";
            countEl.textContent = liked ? count - 1 : count + 1;

            this.classList.toggle("liked", !liked);

            /* micro-bounce */
            this.style.transform = "scale(1.25)";
            setTimeout(() => {
                this.style.transform = "";
            }, 180);
        });
    });

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
       POST BUTTON (frontend-only stub)
    ---------------------------------------------------------- */
    const postBtn = document.getElementById("postBtn");
    const postTextarea = document.getElementById("postTextarea");

    if (postBtn && postTextarea) {
        postBtn.addEventListener("click", () => {
            const text = postTextarea.value.trim();
            if (!text) {
                postTextarea.focus();
                postTextarea.style.borderColor = "#e74c3c";
                setTimeout(() => {
                    postTextarea.style.borderColor = "";
                }, 1200);
                return;
            }
            /* Flash success state */
            postBtn.textContent = "Posted!";
            postBtn.style.background = "#27ae60";
            postTextarea.value = "";
            setTimeout(() => {
                postBtn.textContent = "Post Now";
                postBtn.style.background = "";
            }, 2000);
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
