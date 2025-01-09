document.addEventListener('DOMContentLoaded', function () {
    // Inicializamos la tabla con DataTables
    var table = $('.datatables-batches').DataTable({
        data: batches, // Datos de las fórmulas
        columns: [
            { data: 'batch_number', title: 'N° lote' },
            { data: 'item_name', title: 'Materia prima / Producto' }, // Materia prima o Producto
            { data: 'quantity', title: 'Cantidad' },
            { data: 'production_date', title: 'Fecha producción' },
            { data: 'expiration_date', title: 'Fecha expiración' },
            { data: 'created_at', visible: false },
        ],
        order: [[5, 'desc']],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>'+
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        pageLength: 10,
        searching: true
    });

    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var min = $('#start-time').val();
            var max = $('#end-time').val();
            var expDateStr = data[4]; 
    
            function parseDMY(dateStr) {
                var parts = dateStr.split('-');
                return new Date(parts[2], parts[1] - 1, parts[0]); 
            }
    
            var expDate = parseDMY(expDateStr); 
    
            var minDate = min ? parseDMY(min.split('-').reverse().join('-')) : null;
            var maxDate = max ? parseDMY(max.split('-').reverse().join('-')) : null;
    
        
            if (
                (minDate === null && maxDate === null) ||
                (minDate === null && expDate <= maxDate) ||
                (maxDate === null && expDate >= minDate) ||
                (expDate >= minDate && expDate <= maxDate)
            ) {
                return true;
            }
            return false;
        }
    );
    
    


    $('#filter-button').on('click', function() {
        table.draw();
    });

    $('#reset-filter').on('click', function() {
        $('#start-time').val('');
        $('#end-time').val('');
        table.draw();
    });


    // Lógica para mostrar u ocultar columnas
    $('.toggle-column').on('change', function () {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    $('#item-name-filter').on('keyup', function() {
        table.column(1).search(this.value).draw();
    });

});
