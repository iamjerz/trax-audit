// document.addEventListener('DOMContentLoaded', function() {
//     const elements = document.querySelectorAll('.dropdown-choices');

//     elements.forEach((el) => {
//         new Choices(el, {
//             searchEnabled: true,
//             itemSelectText: '',
//         });
//     });
// });

let userGrid;

// Map API data → Grid format
function mapUsers(data) {
    return data.map(user => [
        user.id,
        user.employeeid,
        user.email,
        user.first_name,
        user.last_name,
        user.position ?? '—',
        user.department ?? '—',
        user.supervisor_name,
        user.role,
        user.status,
        user.is_admin ? 1 : 0
    ]);
}

// Initialize table
function initUserTable() {
    userGrid = new gridjs.Grid({
        columns: [
            { name: "ID", hidden: true },
            "Employee ID",
            "Email",
            "First Name",
            "Last Name",
            "Position",
            "Department",
            "Supervisor",
            "Role",
            {
                name: "Status",
                formatter: (cell) => {
                    const isActive = cell === 'active';

                    return gridjs.html(`
                        <span class="badge ${isActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">
                            ${isActive ? 'Active' : 'Inactive'}
                        </span>
                    `);
                }
            },
            { name: "Is Admin", hidden: true },
            {
                name: "Actions",
                sort: false,
                formatter: (_, row) => {
                    const userId = row.cells[1].data;
                    const fullName = `${row.cells[3].data} ${row.cells[4].data}`;
                    const status = row.cells[9].data;
                    const isAdminRow = !!row.cells[10].data;
                    const isSelf = window.currentEmployeeId && userId === window.currentEmployeeId;

                    // "View as" — hidden for the admin's own row, other
                    // admin rows, and inactive users (ImpersonationController
                    // rejects all three anyway; this just avoids showing a
                    // button that's guaranteed to fail). viewAsUser() lives
                    // in impersonation.js, loaded globally.
                    const viewAsButton = (!isSelf && !isAdminRow && status === 'active')
                        ? `<li class="list-inline-item">
                               <a href="javascript:void(0)"
                                  onclick="viewAsUser('${userId}', '${fullName.replace(/'/g, "\\'")}')"
                                  class="px-2 text-warning"
                                  title="View as this user">
                                   <i class="bx bx-show font-size-18"></i>
                               </a>
                           </li>`
                        : '';

                    return gridjs.html(`
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <a href="/edit-user/${userId}"
                                   class="px-2 text-primary"
                                   title="Edit">
                                    <i class="bx bx-pencil font-size-18"></i>
                                </a>
                            </li>
                            ${viewAsButton}
                        </ul>
                    `);
                }
            }
        ],

        pagination: { limit: 50 },
        sort: true,
        search: true,

        server: {
            url: '/users/data',
            headers: {
                'Accept': 'application/json'
            },
            then: mapUsers
        }
    });

    userGrid.render(document.getElementById("table-gridjs"));
}

// Refresh table (after insert/delete/update)
function refreshUserTable() {
    userGrid.updateConfig({
        server: {
            url: '/users/data',
            headers: {
                'Accept': 'application/json'
            },
            then: mapUsers
        }
    }).forceRender();
}

// Init on page load
document.addEventListener('DOMContentLoaded', () => {
    initUserTable();
});


const choicesInstances = {};

document.querySelectorAll('.dropdown-choices').forEach((el) => {
    choicesInstances[el.id] = new Choices(el, {
        removeItemButton: true,
        searchEnabled: true,
        itemSelectText: ''
    });
});



const emailInput = document.getElementById("email");
const feedback = document.getElementById("email-feedback");

let emailExists = null;

let debounceTimer;
let currentRequest = 0;

emailInput.addEventListener("input", function() {
    // Email is always lowercase — matches the Edit User page and login form.
    this.value = this.value.toLowerCase();

    const email = this.value;

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        if (email.length < 5 || !email.includes("@")) {
            feedback.textContent = "";
            emailInput.classList.remove("is-valid", "is-invalid");
            return;
        }

        checkEmail(email);
    }, 500);
});

function checkEmail(email) {
    const requestId = ++currentRequest;

    feedback.textContent = "Checking...";
    feedback.style.color = "gray";

    fetch(`/check-email?email=${encodeURIComponent(email)}`)
        .then(res => res.json())
        .then(data => {
            if (requestId !== currentRequest) return;

            emailInput.classList.remove("is-valid", "is-invalid");

            if (!data.valid) {
                feedback.textContent = "❌ " + data.message;
                feedback.style.color = "red";
                emailInput.classList.add("is-invalid");
                emailExists = null;
                return;
            }

            if (data.exists) {
                feedback.textContent = "❌ Email already registered";
                feedback.style.color = "red";
                emailInput.classList.add("is-invalid");
                emailExists = true;
            } else {
                feedback.textContent = "✅ Email available";
                feedback.style.color = "green";
                emailInput.classList.add("is-valid");
                emailExists = false;
            }
        })
        .catch(() => {
            feedback.textContent = "Error checking email";
            feedback.style.color = "red";
        });
}

document.getElementById("update-assigned-to").addEventListener("click", function() {

    const btn = this;

    // Collect form data
    const data = {
        email: document.getElementById("email").value.trim().toLowerCase(),
        first_name: document.getElementById("first-name").value.trim(),
        last_name: document.getElementById("last-name").value.trim(),
        department: document.getElementById("department").value,
        position: document.getElementById("position").value,
        role: document.getElementById("role").value,
        supervisor_id: document.getElementById("supervisor").value,
        second_supervisor_id: document.getElementById("second-supervisor").value,
        login_method: document.getElementById("login-method").value,
    };


    if (emailExists === true) {
        notifyError("Email already registered!");
        return;
    }
    // Basic frontend validation (optional but recommended)
    if (!data.email || !data.first_name || !data.last_name) {
        notifyError("Please fill all required fields");
        return;
    }


    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Disable button + loading state
    btn.disabled = true;
    btn.innerText = "Saving...";

    fetch('/insert-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async res => {
            const response = await res.json();

            if (!res.ok) {
                throw response;
            }

            return response;
        })
        .then(response => {
            notifySuccess("User created successfully!", () => {
                // Reset inputs
                document.querySelectorAll("#add-user input").forEach(input => {
                    input.value = "";
                });

                // Reset Choices.js dropdowns
                Object.values(choicesInstances).forEach(instance => {
                    instance.removeActiveItems(); // remove selected
                    instance.setChoiceByValue(''); // reset placeholder
                });

                // Login Method has no blank placeholder option (it's never
                // meant to be "unset") — put it back on the Microsoft-only
                // default instead of leaving it blank after the generic reset above.
                choicesInstances['login-method']?.setChoiceByValue('microsoft');

                // Remove validation classes
                document.querySelectorAll("#add-user .form-control").forEach(el => {
                    el.classList.remove("is-valid", "is-invalid");
                });

                // Clear email feedback
                const feedback = document.getElementById("email-feedback");
                if (feedback) feedback.textContent = "";

                // Close modal
                const modalEl = document.getElementById('add-user');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                refreshUserTable()
            });
        })
        .catch(err => {
            console.error(err);

            // Laravel validation errors
            if (err.errors) {
                let messages = Object.values(err.errors).flat().join("\n");
                notifyError(messages);
            } else {
                notifyError("Something went wrong");
            }
        })
        .finally(() => {
            // Re-enable button
            btn.disabled = false;
            btn.innerText = "Update";
        });
});
