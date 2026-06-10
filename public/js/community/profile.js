document.addEventListener("DOMContentLoaded", () => {
    /* =====================================================
       TABS
    ===================================================== */

    const tabs = document.querySelectorAll(".profile-tab");
    const panels = document.querySelectorAll(".profile-tab-panel");

    // Check if returning from password update

    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            activateTab(tab.dataset.tab);
        });
    });

    function activateTab(name) {
        tabs.forEach((t) => t.classList.remove("active"));
        panels.forEach((p) => p.classList.remove("active"));

        const targetTab = document.querySelector(
            `.profile-tab[data-tab="${name}"]`,
        );
        const targetPanel = document.getElementById(`tab-${name}`);

        if (targetTab) targetTab.classList.add("active");
        if (targetPanel) targetPanel.classList.add("active");
    }

    /* =====================================================
       BIO CHARACTER COUNT
    ===================================================== */

    const bioTextarea = document.querySelector('textarea[name="bio"]');
    const bioCount = document.getElementById("bioCount");

    if (bioTextarea && bioCount) {
        bioTextarea.addEventListener("input", () => {
            bioCount.textContent = bioTextarea.value.length;
        });
    }

    /* =====================================================
       PASSWORD VISIBILITY TOGGLE
    ===================================================== */

    document.querySelectorAll(".toggle-pass").forEach((btn) => {
        btn.addEventListener("click", () => {
            const input = document.getElementById(btn.dataset.target);
            if (!input) return;
            input.type = input.type === "password" ? "text" : "password";
        });
    });

    /* =====================================================
       PASSWORD STRENGTH
    ===================================================== */

    const newPassInput = document.getElementById("newPass");
    const strengthFill = document.getElementById("strengthFill");
    const strengthLabel = document.getElementById("strengthLabel");

    if (newPassInput) {
        newPassInput.addEventListener("input", () => {
            const val = newPassInput.value;
            const score = getPasswordScore(val);

            const levels = [
                { width: "0%", color: "#eee", label: "" },
                { width: "25%", color: "#ef4444", label: "Weak" },
                { width: "50%", color: "#f97316", label: "Fair" },
                { width: "75%", color: "#eab308", label: "Good" },
                { width: "100%", color: "#22c55e", label: "Strong" },
            ];

            const level = levels[score];
            strengthFill.style.width = level.width;
            strengthFill.style.background = level.color;
            strengthLabel.textContent = level.label;
            strengthLabel.style.color = level.color;
        });
    }

    function getPasswordScore(password) {
        if (!password) return 0;
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return score;
    }

    /* =====================================================
       PHOTO CROP MODAL
    ===================================================== */

    const avatarTrigger = document.getElementById("avatarTrigger");
    const cropModal = document.getElementById("cropModal");
    const cropBackdrop = document.getElementById("cropBackdrop");
    const cropClose = document.getElementById("cropClose");
    const cropUploadZone = document.getElementById("cropUploadZone");
    const photoFileInput = document.getElementById("photoFileInput");
    const cropCanvasWrap = document.getElementById("cropCanvasWrap");
    const cropImage = document.getElementById("cropImage");
    const cropFooter = document.getElementById("cropFooter");
    const cropCancel = document.getElementById("cropCancel");
    const cropForm = document.getElementById("cropForm");
    const croppedInput = document.getElementById("croppedPhotoInput");

    let cropper = null;

    function openModal() {
        cropModal.classList.add("open");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        cropModal.classList.remove("open");
        document.body.style.overflow = "";
        resetCrop();
    }

    function resetCrop() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropUploadZone.style.display = "flex";
        cropCanvasWrap.style.display = "none";
        cropFooter.style.display = "none";
        cropImage.src = "";
        photoFileInput.value = "";
    }

    avatarTrigger?.addEventListener("click", openModal);
    cropBackdrop?.addEventListener("click", closeModal);
    cropClose?.addEventListener("click", closeModal);
    cropCancel?.addEventListener("click", resetCrop);

    // Click on upload zone triggers file input
    cropUploadZone?.addEventListener("click", () => photoFileInput.click());

    // Drag and drop
    cropUploadZone?.addEventListener("dragover", (e) => {
        e.preventDefault();
        cropUploadZone.classList.add("drag-over");
    });

    cropUploadZone?.addEventListener("dragleave", () => {
        cropUploadZone.classList.remove("drag-over");
    });

    cropUploadZone?.addEventListener("drop", (e) => {
        e.preventDefault();
        cropUploadZone.classList.remove("drag-over");
        const file = e.dataTransfer.files[0];
        if (file) loadFile(file);
    });

    photoFileInput?.addEventListener("change", () => {
        const file = photoFileInput.files[0];
        if (file) loadFile(file);
    });

    function loadFile(file) {
        // 5 MB limit
        if (file.size > 5 * 1024 * 1024) {
            alert("Image must be under 5 MB.");
            return;
        }

        if (!["image/jpeg", "image/png"].includes(file.type)) {
            alert("Only JPG and PNG files are allowed.");
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            cropImage.src = e.target.result;

            cropUploadZone.style.display = "none";
            cropCanvasWrap.style.display = "block";
            cropFooter.style.display = "flex";

            if (cropper) cropper.destroy();

            cropper = new Cropper(cropImage, {
                aspectRatio: 1, // Square crop
                viewMode: 2,
                dragMode: "move",
                autoCropArea: 0.85,
                responsive: true,
                background: false,
            });
        };

        reader.readAsDataURL(file);
    }

    // On form submit — get cropped canvas as base64 and put in hidden input
    cropForm?.addEventListener("submit", (e) => {
        if (!cropper) return;

        e.preventDefault();

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingQuality: "high",
        });

        croppedInput.value = canvas.toDataURL("image/jpeg", 0.88);
        cropForm.submit();
    });
});
