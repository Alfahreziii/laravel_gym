window.addEventListener("DOMContentLoaded", () => {
    // Pastikan selalu light mode
    document.documentElement.classList.remove('dark');
    localStorage.setItem('color-theme', 'light');

    // Hanya tampilkan icon light mode
    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

    if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
    if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');

    // Sembunyikan tombol toggle supaya user tidak bisa klik
    var themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) themeToggleBtn.style.display = 'none';
});
