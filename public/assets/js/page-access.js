// Page Access — Position x Page checkboxes.
// Nothing is sent to the server until "Save Changes" is clicked; every
// checkbox click only updates the in-page pending-changes map.

document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = Array.from(document.querySelectorAll('.page-access-checkbox'));
    const saveBtn = document.getElementById('save-changes-btn');
    const discardBtn = document.getElementById('discard-changes-btn');
    const pendingBadge = document.getElementById('pending-count');
    const saveBtnLabel = document.getElementById('save-btn-label');

    // key: "position||page_key" -> true (grant) / false (revoke)
    const pendingChanges = new Map();

    function keyFor(position, page) {
        return position + '||' + page;
    }

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

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const position = this.dataset.position;
            const page = this.dataset.page;
            const key = keyFor(position, page);
            const originalGrant = this.dataset.initial === '1';
            const cell = this.closest('.page-access-cell');

            if (this.checked === originalGrant) {
                pendingChanges.delete(key);
                cell.classList.remove('cell-dirty');
            } else {
                pendingChanges.set(key, { position, page_key: page, grant: this.checked });
                cell.classList.add('cell-dirty');
            }

            updateSaveState();
        });
    });

    discardBtn.addEventListener('click', function() {
        location.reload();
    });

    // Row/column hover crosshair — hovering any cell highlights its whole
    // row (Position) and whole column (Page), including the sticky header
    // row and sticky first column, so it's easy to trace a wide matrix.
    const table = document.querySelector('.page-access-wrap table');
    if (table) {
        let lastCell = null;

        const clearCrosshair = () => {
            table.querySelectorAll('.pa-hover-row').forEach(el => el.classList.remove('pa-hover-row'));
            table.querySelectorAll('.pa-hover-col').forEach(el => el.classList.remove('pa-hover-col'));
        };

        table.addEventListener('mouseover', (e) => {
            const cell = e.target.closest('td, th');
            if (!cell || cell === lastCell || !table.contains(cell)) return;
            lastCell = cell;

            clearCrosshair();

            Array.from(cell.parentElement.cells).forEach(c => c.classList.add('pa-hover-row'));

            const colIndex = cell.cellIndex;
            Array.from(table.rows).forEach(row => {
                const colCell = row.cells[colIndex];
                if (colCell) colCell.classList.add('pa-hover-col');
            });
        });

        table.addEventListener('mouseleave', () => {
            lastCell = null;
            clearCrosshair();
        });
    }

    saveBtn.addEventListener('click', function() {
        if (pendingChanges.size === 0) return;

        const changes = Array.from(pendingChanges.values());
        const revokeCount = changes.filter(c => !c.grant).length;

        let confirmMsg = `Apply ${changes.length} change(s)?`;
        if (revokeCount > 0) {
            confirmMsg = `This will revoke access to ${revokeCount} page/position combination(s), `
                + `affecting every user in those positions. Apply all ${changes.length} change(s)?`;
        }
        if (!confirm(confirmMsg)) return;

        const btn = this;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        btn.disabled = true;
        discardBtn.disabled = true;
        saveBtnLabel.textContent = 'Saving...';

        fetch('/page-access/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ changes })
            })
            .then(async res => {
                const response = await res.json();
                if (!res.ok) throw response;
                return response;
            })
            .then(response => {
                notifySuccess(response.message, () => location.reload());
            })
            .catch(err => {
                console.error(err);
                if (err.errors) {
                    notifyError(Object.values(err.errors).flat().join('\n'));
                } else if (err.message) {
                    notifyError(err.message);
                } else {
                    notifyError('Something went wrong');
                }
                btn.disabled = false;
                saveBtnLabel.textContent = 'Save Changes';
                updateSaveState();
            });
    });
});
