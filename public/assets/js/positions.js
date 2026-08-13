// Positions — canonical Position list + data-visibility level (own/team/all).
// Level changes are staged client-side (like Page Access) and only take
// effect on Save. Adding a new position saves immediately via the modal.

document.addEventListener('DOMContentLoaded', () => {
    const selects = Array.from(document.querySelectorAll('.position-scope-select'));
    const saveBtn = document.getElementById('save-changes-btn');
    const discardBtn = document.getElementById('discard-changes-btn');
    const pendingBadge = document.getElementById('pending-count');
    const saveBtnLabel = document.getElementById('save-btn-label');

    // key: position id -> new scope value
    const pendingChanges = new Map();

    function updateSaveState() {
        const count = pendingChanges.size;
        saveBtn.disabled = count === 0;
        discardBtn.disabled = count === 0;
        if (count > 0) {
            pendingBadge.textContent = String(count);
            pendingBadge.classList.remove('d-none');
        } else {
            pendingBadge.classList.add('d-none');
        }
    }

    selects.forEach(sel => {
        sel.addEventListener('change', function () {
            const id = this.dataset.id;
            const initial = this.dataset.initial;
            const row = this.closest('.position-row');

            if (this.value === initial) {
                pendingChanges.delete(id);
                row.classList.remove('row-dirty');
            } else {
                pendingChanges.set(id, this.value);
                row.classList.add('row-dirty');
            }

            updateSaveState();
        });
    });

    discardBtn.addEventListener('click', function () {
        location.reload();
    });

    saveBtn.addEventListener('click', function () {
        if (pendingChanges.size === 0) return;
        if (!confirm(`Apply ${pendingChanges.size} change(s)?`)) return;

        const btn = this;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const entries = Array.from(pendingChanges.entries());

        btn.disabled = true;
        discardBtn.disabled = true;
        saveBtnLabel.textContent = 'Saving...';

        Promise.all(entries.map(([id, scope]) =>
            fetch(`/positions/${id}/scope`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ scope })
            }).then(async res => {
                const body = await res.json();
                if (!res.ok) throw body;
                return body;
            })
        ))
        .then(() => {
            notifySuccess('Changes saved.', () => location.reload());
        })
        .catch(err => {
            console.error(err);
            notifyError(err.message || 'Something went wrong');
            btn.disabled = false;
            saveBtnLabel.textContent = 'Save Changes';
            updateSaveState();
        });
    });

    const addBtn = document.getElementById('add-position-btn');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const name = document.getElementById('new-position-name').value.trim();
            const scope = document.getElementById('new-position-scope').value;

            if (!name) {
                notifyWarning('Enter a position name.');
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/positions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, scope })
            })
            .then(async res => {
                const body = await res.json();
                if (!res.ok) throw body;
                return body;
            })
            .then(res => {
                notifySuccess(res.message, () => location.reload());
            })
            .catch(err => {
                console.error(err);
                if (err.errors) {
                    notifyError(Object.values(err.errors).flat().join('\n'));
                } else {
                    notifyError(err.message || 'Something went wrong');
                }
            });
        });
    }
});
