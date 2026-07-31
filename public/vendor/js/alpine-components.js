function memberSearch(data, options = {}) {
    const endpoint = typeof data === 'string' ? data : null;
    const staticMembers = endpoint ? [] : (data || []);
    const minChars = options.minChars || 1;
    const debounceMs = options.debounce || 300;

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

        init() {
            this.filteredMembers = this.members;
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
        }
    };
}

function evidenceUpload() {
    return {
        preview: null,
        handleFile(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 5MB.');
                    event.target.value = '';
                    return;
                }
                this.preview = URL.createObjectURL(file);
            }
        },
        clearFile() {
            this.preview = null;
            const input = document.querySelector('input[name="payment_evidence"]');
            if (input) input.value = '';
        }
    };
}

function showAllToggle() {
    return {
        showAll: false,
        perPage: 15,
        toggle(showAllUrl) {
            if (this.showAll) {
                window.location.href = showAllUrl + '?per_page=1000';
            } else {
                window.history.back();
            }
        }
    };
}
