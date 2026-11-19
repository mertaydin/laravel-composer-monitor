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

        initializeCheckLoading();
        initializeCommandModals();

        document.getElementById('package-search')?.addEventListener('input', function () {
            const query = this.value.toLocaleLowerCase();
            document.querySelectorAll('#packages-table tbody tr').forEach(function (row) {
                row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            });
        });
    }

    function initializeCheckLoading() {
        const form = document.querySelector('[data-composer-monitor-check]');
        const overlay = document.getElementById('composer-monitor-page-loading');
        if (!form || !overlay) return;

        form.addEventListener('submit', function (event) {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            form.dataset.submitting = 'true';
            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true;
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');

            // Force layout and allow the centered overlay to paint before the scan request.
            overlay.getBoundingClientRect();
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    HTMLFormElement.prototype.submit.call(form);
                });
            });
        });

        window.addEventListener('pageshow', function () {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            form.dataset.submitting = 'false';
            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = false;
        });
    }

    function initializeCommandModals() {
        const modal = document.getElementById('composer-monitor-modal');
        const config = document.getElementById('composer-monitor-config');
        if (!modal || !config) return;

        const title = document.getElementById('composer-monitor-modal-title');
        const content = document.getElementById('composer-monitor-modal-content');
        const csrf = config.dataset.csrfToken;
        const labels = config.dataset;

        function node(tag, className, text) {
            const item = document.createElement(tag);
            if (className) item.className = className;
            if (text !== undefined) item.textContent = text;
            return item;
        }
        function close() {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            content.replaceChildren();
        }
        function open(heading) {
            title.textContent = heading;
            content.replaceChildren(node('div', 'command-loading', labels.loading));
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
        }
        function detail(parent, label, value) {
            if (value === undefined || value === null || value === '') return;
            const item = node('div', 'detail-item');
            item.append(node('span', 'detail-label', label), node('strong', 'detail-value', Array.isArray(value) ? value.join(', ') : String(value)));
            parent.append(item);
        }
        function raw(output) {
            content.append(node('pre', 'command-output', output || labels.empty));
        }
        function renderShow(data, output) {
            if (!data || typeof data !== 'object') return raw(output);
            const summary = node('div', 'detail-grid');
            detail(summary, labels.installed, data.versions);
            detail(summary, labels.description, data.description);
            detail(summary, labels.license, data.license);
            content.replaceChildren(summary);
            [['requires', labels.requires], ['devRequires', 'Dev ' + labels.requires], ['suggests', labels.suggests]].forEach(([key, heading]) => {
                const values = data[key];
                if (!values || typeof values !== 'object' || !Object.keys(values).length) return;
                const section = node('section', 'result-section');
                section.append(node('h3', '', heading));
                const list = node('ul', 'result-list');
                Object.entries(values).forEach(([name, constraint]) => list.append(node('li', '', name + '  ' + String(constraint))));
                section.append(list);
                content.append(section);
            });
            if (!summary.childNodes.length && !content.querySelector('.result-section')) raw(output);
        }
        function renderAudit(data, output) {
            if (!data || typeof data !== 'object') return raw(output);
            const advisories = Object.entries(data.advisories || {});
            const abandoned = Object.entries(data.abandoned || {});
            const malware = Object.entries(data.malware || {});
            const count = advisories.reduce((total, [, items]) => total + (Array.isArray(items) ? items.length : 0), 0);
            const summary = node('div', 'audit-summary');
            summary.append(node('strong', '', count + ' ' + labels.advisories));
            summary.append(node('span', '', abandoned.length + ' ' + labels.abandoned));
            if (!count && !abandoned.length && !malware.length) summary.append(node('p', 'audit-clean', labels.noIssues));
            content.replaceChildren(summary);
            advisories.forEach(([name, items]) => (Array.isArray(items) ? items : []).forEach(item => {
                const card = node('article', 'audit-card');
                const head = node('div', 'audit-card-heading');
                head.append(node('strong', '', name), node('span', 'severity-pill', String(item.severity || 'unknown').toUpperCase()));
                card.append(head, node('p', '', item.title || item.advisoryId || 'Security advisory'));
                detail(card, 'CVE', item.cve);
                detail(card, 'Affected versions', item.affectedVersions);
                if (item.link) {
                    const link = node('a', 'link', 'Advisory details');
                    link.href = item.link;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    card.append(link);
                }
                content.append(card);
            }));
            abandoned.forEach(([name, replacement]) => {
                const card = node('article', 'audit-card abandoned-card');
                card.append(node('strong', '', name), node('p', '', replacement ? 'Suggested replacement: ' + replacement : 'Marked as abandoned'));
                content.append(card);
            });
            malware.forEach(([name, items]) => {
                const card = node('article', 'audit-card');
                card.append(node('strong', '', name), node('p', '', JSON.stringify(items)));
                content.append(card);
            });
            if (!count && !abandoned.length && !malware.length && output) raw(output);
        }
        function renderSuggests(output) {
            const lines = String(output || '').split(/\r?\n/);
            const groups = [];
            let current = null;
            lines.forEach(line => {
                const trimmed = line.trim();
                if (!trimmed) return;
                if (/^[a-z0-9_.-]+\/[a-z0-9_.-]+\s+suggests:?$/i.test(trimmed)) {
                    current = { name: trimmed.replace(/\s+suggests:?$/i, ''), items: [] };
                    groups.push(current);
                } else if (current) current.items.push(trimmed);
            });
            if (!groups.length) return raw(output);
            groups.forEach(group => {
                const card = node('article', 'suggestion-card');
                card.append(node('h3', '', group.name));
                const list = node('ul', 'result-list');
                group.items.forEach(item => list.append(node('li', '', item)));
                card.append(list);
                content.append(card);
            });
        }
        const commandNames = {show: 'composer show', why: 'composer why', audit: 'composer audit', suggests: 'composer suggests'};
        document.addEventListener('click', async event => {
            if (event.target.closest('[data-modal-close]')) return close();
            const button = event.target.closest('[data-monitor-action]');
            if (!button) return;
            const action = button.dataset.monitorAction;
            const packageName = button.dataset.package;
            open(commandNames[action] + (packageName ? ' · ' + packageName : ''));
            button.disabled = true;
            try {
                const body = new URLSearchParams();
                if (packageName) body.set('package', packageName);
                const response = await fetch(button.dataset.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                    body: body.toString()
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || labels.error);
                content.replaceChildren();
                if (action === 'show') renderShow(result.data, result.output);
                else if (action === 'audit') renderAudit(result.data, result.output);
                else if (action === 'suggests') renderSuggests(result.output);
                else raw(result.output);
            } catch (error) {
                content.replaceChildren(node('div', 'command-error', error.message || labels.error));
            } finally {
                button.disabled = false;
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !modal.hidden) close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeComposerMonitor, { once: true });
    } else {
        initializeComposerMonitor();
    }
})();
