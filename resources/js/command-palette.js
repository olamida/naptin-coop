export default (options = {}) => ({
    open: false,
    query: '',
    loading: false,
    groups: [],
    flat: [],
    selected: 0,
    queriedOnce: false,
    shortcuts: {},
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
        return this.firstShortcut(kind) !== null;
    },

    firstShortcut(kind) {
        const els = document.querySelectorAll('[data-shortcut="' + kind + '"]');
        for (const el of els) {
            if (el.offsetParent !== null) return el;
        }
        return els.length ? els[0] : null;
    },

    collectShortcuts() {
        const out = {};
        ['approve', 'reject'].forEach((kind) => {
            out[kind] = this.firstShortcut(kind) !== null;
        });
        return out;
    },

    triggerShortcut(kind) {
        const el = this.firstShortcut(kind);
        if (!el) return;
        if (el.tagName === 'FORM') {
            const submit = el.querySelector('[type="submit"]');
            if (submit) {
                submit.click();
            } else {
                el.requestSubmit ? el.requestSubmit() : el.submit();
            }
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
        this.shortcuts = this.collectShortcuts();
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
