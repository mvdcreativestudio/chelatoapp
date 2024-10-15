@extends('layouts.layoutMaster')

@section('title', 'Caja registradora')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Gestión /</span> Caja registradora
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

@if (Session::has('open_cash_register_id'))
    @php
        $openCashRegisterId = Session::get('open_cash_register_id');
    @endphp

    @if ($openCashRegisterId)
        <div>
            <h1>Caja Registradora Abierta</h1>
            <p>ID: {{ $openCashRegisterId }}</p>
            <button class="btn btn-danger" id="btn-cerrar-caja" data-id="{{ $openCashRegisterId }}">Cerrar Caja</button>
            <button class="btn btn-primary" id="btn-agregar-productos" data-id="{{ $openCashRegisterId }}">Agregar Productos</button>
        </div>
    @endif

@else
    <div>
        <h1>No hay caja registradora abierta</h1>
    </div>
@endif

<!-- Modal para cerrar la caja registradora -->
<div class="modal fade" id="cerrarCajaModal" tabindex="-1" aria-labelledby="cerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cerrarCajaLabel">Cerrar Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cash_register_id_close" name="cash_register_id_close">
                <p>¿Estás seguro de que deseas cerrar esta caja registradora?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="submit-cerrar-caja" class="btn btn-primary">Cerrar Caja</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar productos -->
<div class="modal fade" id="agregarProductosModal" tabindex="-1" aria-labelledby="agregarProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarProductosLabel">Agregar Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <select id="productosSelect" class="form-control">
                        <option value="">Selecciona un producto</option>
                        <!-- Opciones de productos se cargarán aquí -->
                    </select>
                </div>
                <table class="table" id="productosTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Precio total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Productos seleccionados se agregarán aquí -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th id="totalOrdenValor">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="guardar-productos" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para tipo de cliente y forma de pago -->
<div class="modal fade" id="tipoClienteModal" tabindex="-1" role="dialog" aria-labelledby="tipoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tipoClienteModalLabel">Tipo de Cliente y Forma de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="tipoClienteForm">
                    <div class="form-group">
                        <label for="tipoCliente">Tipo de Cliente</label>
                        <select class="form-control" id="tipoCliente" required>
                            <option value="">Selecciona un tipo de cliente</option>
                            <option value="individual">Individual</option>
                            <option value="company">Compañía</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="formaPago">Forma de Pago</label>
                        <select class="form-control" id="formaPago" required>
                            <option value="">Selecciona una forma de pago</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="pos">POS</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="guardarTipoCliente">Siguiente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del cliente -->
<div class="modal fade" id="detallesClienteModal" tabindex="-1" role="dialog" aria-labelledby="detallesClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesClienteModalLabel">Detalles del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="detallesClienteForm">
                    <div class="form-group">
                        <label for="nombreCliente">Nombre</label>
                        <input type="text" class="form-control" id="nombreCliente" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidoCliente">Apellido</label>
                        <input type="text" class="form-control" id="apellidoCliente" required>
                    </div>
                    <div class="form-group">
                        <label for="emailCliente">Correo Electrónico</label>
                        <input type="email" class="form-control" id="emailCliente" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelarDetallesCliente" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#calcularCambioModal">Saltar</button>
                <button type="button" class="btn btn-primary" id="guardarCliente">Guardar Cliente</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal para calcular el cambio -->
<div class="modal fade" id="calcularCambioModal" tabindex="-1" aria-labelledby="calcularCambioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calcularCambioLabel">Calcular Cambio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="montoPagado">Monto Pagado</label>
                    <input type="number" class="form-control" id="montoPagado" required>
                </div>
                <div class="form-group">
                    <label for="totalOrden">Total de la Orden</label>
                    <input type="number" class="form-control" id="totalOrden" readonly>
                </div>
                <div class="form-group">
                    <label for="cambio">Cambio</label>
                    <input type="number" class="form-control" id="cambio" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="calcularCambio">Calcular</button>
                <button type="button" class="btn btn-primary" id="finalizarOrden">Finalizar</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('vendor-script')
