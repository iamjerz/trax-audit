// Shared SweetAlert2 helpers — every popup in the app goes through one of
// these three so styling/behavior stays consistent instead of each page
// building its own Swal.fire({...}) call from scratch. Loaded globally via
// partials/script.blade.php, right after the sweetalert2 library itself.

function _swalEscapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Escapes first (so any server/user text can't inject markup), then turns
// intentional \n line breaks (e.g. a joined list of validation errors) into
// <br> so multi-line messages still read correctly, same as native alert().
function _swalToHtml(message) {
    return _swalEscapeHtml(message).replace(/\n/g, '<br>');
}

/**
 * Success popup. Pass onClose if something (a reload, a modal close, a form
 * reset) should only happen after the user dismisses it — Swal is async,
 * unlike native alert(), so anything that used to "wait" for the alert to be
 * dismissed needs to go in this callback instead of running right after.
 */
function notifySuccess(message, onClose) {
    return Swal.fire({
        icon: 'success',
        title: 'Success',
        html: _swalToHtml(message),
        confirmButtonColor: '#556ee6'
    }).then(() => {
        if (typeof onClose === 'function') onClose();
    });
}

function notifyError(message, onClose) {
    return Swal.fire({
        icon: 'error',
        title: 'Error',
        html: _swalToHtml(message),
        confirmButtonColor: '#556ee6'
    }).then(() => {
        if (typeof onClose === 'function') onClose();
    });
}

function notifyWarning(message, onClose) {
    return Swal.fire({
        icon: 'warning',
        title: 'Heads up',
        html: _swalToHtml(message),
        confirmButtonColor: '#556ee6'
    }).then(() => {
        if (typeof onClose === 'function') onClose();
    });
}
