$(function () {
  // Variable declaration for table
  var dt_details_table = $('.datatables-order-details');
  var orderId = $('.datatables-order-details').data('order-id');

  // E-commerce Products datatable
  if (dt_details_table.length) {
    var dt_products = dt_details_table.DataTable({
      ajax: {
        url: 'datatable',
        type: 'GET'
      },
      columns: [
        {
          // Image
          data: 'product.image',
          render: function(data, type, full, meta) {
            var imagePath = '/chelatoapp/public/' + data;
            return '<img src="' + imagePath + '" style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;" />';
          }
        },
        {
          // Nombre del producto con sabores
          data: 'product_name',
          render: function(data, type, row, meta) {
              return '<span>' + data + '</span>'; // Renderiza el nombre del producto dentro de un <span>
          }
        },
        { data: 'price' },
        { data: 'quantity' },
        { data: 'total_product' }
      ],
      columnDefs: [
        {
          // Price
          targets: 2,
          render: function (data, type, full, meta) {
            return '$' + data;
          }
        },
        {
          // Total Product
          targets: -1,
          render: function (data, type, full, meta) {
            return '$' + data;
          }
        }
      ],
      order: [2, ''],
      dom: 't'
    });
  }
});
