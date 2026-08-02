// Boot: wire up everything once the DOM is ready (scripts sit at end of body).
(function() {
    function boot() {
        if (typeof window.initPage === 'function') initPage();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
