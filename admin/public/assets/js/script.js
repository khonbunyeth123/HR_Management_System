document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll("nav a");
    const contentDiv = document.getElementById("content");

    function loadPage(page) {
        fetch(`index.php?page=${page}&ajax=1`)
            .then(res => res.text())
            .then(html => contentDiv.innerHTML = html)
            .catch(err => console.error(err));
    }

    links.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const page = this.getAttribute("data-page");
            loadPage(page);
            history.pushState(null, '', `?page=${page}`);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function () {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 'dashboard';
        loadPage(page);
    });
});


// Optional: AJAX page loading (for smoother transitions)
document.querySelectorAll("aside a").forEach(link => {
    link.addEventListener("click", async e => {
        e.preventDefault();
        const url = e.target.getAttribute("href");
        const res = await fetch(`${url}&ajax=1`);
        const html = await res.text();
        document.getElementById("content").innerHTML = html;
        history.pushState({}, "", url);
    });
});

// Handle back/forward navigation
window.addEventListener("popstate", async () => {
    const url = location.search + "&ajax=1";
    const res = await fetch(url);
    document.getElementById("content").innerHTML = await res.text();
});
