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
document.getElementById("edit-user").addEventListener("click", function() {

    // Collect form data
    const data = {
        employeeid: document.getElementById("employeeid").value.trim(),
        email: document.getElementById("email").value.trim().toLowerCase(),
        first_name: capitalizeFirst(document.getElementById("first-name").value.trim().toLowerCase()),
        last_name: capitalizeFirst(document.getElementById("last-name").value.trim().toLowerCase()),
        role: document.getElementById("role").value,
        supervisor_id: document.getElementById("supervisor").value,
        status: document.getElementById("status").value,
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
                alert(res.message || 'Update failed');
            }
        })
        .catch(err => {
            console.error(err);

            if (err.errors) {
                alert('Validation error');
            } else {
                alert('Something went wrong');
            }
        });
});

document.getElementById('update-access-btn').addEventListener('click', function () {

    const employeeid = document.getElementById('employeeid').value;

    const access = Array.from(document.getElementById("access").selectedOptions)
        .map(opt => opt.value);

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
            alert('✅ Access updated!');
            location.reload();
        } else {
            alert(res.message || 'Failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error updating access');
    });

});