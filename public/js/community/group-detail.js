(() => {
    "use strict";

    const btn = document.getElementById("groupAboutBtn");
    const modal = document.getElementById("groupAboutModal");
    const closeBtn = document.getElementById("groupAboutClose");

    if (!btn || !modal) return;

    function open() {
        modal.hidden = false;
        document.body.style.overflow = "hidden";
    }

    function close() {
        modal.hidden = true;
        document.body.style.overflow = "";
    }

    btn.addEventListener("click", open);
    closeBtn?.addEventListener("click", close);
    modal.addEventListener("click", (e) => {
        if (e.target === modal) close();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.hidden) close();
    });
})();
