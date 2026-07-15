<script>
/* ── Modal helpers ─────────────────────────────────── */
function saOpenModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function saCloseModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('sa-modal-backdrop')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.sa-modal-backdrop.open').forEach(function (el) {
            el.classList.remove('open');
            document.body.style.overflow = '';
        });
        closeSaProfileDropdown();
    }
});

/* ── Profile dropdown ──────────────────────────────── */
function closeSaProfileDropdown() {
    var dd = document.getElementById('sa-profile-dropdown');
    if (dd) dd.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    var profileToggle   = document.getElementById('sa-profile-toggle');
    var profileDropdown = document.getElementById('sa-profile-dropdown');

    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
    }

    document.addEventListener('click', function (e) {
        if (profileDropdown && profileToggle &&
            !profileDropdown.contains(e.target) &&
            !profileToggle.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });
});

/* ── Hamburger: desktop collapse + mobile slide ───── */
document.addEventListener('DOMContentLoaded', function () {
    var sidebarBtn     = document.getElementById('sa-sidebar-toggle');
    var sidebar        = document.querySelector('.sa-sidebar');
    var main           = document.querySelector('.sa-main');
    var overlay        = document.getElementById('sa-sidebar-overlay');

    if (!sidebarBtn) return;

    sidebarBtn.addEventListener('click', function () {
        if (window.innerWidth >= 1024) {
            // Desktop: toggle icon-only collapse (same as gym sidebar-toggle)
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('collapsed');
        } else {
            // Mobile: slide sidebar in/out with overlay
            if (document.body.classList.contains('sa-sidebar-open')) {
                document.body.classList.remove('sa-sidebar-open');
            } else {
                document.body.classList.add('sa-sidebar-open');
            }
        }
    });

    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('sa-sidebar-open');
        });
    }

    // Clear mobile state on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            document.body.classList.remove('sa-sidebar-open');
        }
    });
});

/* ── Dark mode ─────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('sa-theme-toggle');
    var darkIcon  = document.getElementById('sa-theme-dark-icon');
    var lightIcon = document.getElementById('sa-theme-light-icon');

    function syncIcons() {
        var isDark = document.documentElement.classList.contains('dark');
        if (darkIcon)  darkIcon.style.display  = isDark ? 'none' : '';
        if (lightIcon) lightIcon.style.display = isDark ? '' : 'none';
    }

    syncIcons(); // apply on page load (IIFE already set class, this syncs icons)

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark');
            var isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
            syncIcons();
        });
    }
});
</script>
