(() => {
    function getContentEl() {
        return document.getElementById("content");
    }

    function buildAjaxUrl(url) {
        const u = new URL(url, window.location.origin);
        u.searchParams.set("ajax", "1");
        return u.toString();
    }

    function normalizePageFromUrl(url) {
        const u = new URL(url, window.location.origin);
        return u.searchParams.get("page") || "dashboard";
    }

    function updateActiveNav(page) {
        const navRoot = document.querySelector(".navigation");
        if (!navRoot) return;

        const links = navRoot.querySelectorAll("a[data-page]");
        links.forEach(link => {
            const linkPage = link.getAttribute("data-page");
            const isActive = page === linkPage || page.startsWith(linkPage + "/");

            if (link.closest(".submenu")) {
                link.classList.toggle("bg-slate-700", isActive);
                link.classList.toggle("text-white", isActive);
                link.classList.toggle("font-semibold", isActive);

                link.classList.toggle("text-slate-400", !isActive);
                link.classList.toggle("hover:text-white", !isActive);
                link.classList.toggle("hover:bg-slate-700/30", !isActive);
            } else {
                const parentLi = link.closest("li");
                if (parentLi) {
                    parentLi.classList.toggle("bg-gradient-to-r", isActive);
                    parentLi.classList.toggle("from-indigo-500", isActive);
                    parentLi.classList.toggle("to-purple-600", isActive);
                    parentLi.classList.toggle("text-white", isActive);

                    parentLi.classList.toggle("hover:bg-slate-700/50", !isActive);
                    parentLi.classList.toggle("text-slate-300", !isActive);
                }
            }
        });

        // Open the submenu of the active link if applicable
        const activeSub = navRoot.querySelector(`.submenu a[data-page="${page}"]`);
        if (activeSub) {
            const submenu = activeSub.closest(".submenu");
            const toggle = submenu ? submenu.previousElementSibling : null;
            const arrow = toggle ? toggle.querySelector(".dropdown-arrow") : null;
            submenu?.classList.add("open");
            arrow?.classList.add("rotate-180");
        }
    }

    function executeScripts(container) {
        const scripts = Array.from(container.querySelectorAll("script"));
        scripts.forEach(script => script.remove());

        scripts.forEach(script => {
            const s = document.createElement("script");
            if (script.src) {
                if (document.querySelector(`script[src="${script.src}"]`)) {
                    return;
                }
                s.src = script.src;
                s.async = false;
            } else {
                s.textContent = script.textContent;
            }
            document.body.appendChild(s);
        });
    }

    async function loadUrl(url, pushState) {
        const contentEl = getContentEl();
        if (!contentEl) return;

        try {
            const res = await fetch(buildAjaxUrl(url), {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            if (res.status === 401) {
                window.location.href = "/login.php";
                return;
            }
            if (!res.ok) {
                console.error("Failed to load page", res.status);
                return;
            }
            const html = await res.text();
            const tmp = document.createElement("div");
            tmp.innerHTML = html;

            contentEl.innerHTML = tmp.innerHTML;
            executeScripts(contentEl);

            if (pushState) {
                const u = new URL(url, window.location.origin);
                history.pushState({}, "", u.pathname + u.search);
            }

            updateActiveNav(normalizePageFromUrl(url));
            contentEl.scrollTop = 0;
        } catch (err) {
            console.error("SPA load error", err);
        }
    }

    document.addEventListener("click", (e) => {
        const link = e.target.closest("a");
        if (!link) return;

        const href = link.getAttribute("href") || "";
        if (!href.startsWith("?page=") && !href.includes("index.php?page=")) return;

        e.preventDefault();
        const url = new URL(href, window.location.origin).toString();
        loadUrl(url, true);
    });

    window.addEventListener("popstate", () => {
        const url = window.location.href;
        loadUrl(url, false);
    });

    document.addEventListener("DOMContentLoaded", () => {
        const contentEl = getContentEl();
        if (!contentEl) return;
        updateActiveNav(normalizePageFromUrl(window.location.href));
    });
})();
