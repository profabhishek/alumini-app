document.getElementById("reloadCaptcha").addEventListener("click", function () {
    fetch("{{ route('refresh.captcha') }}")
        .then((response) => response.json())
        .then((data) => {
            document.getElementById("captcha-image").innerHTML = data.captcha;
        });
});
