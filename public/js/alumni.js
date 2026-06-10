document.addEventListener("DOMContentLoaded", () => {
    /* ===================================
       REVEAL ANIMATION
    =================================== */

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("show");
                }
            });
        },
        {
            threshold: 0.15,
        },
    );

    document.querySelectorAll(".alumni-card").forEach((card) => {
        card.classList.add("reveal-card");
        observer.observe(card);
    });

    /* ===================================
       IMAGE FALLBACK
    =================================== */

    document.querySelectorAll(".alumni-image").forEach((img) => {
        img.addEventListener("error", function () {
            this.src =
                "https://via.placeholder.com/600x800/f0ede7/555555?text=Alumni";
        });
    });

    /* ===================================
       SEARCH ENTER KEY
    =================================== */

    // Replace the SEARCH ENTER KEY section with this:

    const searchInput = document.querySelector(".search-box input");
    const form = document.querySelector(".toolbar-form");

    if (searchInput) {
        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                form.submit();
            }
        });
    }

    // Auto-submit on filter change
    document.querySelectorAll(".filter-box select").forEach((select) => {
        select.addEventListener("change", function () {
            form.submit();
        });
    });

    /* ===================================
       SCROLL TO TOP AFTER PAGINATION
    =================================== */

    const paginationLinks = document.querySelectorAll(".pagination a");

    paginationLinks.forEach((link) => {
        link.addEventListener("click", () => {
            sessionStorage.setItem("alumniScrollTop", "true");
        });
    });

    if (sessionStorage.getItem("alumniScrollTop")) {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });

        sessionStorage.removeItem("alumniScrollTop");
    }
});
