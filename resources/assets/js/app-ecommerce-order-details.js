$(function () {
  // Variable declaration for table
  var dt_details_table = $('.datatables-order-details');
  var products = window.orderProducts;
  var currencySymbol = window.currencySymbol;

  // E-commerce Products datatable
  if (dt_details_table.length) {
    var dt_products = dt_details_table.DataTable({
      data: products,
      columns: [
        {
          // Imagen del producto
          data: 'image',
          render: function(data, type, full, meta) {
            var imagePath = `${baseUrl}${data}`;
            return `
              <img src="${imagePath}"
                   onerror="this.onerror=null; this.src='${baseUrl}admin/default-image.png';"
                   style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;" />
            `;
          }
        },
        {
          // Nombre del producto con variaciones
          data: 'name',
          render: function(data, type, row, meta) {
              var flavors = row.flavors ? '<br><small>' + row.flavors + '</small>' : '';
              return '<span>' + data + flavors + '</span>';
          }
        },
        {
          // Precio del producto
          data: 'price',
          render: function(data, type, full, meta) {
            return `${currencySymbol}${parseFloat(data).toFixed(2)}`;
          }
        },
        { data: 'quantity' },
        {
          // Total por producto
          data: null,
          render: function (data, type, row, meta) {
            return `${currencySymbol}${(row.price * row.quantity).toFixed(2)}`;
          }
        }
      ],
      columnDefs: [
        {
          // Renderizar Precio
          targets: 2,
          render: function (data, type, full, meta) {
            return `${currencySymbol}${parseFloat(data).toFixed(2)}`;
          }
        },
        {
          // Renderizar Total por Producto
          targets: -1,
          render: function (data, type, full, meta) {
            return `${currencySymbol}${(full.price * full.quantity).toFixed(2)}`;
          }
        }
      ],
      order: [2, ''],
      dom: 't'
    });
  }
});
