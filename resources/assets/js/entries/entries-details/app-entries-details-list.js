$(function () {
  $('#export-excel').on('click', function () {
    // Obtener el ID del asiento
    let entryId = $('#entryId').val(); // Asegúrate de tener un input hidden o algún valor que contenga el ID del asiento

    // Construir la URL para exportar a Excel
    let url = `/admin/entry-details-export-excel/${entryId}`;

    // Redirigir a la ruta para exportar, abriendo en una nueva pestaña
    window.open(url, '_blank');
});

$('#export-pdf').on('click', function () {
    // Obtener el ID del asiento
    let entryId = $('#entryId').val(); // Asegúrate de tener un input hidden o algún valor que contenga el ID del asiento

    // Construir la URL para exportar a PDF
    let url = `/admin/entry-details-export-pdf/${entryId}`;

    // Redirigir a la ruta para exportar, abriendo en una nueva pestaña
    window.open(url, '_blank');
});

});
