import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

window.Alpine = Alpine;
Alpine.plugin(focus);

window.AppTheme = {
    get() {
        try {
            return localStorage.getItem('app-theme');
        } catch (e) {
            return null;
        }
    },
    apply(dark) {
        this._set(dark);
        try {
            localStorage.setItem('app-theme', dark ? 'dark' : 'light');
        } catch (e) {}
    },
    _set(dark) {
        document.documentElement.classList.toggle('dark', dark);
        if (window.Chart) {
            Chart.defaults.color = dark ? '#94a3b8' : '#666';
        }
    },
    setTheme(mode) {
        const dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        try {
            if (mode === 'system') {
                localStorage.removeItem('app-theme');
            } else {
                localStorage.setItem('app-theme', mode);
            }
        } catch (e) {}
        this._set(dark);
    },
    init() {
        const stored = this.get();
        const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.apply(dark);
    },
    toggle() {
        this.apply(!document.documentElement.classList.contains('dark'));
    },
};

document.addEventListener('DOMContentLoaded', () => AppTheme.init());

window.commandPalette = (options = {}) => ({
    open: false,
    query: '',
    loading: false,
    groups: [],
    flat: [],
    selected: 0,
    queriedOnce: false,
    searchUrl: options.searchUrl || '/command/search',
    newMemberUrl: options.newMemberUrl || '',

    isTypingTarget(e) {
        if (e.metaKey || e.ctrlKey || e.altKey) return true;
        const t = e.target;
        if (t instanceof HTMLElement) {
            if (t.isContentEditable) return true;
            const tag = (t.tagName || '').toUpperCase();
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return true;
        }
        return false;
    },

    handleGlobalKey(e) {
        if (this.isTypingTarget(e)) return;
        if (this.open) return;
        if (e.key === '/') {
            e.preventDefault();
            this.openPalette();
        } else if (e.key === 'n' && !e.shiftKey && this.newMemberUrl) {
            window.location.href = this.newMemberUrl;
        } else if ((e.key === 'a' || e.key === 'A') && this.hasShortcut('approve')) {
            e.preventDefault();
            this.triggerShortcut('approve');
        } else if ((e.key === 'r' || e.key === 'R') && this.hasShortcut('reject')) {
            e.preventDefault();
            this.triggerShortcut('reject');
        }
    },

    hasShortcut(kind) {
        return document.querySelectorAll('[data-shortcut="' + kind + '"]').length === 1;
    },

    triggerShortcut(kind) {
        const el = document.querySelector('[data-shortcut="' + kind + '"]');
        if (!el) return;
        if (el.tagName === 'FORM') {
            el.requestSubmit ? el.requestSubmit() : el.submit();
        } else {
            el.click();
        }
    },

    openPalette() {
        this.open = true;
        this.query = '';
        this.groups = [];
        this.flat = [];
        this.selected = 0;
        this.$nextTick(() => this.$refs.input?.focus());
        if (!this.queriedOnce) {
            this.queriedOnce = true;
            this.search();
        }
    },

    close() {
        this.open = false;
    },

    async search() {
        this.loading = true;
        try {
            const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(this.query));
            const data = await res.json();
            this.groups = Array.isArray(data) ? data : [];
            this.reindex();
        } catch (e) {
            this.groups = [];
            this.flat = [];
        } finally {
            this.loading = false;
        }
    },

    reindex() {
        this.flat = [];
        this.groups.forEach((group, gi) => {
            group.items.forEach((item, ii) => {
                this.flat.push({ gi, ii, item });
            });
        });
        this.selected = this.flat.length ? 0 : 0;
    },

    isSelected(item) {
        return this.flat[this.selected]?.item === item;
    },

    goto(item) {
        if (item?.url) window.location.href = item.url;
    },

    handleKey(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.selected = Math.min(this.selected + 1, Math.max(this.flat.length - 1, 0));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.selected = Math.max(this.selected - 1, 0);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const current = this.flat[this.selected];
            if (current) this.goto(current.item);
        } else if (e.key === 'Escape') {
            this.close();
        }
    },
});

Alpine.start();
