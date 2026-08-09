import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import commandPalette from './command-palette';

window.Alpine = Alpine;
Alpine.plugin(focus);

window.commandPalette = commandPalette;

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

window.memberFormSearch = (data, options = {}) => {
    const endpoint = typeof data === 'string' ? data : null;
    const staticMembers = endpoint ? [] : (data || []);
    const minChars = options.minChars || 1;
    const debounceMs = options.debounce || 300;
    const initialSelectedId = options.initialSelectedId ? String(options.initialSelectedId) : '';

    return {
        search: '',
        showDropdown: false,
        selectedId: '',
        selectedName: '',
        members: staticMembers,
        filteredMembers: [],
        loading: false,
        _endpoint: endpoint,
        _timer: null,
        _initial: initialSelectedId,

        init() {
            this.filteredMembers = this.members;
            if (this._initial && !this._endpoint) {
                const m = this.members.find(x => String(x.id) === this._initial);
                if (m) {
                    this.selectedId = m.id;
                    this.selectedName = m.first_name + ' ' + m.last_name;
                    this.search = m.first_name + ' ' + m.last_name;
                    this.$nextTick(() => this.$el.dispatchEvent(new CustomEvent('member-selected', { bubbles: true, detail: { member: m } })));
                }
            }
        },
        filterMembers() {
            if (this._endpoint) {
                this.debouncedSearch();
                return;
            }
            const q = this.search.toLowerCase();
            if (!q) {
                this.filteredMembers = this.members;
                return;
            }
            this.filteredMembers = this.members.filter(m =>
                (m.first_name + ' ' + m.last_name).toLowerCase().includes(q) ||
                (m.staff_id || '').toLowerCase().includes(q) ||
                (m.account_number || '').toLowerCase().includes(q)
            );
        },
        debouncedSearch() {
            clearTimeout(this._timer);
            const q = this.search.trim();
            if (q.length < minChars) {
                this.filteredMembers = [];
                this.loading = false;
                return;
            }
            this._timer = setTimeout(() => this.fetchRemote(q), debounceMs);
        },
        async fetchRemote(q) {
            this.loading = true;
            try {
                const res = await fetch(this._endpoint + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                if (q !== this.search.trim()) return;
                this.members = Array.isArray(json) ? json : [];
                this.filteredMembers = this.members;
            } catch (e) {
                this.filteredMembers = [];
            } finally {
                this.loading = false;
            }
        },
        selectMember(m) {
            this.selectedId = m.id;
            this.selectedName = m.first_name + ' ' + m.last_name;
            this.search = m.first_name + ' ' + m.last_name + (m.staff_id_display ? ' (' + m.staff_id_display + ')' : '');
            this.showDropdown = false;
            this.$el.dispatchEvent(new CustomEvent('member-selected', { bubbles: true, detail: { member: m } }));
        },
        clearSelected() {
            this.selectedId = '';
            this.selectedName = '';
            this.search = '';
            this.showDropdown = false;
            this.$el.dispatchEvent(new CustomEvent('member-cleared', { bubbles: true }));
        }
    };
};

window.memberSearch = (data, options = {}) => window.memberFormSearch(data, options);

window.searchAutocomplete = (options = {}) => ({
    query: options.value || '',
    results: [],
    open: false,
    loading: false,
    selected: -1,
    endpoint: options.endpoint || '',
    minChars: options.minChars || 2,
    _timer: null,

    async fetchResults() {
        clearTimeout(this._timer);
        const q = this.query.trim();
        if (q.length < this.minChars) {
            this.results = [];
            this.open = false;
            return;
        }
        this._timer = setTimeout(() => this._fetch(q), 300);
    },

    async _fetch(q) {
        this.loading = true;
        try {
            const res = await fetch(this.endpoint + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();
            if (q !== this.query.trim()) return;
            this.results = Array.isArray(json) ? json : [];
            this.open = this.results.length > 0;
            this.selected = this.results.length ? 0 : -1;
        } catch (e) {
            this.results = [];
        } finally {
            this.loading = false;
        }
    },

    move(dir) {
        if (!this.results.length) return;
        this.selected = (this.selected + dir + this.results.length) % this.results.length;
    },

    select(r) {
        if (r && r.url) {
            window.location.href = r.url;
            return;
        }
        if (r) this.query = r.label;
        this.open = false;
        this.submitForm();
    },

    submitForm() {
        const form = this.$el.closest('form');
        if (form) form.submit();
    },

    onEnter() {
        if (this.selected >= 0 && this.results[this.selected]) {
            this.select(this.results[this.selected]);
            return;
        }
        this.open = false;
        this.submitForm();
    },
});

Alpine.start();
