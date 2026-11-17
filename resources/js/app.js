(function () {
    'use strict';

    const storageKey = 'composer-monitor-theme';

    function readTheme() {
        try {
            return window.localStorage.getItem(storageKey) || 'system';
        } catch (error) {
            return 'system';
        }
    }

    function saveTheme(theme) {
        try {
            window.localStorage.setItem(storageKey, theme);
        } catch (error) {
            // The dashboard still works when browser storage is disabled.
        }
    }

    function applyTheme(theme) {
        const root = document.documentElement;

        if (theme === 'system') {
            root.removeAttribute('data-theme');
        } else {
            root.setAttribute('data-theme', theme);
        }

        const select = document.getElementById('theme-select');
        if (select) {
            select.value = theme;
        }
    }

    function initializeComposerMonitor() {
        const themeSelect = document.getElementById('theme-select');

        applyTheme(readTheme());

        themeSelect?.addEventListener('change', function () {
            const theme = ['system', 'light', 'dark'].includes(this.value) ? this.value : 'system';
            saveTheme(theme);
            applyTheme(theme);
        });

        document.querySelectorAll('[data-composer-monitor-preferences]').forEach(function (form) {
            form.querySelector('select[name="locale"]')?.addEventListener('change', function () {
                form.submit();
            });
        });

        document.getElementById('package-search')?.addEventListener('input', function () {
            const query = this.value.toLocaleLowerCase();
            document.querySelectorAll('#packages-table tbody tr').forEach(function (row) {
                row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeComposerMonitor, { once: true });
    } else {
        initializeComposerMonitor();
    }
})();
