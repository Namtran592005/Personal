// Analytics: page-view pixel + time-on-page beacon. Reads window.SITE config.
(function() {
    if (!window.SITE || !window.SITE.basePath) return;
    var p = location.pathname;
    var i = new Image();
    i.src = window.SITE.basePath + '/includes/track.php?path=' + encodeURIComponent(p)
        + '&sw=' + screen.width + '&sh=' + screen.height
        + '&lang=' + encodeURIComponent(navigator.language || '');

    // Time-on-page: count only while the tab is visible, report on leave.
    var t0 = Date.now();
    var acc = 0;
    var last = t0;
    function mark() {
        var now = Date.now();
        if (!document.hidden) acc += now - last;
        last = now;
    }
    document.addEventListener('visibilitychange', mark);
    window.addEventListener('pagehide', function() {
        mark();
        var s = Math.round(acc / 1000);
        if (s > 0) {
            try {
                navigator.sendBeacon(window.SITE.basePath + '/includes/track.php?duration=' + Math.min(s, window.SITE.beaconMax || 3600));
            } catch (e) {}
        }
    });
})();
