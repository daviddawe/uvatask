$(document).ready(function() {
    const apiUrl = '/api/invoices';
    let currentPage = 1;
    let currentStatus = '';
    let currentCustomer = '';
    let perPage = 10;
    let totalPages = 1;

    function loadInvoices(status = '', customer = '', page = 1, perPageCount = 10) {
        $.get(apiUrl, { status, q: customer, page, per_page: perPageCount })
            .done(function(response) {
                renderInvoices(response.data || []);
                updatePagination(response.page, response.per_page, response.total);
            })
            .fail(function() {
                renderInvoices([]);
                updatePagination(1, perPage, 0);
                alert('Error loading invoices.');
            });
    }

    function renderInvoices(invoices) {
        const tableBody = $('#invoice-table tbody');
        tableBody.empty();

        if (!invoices.length) {
            tableBody.append('<tr><td colspan="5">No invoices found.</td></tr>');
            return;
        }

        invoices.forEach(invoice => {
            let actions = `
                <button class="show-invoice button-orange" data-id="${invoice.id}">Show</button>
                <button class="edit-invoice button-blue" data-id="${invoice.id}">Edit</button>
                <button class="delete-invoice button-red" data-id="${invoice.id}">Delete</button>
            `;

            let stat = '';
            if (invoice.status === 'pending') {
                stat = `<button class="pay-button" data-id="${invoice.id}">Pay</button>`;
            } else {
                stat = invoice.status;
            }

            const row = `<tr>
                <td>${invoice.customer}</td>
                <td>${invoice.amount} ${invoice.currency}</td>
                <td>${stat}</td>
                <td>${invoice.due_date}</td>
                <td>${actions}</td>
            </tr>`;
            tableBody.append(row);
        });

        $('.pay-button').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            markAsPaid(id, $(this));
        });

        $('.show-invoice').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            showInvoice(id);
        });

        $('.edit-invoice').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            editInvoice(id);
        });

        $('.delete-invoice').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            deleteInvoice(id);
        });
    }

    function updatePagination(page, perPageCount, total) {
        currentPage = page;
        perPage = perPageCount;
        totalPages = Math.ceil(total / perPage);

        let paginationHtml = '';
        if (totalPages > 1) {
            paginationHtml += `<button class="pagination-btn" data-page="1" ${page === 1 ? 'disabled' : ''}>&laquo; First</button>`;
            paginationHtml += `<button class="pagination-btn" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}>&lt; Prev</button>`;

            let start = Math.max(1, page - 2);
            let end = Math.min(totalPages, page + 2);
            for (let i = start; i <= end; i++) {
                paginationHtml += `<button class="pagination-btn" data-page="${i}" ${i === page ? 'disabled style="font-weight:bold;"' : ''}>${i}</button>`;
            }

            paginationHtml += `<button class="pagination-btn" data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}>Next &gt;</button>`;
            paginationHtml += `<button class="pagination-btn" data-page="${totalPages}" ${page === totalPages ? 'disabled' : ''}>Last &raquo;</button>`;
        }
        if ($('#pagination').length === 0) {
            $('#invoice-table').after('<div id="pagination" style="text-align:center;margin:20px 0;"></div>');
        }
        $('#pagination').html(paginationHtml);

        $('.pagination-btn').off('click').on('click', function() {
            const newPage = parseInt($(this).data('page'));
            if (newPage !== currentPage && newPage >= 1 && newPage <= totalPages) {
                loadInvoices(currentStatus, currentCustomer, newPage, perPage);
            }
        });
    }

    function markAsPaid(id, button) {
        button.prop('disabled', true).text('Processing...');
        $.post(`${apiUrl}/${id}/pay`)
            .done(function(response) {
                alert(response.already_paid ? 'Invoice was already paid.' : 'Invoice marked as paid.');
                loadInvoices();
            })
            .fail(function() {
                alert('Error marking invoice as paid.');
            })
            .always(function() {
                button.prop('disabled', false).text('Pay');
            });
    }

    function showInvoice(id) {
        $.get(`${apiUrl}/${id}`, function(invoice) {
            $('#invoice-form').hide();
            $('#modal-title').text('Invoice Details');
            let infoHtml = `
                <div id="invoice-info">
                    <p><strong>Customer:</strong> ${invoice.customer}</p>
                    <p><strong>Amount:</strong> ${invoice.amount} ${invoice.currency}</p>
                    <p><strong>Status:</strong> ${invoice.status}</p>
                    <p><strong>Due Date:</strong> ${invoice.due_date}</p>
                    <p><strong>Updated At:</strong> ${invoice.updated_at}</p>
                    <button type="button" id="close-info-btn">Close</button>
                </div>
            `;
            $('#invoice-modal').append(infoHtml).show();
            $('#modal-overlay').show();

            $('#close-info-btn').on('click', function() {
                $('#invoice-info').remove();
                $('#invoice-form').show();
                $('#invoice-modal').hide();
                $('#modal-overlay').hide();
                $('#modal-title').text('Create Invoice');
            });
        });
    }

    function editInvoice(id) {
        $.get(`${apiUrl}/${id}`, function(invoice) {
            $('#modal-title').text('Edit Invoice');
            $('#invoice-id').val(invoice.id);
            $('#invoice-customer').val(invoice.customer);
            $('#invoice-amount').val(invoice.amount);
            $('#invoice-currency').val(invoice.currency);
            $('#invoice-due-date').val(invoice.due_date);
            $('#invoice-status').val(invoice.status);
            $('#invoice-form').show();
            $('#invoice-info').remove();
            $('#invoice-modal').show();
            $('#modal-overlay').show();
        });
    }

    function deleteInvoice(id) {
        if (!confirm('Are you sure you want to delete this invoice?')) return;
        $.ajax({
            url: `${apiUrl}/${id}`,
            type: 'DELETE',
            success: function() {
                alert('Invoice deleted.');
                loadInvoices();
            },
            error: function() {
                alert('Error deleting invoice.');
            }
        });
    }

    $('#create-invoice-btn').on('click', function() {
        $('#modal-title').text('Create Invoice');
        $('#invoice-form')[0].reset();
        $('#invoice-id').val('');
        $('#invoice-form').show();
        $('#invoice-info').remove();
        $('#invoice-modal').show();
        $('#modal-overlay').show();
    });

    $('#cancel-invoice-btn').on('click', function() {
        $('#invoice-modal').hide();
        $('#modal-overlay').hide();
        $('#invoice-form').show();
        $('#invoice-info').remove();
        $('#modal-title').text('Create Invoice');
    });

    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#invoice-id').val();
        const data = {
            customer: $('#invoice-customer').val(),
            amount: $('#invoice-amount').val(),
            currency: $('#invoice-currency').val(),
            due_date: $('#invoice-due-date').val(),
            status: $('#invoice-status').val()
        };
        if (id) {
            $.ajax({
                url: `${apiUrl}/${id}`,
                type: 'PUT',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function() {
                    alert('Invoice updated.');
                    $('#invoice-modal').hide();
                    $('#modal-overlay').hide();
                    loadInvoices();
                },
                error: function() {
                    alert('Error updating invoice.');
                }
            });
        } else {
            $.ajax({
                url: apiUrl,
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function() {
                    alert('Invoice created.');
                    $('#invoice-modal').hide();
                    $('#modal-overlay').hide();
                    loadInvoices();
                },
                error: function() {
                    alert('Error creating invoice.');
                }
            });
        }
    });

    $('#search').on('input', function() {
        const customer = $(this).val();
        const status = $('#status-dropdown').val();
        loadInvoices(status, customer);
    });

    $('#status-dropdown').on('change', function() {
        const status = $(this).val();
        const customer = $('#search').val();
        loadInvoices(status, customer);
    });

    $('#modal-overlay').on('click', function() {
        $('#invoice-modal').hide();
        $('#modal-overlay').hide();
        $('#invoice-form').show();
        $('#invoice-info').remove();
        $('#modal-title').text('Create Invoice');
    });

    loadInvoices();
});