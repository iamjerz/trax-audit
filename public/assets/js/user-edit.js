document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.dropdown-choices');

    elements.forEach((el) => {
        new Choices(el, {
            searchEnabled: true,
            itemSelectText: '',
            removeItemButton: true
        });
    });
});

const capitalizeFirst = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

// Leaver Date (daterangepicker.com, single-date mode — same widget as the
// dashboards). Admin-editable now; Status still auto-manages it as a
// convenience default server-side (see UserPageController::updateUser).
const leaverDateInput = $('#leaver-date');
const leaverDateValue = document.getElementById('leaver-date-value');
// So the calendar opens navigated to (and highlighting) the date already on
// file, instead of always jumping to today's month — daterangepicker's
// startDate/endDate options control calendar navigation independently of
// the input's displayed text (which autoUpdateInput:false leaves alone).
const initialLeaverDate = leaverDateValue.value ? moment(leaverDateValue.value, 'YYYY-MM-DD') : moment();

leaverDateInput.daterangepicker({
    singleDatePicker: true,
    autoUpdateInput: false,
    autoApply: true,
    startDate: initialLeaverDate,
    endDate: initialLeaverDate,
    locale: {
        format: 'MMM D, YYYY'
    }
});

leaverDateInput.on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('MMM D, YYYY'));
    leaverDateValue.value = picker.startDate.format('YYYY-MM-DD');
});

document.getElementById('leaver-date-clear').addEventListener('click', function () {
    leaverDateInput.val('');
    leaverDateValue.value = '';
});

document.getElementById("edit-user").addEventListener("click", function() {

    // Collect form data
    const data = {
        employeeid: document.getElementById("employeeid").value.trim(),
        email: document.getElementById("email").value.trim().toLowerCase(),
        first_name: capitalizeFirst(document.getElementById("first-name").value.trim().toLowerCase()),
        last_name: capitalizeFirst(document.getElementById("last-name").value.trim().toLowerCase()),
        role: document.getElementById("role").value,
        supervisor_id: document.getElementById("supervisor").value,
        second_supervisor_id: document.getElementById("second-supervisor").value,
        status: document.getElementById("status").value,
        position: document.getElementById("position").value,
        effectivity_date_leaver: leaverDateValue.value || null
    };

    console.log("DATA :: :: ", data)

    fetch(`/users/edit/${data.employeeid}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const res = await response.json();

            if (!response.ok) throw res;

            return res;
        })
        .then(res => {
            if (res.success) {
                // ✅ Reload page
                location.reload();
            } else {
                notifyError(res.message || 'Update failed');
            }
        })
        .catch(err => {
            console.error(err);

            if (err.errors) {
                notifyError('Validation error');
            } else {
                notifyError('Something went wrong');
            }
        });
});

document.getElementById('reset-password-btn').addEventListener('click', function () {

    const employeeid = document.getElementById('employeeid').value;

    if (!confirm("Reset this user's password to the default? They will be required to change it at next login.")) {
        return;
    }

    fetch(`/users/${employeeid}/reset-password`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(async response => {
        const res = await response.json();
        if (!response.ok) throw res;
        return res;
    })
    .then(res => {
        if (res.success) {
            notifySuccess(res.message || 'Password reset to default.');
        } else {
            notifyError(res.message || 'Failed to reset password');
        }
    })
    .catch(err => {
        console.error(err);
        notifyError('Error resetting password');
    });

});

document.getElementById('update-access-btn').addEventListener('click', function () {

    const employeeid = document.getElementById('employeeid').value;

    const access = document.getElementById("access-admin").checked ? ['admin'] : [];

    fetch(`/users/${employeeid}/access`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ access })
    })
    .then(async response => {
        const res = await response.json();
        if (!response.ok) throw res;
        return res;
    })
    .then(res => {
        if (res.success) {
            notifySuccess('Access updated!', () => location.reload());
        } else {
            notifyError(res.message || 'Failed');
        }
    })
    .catch(err => {
        console.error(err);
        notifyError('Error updating access');
    });

});