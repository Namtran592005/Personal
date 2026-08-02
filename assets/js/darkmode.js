// Dark mode: applied before first paint (loaded synchronously in <head>).
(function() {
    var saved = localStorage.getItem('darkMode');
    if (saved === 'false') {
        document.documentElement.classList.remove('dark');
    } else {
        document.documentElement.classList.add('dark');
        if (saved === null) {
            localStorage.setItem('darkMode', 'true');
        }
    }
})();
function toggleDark() {
    var html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('darkMode', html.classList.contains('dark'));
}
