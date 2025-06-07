document.addEventListener('DOMContentLoaded', function () {
  $('#unit_of_measure').on('change', function () {
    var selectedUnit = $(this).find('option:selected').text();
    var rawMaterialName = $('#raw-material-name').val() || 'la materia prima';
    if (selectedUnit) {
      $('#unit_example')
        .html(`Ejemplo: 10 ${selectedUnit.toLowerCase()} de ${rawMaterialName}`)
        .css('display', 'block');
    } else {
      $('#unit_example').css('display', 'none');
    }
  });

  $('#raw-material-name').on('input', function () {
    if ($('#unit_of_measure').val()) {
      $('#unit_of_measure').change();
    }
  });
});
