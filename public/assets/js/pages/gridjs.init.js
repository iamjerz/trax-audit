document.addEventListener('DOMContentLoaded', () => {
    new gridjs.Grid({
        columns: [
            { name: "ID", hidden: true },
            "Employee ID",
            "Email",
            "First Name",
            "Last Name",
            "Position",
            "Department",
            "Role",
            {
                name: "Status",
                formatter: (cell) => {
                    const isActive = cell === 'active';

                    return gridjs.html(`
                        <span class="badge ${isActive ? 'bg-success-subtle text-success ' : 'bg-danger-subtle text-danger'} mb-0">
                            ${isActive ? 'Active' : 'Inactive'}
                        </span>
                    `);
                }
            },
            {
                name: "Actions",
                sort: false,
                formatter: (_, row) => {
                    const userId = row.cells[0].data;

                    return gridjs.html(`
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <a href="/users/${userId}/edit"
                                class="px-2 text-primary"
                                title="Edit">
                                    <i class="bx bx-pencil font-size-18"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="javascript:void(0);"
                                onclick="deleteUser(${userId})"
                                class="px-2 text-danger"
                                title="Delete">
                                    <i class="bx bx-trash-alt font-size-18"></i>
                                </a>
                            </li>
                        </ul>
                    `);
                }
            }
        ],
        pagination: {
            limit: 10
        },
        sort: true,
        search: true,
        server: {
            url: '/users/data',
            headers: {
                'Accept': 'application/json'
            },
            then: data => data.map(user => [
                user.id,
                user.employeeid,
                user.email,
                user.first_name,
                user.last_name,
                user.position ?? '—',
                user.department ?? '—',
                user.role,
                user.status // raw value, badge formatter handles display
            ])
        }
    }).render(document.getElementById("table-gridjs"));
});
