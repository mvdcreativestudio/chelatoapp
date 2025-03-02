'use strict';

$(function () {
    var budgetsContainer = $('#budgets-view');
    var isListView = false;
    var budgets = [];

    // Inicializar los datos
    function initializeBudgets() {
        if (window.initialBudgets && window.initialBudgets.length > 0) {
            budgets = window.initialBudgets;
            displayBudgetsCards(budgets);
        } else {
            refreshBudgets();
        }
    }

    // Función para refrescar los presupuestos
    function refreshBudgets() {
        var ajaxUrl = budgetsContainer.data('ajax-url');

        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                if (response.success) {
                    budgets = response.data;
                    displayBudgetsContent();
                }
            },
            error: function(xhr) {
                console.error('Error al cargar presupuestos:', xhr);
                displayError();
            }
        });
    }

    // Función para mostrar el contenido según los datos
    function displayBudgetsContent() {
        if (budgets.length === 0) {
            displayEmptyMessage();
        } else {
            if (isListView) {
                displayBudgetsList(budgets);
            } else {
                displayBudgetsCards(budgets);
            }
        }
    }

    // Función para mostrar mensaje de error
    function displayError() {
        budgetsContainer.html(`
            <div class="alert alert-danger text-center w-100">
                <i class="bx bx-error-circle"></i> Error al cargar los presupuestos
            </div>
        `);
    }

    // Función para mostrar mensaje de vacío
    function displayEmptyMessage() {
        budgetsContainer.html(`
            <div class="alert alert-info text-center w-100">
                <i class="bx bx-info-circle"></i> No existen presupuestos disponibles.
            </div>
        `);
    }

    // Manejar el cambio de vista (tarjetas/lista)
    $('#toggle-view-btn').on('click', function () {
        isListView = !isListView;
        // Cambiar el icono según la vista actual
        const $icon = $(this).find('i');
        if (isListView) {
            $icon.removeClass('bx-grid-alt').addClass('bx-list-ul');
        } else {
            $icon.removeClass('bx-list-ul').addClass('bx-grid-alt');
        }
        displayBudgetsContent();
    });

    // Función para obtener el estado formateado
    function getFormattedStatus(status) {
        const statusTranslations = {
            'draft': 'Borrador',
            'pending_approval': 'Pendiente de Aprobación',
            'sent': 'Enviado',
            'negotiation': 'En Negociación',
            'approved': 'Aprobado',
            'rejected': 'Rechazado',
            'expired': 'Expirado',
            'cancelled': 'Cancelado'
        };

        const statusColors = {
            'draft': 'warning',
            'pending_approval': 'info',
            'sent': 'primary',
            'negotiation': 'info',
            'approved': 'success',
            'rejected': 'danger',
            'expired': 'secondary',
            'cancelled': 'danger'
        };

        const translatedStatus = statusTranslations[status] || status;
        const statusColor = statusColors[status] || 'primary';

        return `<span class="badge bg-label-${statusColor}">${translatedStatus}</span>`;
    }

    // Función para formatear los productos
    function formatProducts(items) {
        if (!items || items.length === 0) return 'Sin productos';

        const firstThree = items.slice(0, 3).map(item => {
            const productName = item.product ? item.product.name : 'Producto no disponible';
            return `${productName} (${item.quantity})`;
        }).join(', ');

        if (items.length > 3) {
            return `${firstThree} y ${items.length - 3} más...`;
        }

        return firstThree;
    }

    // Función para mostrar presupuestos en formato de tarjetas
    function displayBudgetsCards(budgetsToDisplay) {
        budgetsContainer.html(''); // Limpiar contenedor

        budgetsToDisplay.forEach(function (budget) {
            const formattedDate = new Date(budget.due_date).toLocaleDateString();

            let cardHtml = `
                <div class="col-md-6 col-lg-4 col-12 mb-4">
                    <div class="order-card p-3 shadow-sm rounded bg-white d-flex flex-column justify-content-between" data-budget-id="${budget.id}">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    ${budget.client
                                        ? (budget.client.client_type === 'individual'
                                            ? budget.client.name
                                            : budget.client.company_name)
                                        : (budget.lead?.name || "Sin Cliente")}
                                </h5>
                                ${getFormattedStatus(budget.current_status)}
                            </div>
                            <p class="mb-1"><i class="bx bx-calendar"></i> ${formattedDate}</p>
                            <p class="mb-1"><i class="bx bx-store"></i> ${budget.store.name}</p>
                            <p class="mb-2"><i class="bx bx-package"></i> ${formatProducts(budget.items)}</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="card-text mb-0">Total: <span class="budget-total fw-bold text-primary">$${parseFloat(budget.total).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></p>
                            <div class="d-flex gap-2">
                                <a href="/admin/budgets/${budget.id}/detail" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                <a href="/admin/budgets/${budget.id}/edit" class="btn btn-sm btn-outline-primary"><i class="bx bx-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger delete-budget" data-id="${budget.id}"><i class="bx bx-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>`;

            budgetsContainer.append(cardHtml);
        });
    }

    // Función para mostrar presupuestos en formato de lista
    function displayBudgetsList(budgetsToDisplay) {
        budgetsContainer.html('<ul class="list-group w-100 p-0"></ul>'); // Crear lista

        budgetsToDisplay.forEach(function (budget) {
            const formattedDate = new Date(budget.due_date).toLocaleDateString();

            let rowHtml = `
                <li class="list-group-item d-flex justify-content-between align-items-center bg-white" data-budget-id="${budget.id}">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="table-title mb-0">${budget.client?.name || budget.lead?.name || "Sin Cliente"}</h5>
                            ${getFormattedStatus(budget.current_status)}
                        </div>
                        <p class="mb-1"><i class="bx bx-calendar"></i> ${formattedDate}</p>
                        <p class="mb-1"><i class="bx bx-store"></i> ${budget.store.name}</p>
                        <p class="mb-2"><i class="bx bx-package"></i> ${formatProducts(budget.items)}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="card-text mb-0">Total: <span class="budget-total fw-bold text-primary">$${parseFloat(budget.total).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></p>
                            <div class="d-flex gap-2">
                                <a href="/admin/budgets/${budget.id}/detail" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                <a href="/admin/budgets/${budget.id}/edit" class="btn btn-sm btn-outline-primary"><i class="bx bx-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger delete-budget" data-id="${budget.id}"><i class="bx bx-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </li>`;

            budgetsContainer.find('ul').append(rowHtml);
        });
    }

    // Manejador para el botón de eliminar
    $(document).on('click', '.delete-budget', function(e) {
        e.preventDefault();
        const budgetId = $(this).data('id');
        const isCard = $(this).closest('.order-card').length > 0;

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteBudget(budgetId, isCard);
            }
        });
    });

    function deleteBudget(budgetId, isCard) {
        $.ajax({
            url: `/admin/budgets/${budgetId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        budgets = budgets.filter(b => b.id !== budgetId);
                        if (isListView) {
                            displayBudgetsList(budgets);
                        } else {
                            displayBudgetsCards(budgets);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Ocurrió un error al eliminar el presupuesto',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar el presupuesto',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    // Inicializar la vista
    initializeBudgets();

    // Agregar listener para el campo de búsqueda
    $('#searchClient').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        const filteredBudgets = budgets.filter(budget => {
            // Get all searchable fields
            const clientName = (budget.client?.name || budget.lead?.name || "Sin Cliente").toLowerCase();
            const storeName = budget.store?.name?.toLowerCase() || "";
            const status = getTranslatedStatus(budget.current_status).toLowerCase();
            const total = budget.total.toString();

            // Check if any field matches the search term
            return clientName.includes(searchTerm) ||
                   storeName.includes(searchTerm) ||
                   status.includes(searchTerm) ||
                   total.includes(searchTerm);
        });

        if (isListView) {
            displayBudgetsList(filteredBudgets);
        } else {
            displayBudgetsCards(filteredBudgets);
        }
    });

    // Add this helper function to get translated status
    function getTranslatedStatus(status) {
        const statusTranslations = {
            'draft': 'Borrador',
            'pending_approval': 'Pendiente de Aprobación',
            'sent': 'Enviado',
            'negotiation': 'En Negociación',
            'approved': 'Aprobado',
            'rejected': 'Rechazado',
            'expired': 'Expirado',
            'cancelled': 'Cancelado'
        };

        return statusTranslations[status] || status;
    }
});
