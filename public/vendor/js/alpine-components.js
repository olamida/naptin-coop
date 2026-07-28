function memberSearch(data) {
    return {
        search: '',
        showDropdown: false,
        selectedId: '',
        selectedName: '',
        members: data || [],
        filteredMembers: [],
        init() {
            this.filteredMembers = this.members;
        },
        filterMembers() {
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
        selectMember(m) {
            this.selectedId = m.id;
            this.selectedName = m.first_name + ' ' + m.last_name;
            this.search = m.first_name + ' ' + m.last_name + ' (' + m.staff_id + ')';
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
