(function () {
    const els = document.querySelectorAll(".reveal");
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add("visible");
                    observer.unobserve(e.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    els.forEach((el) => observer.observe(el));

    // Trigger hero immediately
    document.querySelectorAll(".hero .reveal").forEach((el) => {
        setTimeout(() => el.classList.add("visible"), 100);
    });
})();
