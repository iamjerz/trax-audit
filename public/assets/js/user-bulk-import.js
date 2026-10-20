// "Bulk Edit (Excel)" modal on /users — Upload & Preview hits
// /users/bulk-edit/preview (validates only, no DB writes), then Confirm
// Import hits /users/bulk-edit/commit with the token from that preview.
// Mirrors the CSRF/fetch/notify* conventions used in user.js / user-edit.js;
// refreshUserTable() (defined in user.js, loaded before this file) reloads
// the Grid.js table after a successful import.
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('bulk-edit-file');
    const previewBtn = document.getElementById('bulk-edit-preview-btn');
    const confirmBtn = document.getElementById('bulk-edit-confirm-btn');
    const resultsBox = document.getElementById('bulk-edit-results');
    const summaryBox = document.getElementById('bulk-edit-summary');
    const rowsBody = document.getElementById('bulk-edit-rows');
    const modalEl = document.getElementById('bulk-edit-modal');

    if (!previewBtn) return; // modal not on this page

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentToken = null;

    const escapeHtml = (str) => String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    const actionBadge = (action) => {
        const map = { create: 'badge bg-success', update: 'badge bg-info', error: 'badge bg-danger' };
        return `<span class="${map[action] || 'badge bg-secondary'}">${action}</span>`;
    };

    function resetResults() {
        currentToken = null;
        confirmBtn.disabled = true;
        resultsBox.style.display = 'none';
        rowsBody.innerHTML = '';
        summaryBox.innerHTML = '';
    }

    previewBtn.addEventListener('click', () => {
        const file = fileInput.files[0];
        if (!file) {
            notifyError('Choose a file first.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        previewBtn.disabled = true;
        previewBtn.innerText = 'Reading file...';

        fetch('/users/bulk-edit/preview', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                currentToken = data.token;
                const s = data.summary;
                summaryBox.innerHTML =
                    `${s.total} row(s): <span class="text-success">${s.create} to create</span>, ` +
                    `<span class="text-info">${s.update} to update</span>, ` +
                    `<span class="text-danger">${s.error} skipped (errors)</span>`;

                rowsBody.innerHTML = data.rows.map(r => `
                    <tr>
                        <td>${r.row}</td>
                        <td>${escapeHtml(r.employeeid ?? '')}</td>
                        <td>${escapeHtml(r.name ?? '')}</td>
                        <td>${actionBadge(r.action)}</td>
                        <td class="text-muted font-size-12">${(r.errors || []).map(escapeHtml).join('<br>')}</td>
                    </tr>
                `).join('');

                resultsBox.style.display = 'block';
                confirmBtn.disabled = (s.create + s.update) === 0;

                if (s.error > 0) {
                    notifyWarning(`${s.error} row(s) have errors and will be skipped. Review the table before confirming.`);
                }
            })
            .catch(err => {
                console.error(err);
                const msg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'Could not read that file.');
                notifyError(msg);
            })
            .finally(() => {
                previewBtn.disabled = false;
                previewBtn.innerText = 'Upload & Preview';
            });
    });

    confirmBtn.addEventListener('click', () => {
        if (!currentToken) return;

        confirmBtn.disabled = true;
        confirmBtn.innerText = 'Importing...';

        fetch('/users/bulk-edit/commit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ token: currentToken }),
        })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                let msg = `${data.created} user(s) created, ${data.updated} updated.`;
                if (data.failed) msg += `\n${data.failed} row(s) failed:\n${data.errors.join('\n')}`;

                notifySuccess(msg, () => {
                    fileInput.value = '';
                    resetResults();
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                    refreshUserTable();
                });
            })
            .catch(err => {
                console.error(err);
                notifyError(err.message || 'Something went wrong while importing.');
            })
            .finally(() => {
                confirmBtn.innerText = 'Confirm Import';
            });
    });

    modalEl?.addEventListener('hidden.bs.modal', resetResults);
});
