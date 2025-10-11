(function () {
    const savedTheme = localStorage.getItem('theme');
    // If a theme is saved, apply it immediately before the page renders.
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
})();