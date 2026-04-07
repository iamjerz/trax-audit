new gridjs.Grid({
    columns: [
        {
            name: "Form ID",
            formatter: (value) =>
                gridjs.html(`<span class="fw-semibold">${value}</span>`)
        },
        "Form Name",
        "Created By",
        {
            name: "Status",
            formatter: (value) => {
                switch (value) {
                    case "active":
                        return gridjs.html(
                            `<span class="badge rounded-pill bg-success-subtle text-success font-size-12">
                                ${value.charAt(0).toUpperCase() + value.slice(1)}
                            </span>`
                        );
                    case "inactive":
                        return gridjs.html(
                            `<span class="badge rounded-pill bg-danger-subtle text-danger font-size-12">
                                ${value.charAt(0).toUpperCase() + value.slice(1)}
                            </span>`
                        );
                    default:
                        return gridjs.html(
                            `<span class="badge rounded-pill bg-secondary-subtle text-secondary font-size-12">
                                ${value.charAt(0).toUpperCase() + value.slice(1)}
                            </span>`
                        );
                }
            }
        },
        {
            name: "Action",
            sort: false,
            formatter: (_, row) => {
                const formId = row.cells[0].data;
                return gridjs.html(`
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/formbuilder/${formId}">Edit</a></li>
                            <li><a class="dropdown-item text-primary" href="#">View</a></li>
                        </ul>
                    </div>
                `)
            }
                
        }
    ],
    pagination: {
            limit: 10
        },
        sort: true,
        search: true,
        server: {
            url: '/forms/data',
            headers: {
                'Accept': 'application/json'
            },
            then: data => data.map(form => [
                form.formid,
                form.form_name,
                form.created_by,
                form.status
            ])
        }
}).render(document.getElementById("table-invoices-list"));