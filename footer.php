</div>
<!-- Bootstrap JS: مطلوب لزرار التفاصيل (collapse) وقائمة الموبايل -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var themeBtn = document.getElementById('themeToggleBtn');
        var themeIcon = document.getElementById('themeIcon');
        var root = document.documentElement;

        function applyTheme(theme) {
            root.setAttribute('data-theme', theme);
            root.setAttribute('data-bs-theme', theme);
            if (themeIcon) {
                themeIcon.classList.toggle('fa-sun', theme === 'light');
                themeIcon.classList.toggle('fa-moon', theme !== 'light');
            }
        }

        applyTheme(root.getAttribute('data-theme') || 'dark');

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                try { localStorage.setItem('cafe_theme', next); } catch (e) {}
            });
        }
    });
</script>
