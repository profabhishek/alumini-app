async function startDirectChat(alumniId, btn) {
    let originalHtml = null;

    if (btn) {
        originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = "Starting…";
    }

    try {
        const base = (window.APP_BASE_URL || '').replace(/\/$/, '');
        const res = await fetch(base + "/chat/direct", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            // ChatController::startDirect() validates 'user_id', not 'alumni_id'.
            body: JSON.stringify({ user_id: alumniId }),
        });

        if (!res.ok) throw new Error("Failed to start conversation");

        const data = await res.json();

        // Response shape: { conversation: { id, ... } }
        const conversationId = data.conversation?.id ?? null;

        window.location.href = conversationId
            ? `${base}/chat?conversation=${conversationId}`
            : `${base}/chat`;
    } catch (err) {
        window.location.href = `${base}/chat`;
    } finally {
        if (btn && originalHtml !== null) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}