@parent
<script>
    $(document).ready(function() {
        let categories = [];
    let flavors = [];

    let openCashRegisterId = $('#btn-agregar-productos').data('id'); // Obtener el ID de la caja registradora abierta
    let cashRegisterLogId = null; // Variable para guardar el ID de cash register log

    // Función para obtener el ID del registro de caja
    function obtenerCashRegisterLogId() {
        if (openCashRegisterId) {
            $.ajax({
                url: `pdv/log/${openCashRegisterId}`,
                type: 'GET',
                success: function(response) {
                    cashRegisterLogId = response.cash_register_log_id; // Guardar el ID devuelto
                },
                error: function(xhr, status, error) {
                    alert('Error al obtener el ID de cash register log: ' + xhr.responseText);
                }
            });
        } else {
            console.error('ID de caja registradora no definido');
        }
    }

    // Llamar a la función para obtener el ID cuando se carga la página
    obtenerCashRegisterLogId();


    $('#cancelarDetallesCliente').click(function() {
        $('#detallesClienteModal').modal('hide');

        // Calcular el total de la orden
        var totalOrden = calcularTotalOrden();

        // Actualizar el valor del campo 'totalOrden' en el modal de "Calcular Cambio"
        $('#totalOrden').val(totalOrden);

        // Mostrar el modal de "Calcular Cambio"
        $('#calcularCambioModal').modal('show');
    });

     // Finalizar la orden
     $('#finalizarOrden').click(function() {
        var totalOrden = parseFloat($('#totalOrden').val());
        var montoPagado = parseFloat($('#montoPagado').val());
        var cambio = parseFloat($('#cambio').val());

        // Recuperar datos de tipo de cliente y forma de pago
        var tipoCliente = $('#tipoCliente').val();
        var formaPago = $('#formaPago').val();

        // Recuperar las órdenes existentes del localStorage
        var ordenes = JSON.parse(localStorage.getItem('ordenes')) || [];

        const now = new Date();
        const date = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        const hour = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');


        // Construir los datos de la orden
        var ordenData = {
            date: date,
            hour: hour,
            cash_register_log_id: cashRegisterLogId,
            cash_sales: formaPago === 'efectivo' ? totalOrden : 0,
            pos_sales: formaPago === 'pos' ? totalOrden : 0,
            discount: 0,
            client_type: tipoCliente,
            products: JSON.stringify(ordenes[0]),
            subtotal: (formaPago === 'efectivo' ? totalOrden : 0) + (formaPago === 'pos' ? totalOrden : 0),
            total: ((formaPago === 'efectivo' ? totalOrden : 0) + (formaPago === 'pos' ? totalOrden : 0)) - 0, // Resta 0 para acordarnos después de que debemos ver la lógica del tema descuentos.
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: 'pos-orders',
            type: 'POST',
            data: ordenData,
            success: function(response) {
                alert('Orden finalizada correctamente.');
                // Limpiar datos y cerrar modal
                localStorage.removeItem('ordenes');
                $('#calcularCambioModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function(xhr, status, error) {
                alert('Error al finalizar la orden: ' + xhr.responseText);
            }
        });
    });


    // Cargar categorías desde el backend
    function cargarCategorias() {
        $.ajax({
            url: 'pdv/categories',
            type: 'GET',
            success: function(response) {
                if (response && response.categories) {
                    categories = response.categories;
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Cargar variaciones desde el backend
    function cargarVariaciones() {
        $.ajax({
            url: 'pdv/flavors',
            type: 'GET',
            success: function(response) {
                if (response && response.flavors) {
                    flavors = response.flavors;
                } else {
                    alert('No se encontraron variaciones.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar los variaciones: ' + xhr.responseText);
            }
        });
    }

    cargarCategorias();
    cargarVariaciones();

    // Mostrar el modal de cerrar caja al hacer clic en el botón correspondiente
    $('#btn-cerrar-caja').click(function() {
        var cashRegisterId = $(this).data('id');
        $('#cash_register_id_close').val(cashRegisterId);
        $('#cerrarCajaModal').modal('show');
    });

    // Enviar la solicitud para cerrar la caja registradora
    $('#submit-cerrar-caja').click(function() {
        var cashRegisterId = $('#cash_register_id_close').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'pdv/close/' + cashRegisterId,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                $('#cerrarCajaModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function(xhr, status, error) {
                alert('Error al cerrar la caja registradora: ' + xhr.responseText);
            }
        });
    });

    // Cargar productos en el modal desde el backend
    function cargarProductos(cashRegisterId) {
        $.ajax({
            url: 'pdv/products/' + cashRegisterId,
            type: 'GET',
            success: function(response) {
                if (response && response.products) {
                    var productos = response.products;
                    var productosSelect = $('#productosSelect');
                    productosSelect.empty();
                    productosSelect.append('<option value="">Selecciona un producto</option>');

                    productos.forEach(function(producto) {
                        var option = `<option value="${producto.id}" data-name="${producto.name}" data-price="${producto.price}" data-image="${producto.image}" data-type="${producto.type}" data-max-flavors="${producto.max_flavors}">${producto.name}</option>`;
                        productosSelect.append(option);
                    });
                } else {
                    alert('No se encontraron productos.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar los productos: ' + xhr.responseText);
            }
        });
    }

    // Abrir el modal de agregar productos
    $('#btn-agregar-productos').click(function() {

        //Limpieza entre orden y orden.
        localStorage.removeItem('ordenes');
        $('#productosTable tbody').empty();
        $('#totalOrdenValor').text('0.00');

        $('#tipoClienteForm')[0].reset();
        $('#detallesClienteForm')[0].reset();
        $('#montoPagado').val('');
        $('#totalOrden').val('');
        $('#cambio').val('');

        var cashRegisterId = $(this).data('id'); // Obtener el ID de la caja registradora abierta
        cargarProductos(cashRegisterId);
        $('#agregarProductosModal').modal('show');
    });

    // Actualizar el precio total cuando cambia la cantidad
    $(document).on('input', '.cantidad', function() {
        var cantidad = $(this).val();
        var price = $(this).closest('tr').find('.product-price').text();
        var total = cantidad * price;
        $(this).closest('tr').find('.total-price').text(total);
        actualizarTotalOrden();
    });

    // Eliminar un producto de la tabla
    $(document).on('click', '.btn-eliminar-producto', function() {
        var id = $(this).closest('tr').data('id');
        $(this).closest('tr').remove();
        $('.flavor-row[data-id="' + id + '"]').remove();
        actualizarTotalOrden();
    });

    // Función para actualizar el total de la orden
    function actualizarTotalOrden() {
        var total = 0;
        $('#productosTable tbody tr').each(function() {
            // Verifica que no sea una fila de variaciones
            if (!$(this).hasClass('flavor-row')) {
                var price = parseFloat($(this).data('price')) || 0;
                var quantity = parseInt($(this).find('.cantidad').val()) || 0;
                var totalPrice = price * quantity;
                $(this).find('.total-price').text(totalPrice.toFixed(2));
                total += totalPrice;
            }
        });
        $('#totalOrdenValor').text(total.toFixed(2));
    }



    // Agregar un nuevo producto a la tabla cuando se selecciona en el dropdown
$('#productosSelect').change(function() {
    var selectedOption = $(this).find('option:selected');
    var id = selectedOption.val();
    var name = selectedOption.data('name');
    var price = selectedOption.data('price');
    var image = selectedOption.data('image');
    var type = selectedOption.data('type');
    var maxFlavors = selectedOption.data('max-flavors');

    if (id) {
        var tr = `
            <tr data-id="${id}" data-image="${image}" data-type="${type}" data-price="${price}">
                <td>${name}</td>
                <td class="product-price">${price}</td>
                <td><input type="number" class="form-control cantidad" value="1"></td>
                <td class="total-price">${price}</td>
                <td><button class="btn btn-danger btn-eliminar-producto">Eliminar</button></td>
            </tr>
        `;

        if (type === 'configurable') {
            tr += `<tr class="flavor-row" data-id="${id}">
                    <td colspan="5">
                        <select class="form-control flavor-select" multiple>
                            ${flavors.map(flavor => `<option value="${flavor.id}">${flavor.name}</option>`).join('')}
                        </select>
                    </td>
                </tr>`;
         }

        $('#productosTable tbody').append(tr);

        // Resetear el select después de agregar el producto
        $(this).val('');

        // Actualizar el total de la orden después de agregar el producto
        actualizarTotalOrden();
    }
});

    // Guardar los productos seleccionados en localStorage
    $('#guardar-productos').click(function() {
        var productosSeleccionados = [];
        $('#productosTable tbody tr').each(function() {
            var id = $(this).data('id');
            var name = $(this).find('td:first').text();
            var price = $(this).find('.product-price').text();
            var quantity = $(this).find('.cantidad').val();
            var image = $(this).data('image');
            var type = $(this).data('type');
            var productFlavors = [];

            // Obtener variaciones seleccionados si el producto es configurable
            if (type === 'configurable') {
                $(this).next('.flavor-row').find('.flavor-select option:selected').each(function() {
                    productFlavors.push($(this).val());
                });
            }

            // Encontrar el category_id correcto basado en el product_id
            var category = categories.find(category => category.product_id == id);
            var category_id = category ? category.category_id : null;

            // Evitar agregar productos con valores nulos
            if (id && name && price && quantity) {
                productosSeleccionados.push({
                    id: id,
                    name: name.trim(),  // Eliminar espacios en blanco adicionales
                    image: image,
                    price: parseFloat(price),
                    flavors: productFlavors,
                    quantity: parseInt(quantity),
                    category_id: category_id
                });
            }
        });

        // Recuperar las órdenes existentes del localStorage o crear una nueva lista si no existen
        var ordenes = JSON.parse(localStorage.getItem('ordenes')) || [];

        // Agregar la nueva orden a la lista de órdenes
        ordenes.push(productosSeleccionados);

        // Limpieza de tabla post guardar.
        $('#productosTable tbody').empty();

        // Guardar la lista de órdenes actualizada en localStorage
        localStorage.setItem('ordenes', JSON.stringify(ordenes));
        $('#agregarProductosModal').modal('hide');
    });

    // Mostrar el modal de tipo de cliente al guardar productos
    $('#guardar-productos').click(function() {
        $('#tipoClienteModal').modal('show');
    });

    // Manejar el guardado del tipo de cliente y forma de pago
    $('#guardarTipoCliente').click(function() {
        var tipoCliente = $('#tipoCliente').val();
        var formaPago = $('#formaPago').val();

        if (tipoCliente && formaPago) {
            $('#tipoClienteModal').modal('hide');
            $('#detallesClienteModal').modal('show');
        } else {
            alert('Por favor, completa todos los campos.');
        }
    });

    // Manejar el guardado de los detalles del cliente y abrir el modal de calcular cambio
    $('#guardarCliente').click(function() {
        var nombreCliente = $('#nombreCliente').val();
        var apellidoCliente = $('#apellidoCliente').val();
        var emailCliente = $('#emailCliente').val();
        var tipoCliente = $('#tipoCliente').val(); // Obtenido del modal anterior

        if (nombreCliente && apellidoCliente && emailCliente) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: 'pdv/client',
                type: 'POST',
                data: {
                    _token: csrfToken,
                    name: nombreCliente,
                    lastname: apellidoCliente,
                    type: tipoCliente,
                    email: emailCliente
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cliente creado correctamente.');
                        $('#detallesClienteModal').modal('hide');
                        var totalOrden = calcularTotalOrden();
                        $('#totalOrden').val(totalOrden);
                        $('#calcularCambioModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error al crear el cliente: ' + xhr.responseText);
                }
            });
        } else {
            alert('Por favor, completa todos los campos.');
        }
    });



    // Calcular el total de la orden sumando los precios de los productos seleccionados
    function calcularTotalOrden() {
        var total = 0;
        var ordenes = JSON.parse(localStorage.getItem('ordenes')) || [];
        ordenes.forEach(function(orden) {
            orden.forEach(function(producto) {
                total += producto.price * producto.quantity; // Aquí se suma correctamente price * quantity
            });
        });
        return total;
    }


    // Calcular el cambio
    $('#calcularCambio').click(function() {
        var montoPagado = parseFloat($('#montoPagado').val());
        var totalOrden = parseFloat($('#totalOrden').val());
        if (montoPagado >= totalOrden) {
            var cambio = montoPagado - totalOrden;
            $('#cambio').val(cambio);
        } else {
            alert('El monto pagado no puede ser menor que el total de la orden.');
        }
    });
});

    $('#guardar-productos').click(function() {
    var productosSeleccionados = [];
    $('#productosTable tbody tr').each(function() {
        var id = $(this).data('id');
        var name = $(this).find('td:first').text();
        var price = $(this).find('.product-price').text();
        var quantity = $(this).find('.cantidad').val();
        var image = $(this).data('image');
        var type = $(this).data('type');
        var productFlavors = [];

        // Obtener variaciones seleccionados si el producto es configurable
        if (type === 'configurable') {
            $(this).next('.flavor-row').find('.flavor-select option:selected').each(function() {
                productFlavors.push($(this).val());
            });
        }

        // Encontrar el category_id correcto basado en el product_id
        var category = categories.find(category => category.product_id == id);
        var category_id = category ? category.category_id : null;

        productosSeleccionados.push({
            id: id,
            name: name,
            image: image,
            price: parseFloat(price),
            flavors: productFlavors,
            quantity: parseInt(quantity),
            category_id: category_id
        });
    });
    // Recuperar las órdenes existentes del localStorage o crear una nueva lista si no existen
    var ordenes = JSON.parse(localStorage.getItem('ordenes')) || [];

    // Agregar la nueva orden a la lista de órdenes
    ordenes.push(productosSeleccionados);

    // Limpieza de tabla post guardar.
    $('#productosTable tbody').empty();

    // Guardar la lista de órdenes actualizada en localStorage
    localStorage.setItem('ordenes', JSON.stringify(ordenes));
    $('#agregarProductosModal').modal('hide');
});
</script>
@endsection
