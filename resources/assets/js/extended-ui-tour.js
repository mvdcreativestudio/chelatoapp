'use strict';

(function () {
  const helpModeBtn = document.querySelector('#help-mode-toggle');
  let helpModeActive = false;
  let tourVar = null;

  function setupTour(tour) {
    const backBtnClass = 'btn btn-sm btn-label-secondary md-btn-flat',
      nextBtnClass = 'btn btn-sm btn-primary btn-next';

    const steps = [
      {
        id: 'tour-inicio',
        title: 'Inicio',
        text: 'Resumen general de tu aplicación. Aquí encontrarás una visión global de todas las actividades y estadísticas importantes.',
        attachTo: { element: '#tour-inicio', on: 'right' },
      },
      {
        id: 'tour-materias-primas',
        title: 'Materias Primas',
        text: 'Maneja tus materias primas desde aquí. Puedes agregar, editar o eliminar materias primas según tus necesidades.',
        attachTo: { element: '#tour-materias-primas', on: 'right' },
      },
      {
        id: 'tour-proveedores',
        title: 'Proveedores',
        text: 'Gestiona tus proveedores. Puedes añadir nuevos proveedores, actualizar información existente o eliminar proveedores que ya no utilizas.',
        attachTo: { element: '#tour-proveedores', on: 'right' },
      },
      {
        id: 'tour-ordenes-de-compra',
        title: 'Órdenes de Compra',
        text: 'Gestiona las órdenes de compra que realizas a tus proveedores. Desde aquí, puedes crear nuevas órdenes, ver el estado de las existentes y más.',
        attachTo: { element: '#tour-ordenes-de-compra', on: 'right' },
      },
      {
        id: 'tour-stock',
        title: 'Stock',
        text: 'Gestiona tu inventario de productos. Esta sección te permite ver el estado actual del inventario, actualizar cantidades y más.',
        attachTo: { element: '#tour-stock', on: 'right' },
      },
      {
        id: 'tour-contabilidad',
        title: 'Contabilidad',
        text: 'Gestiona tu contabilidad y finanzas. Aquí puedes acceder a las facturas, recibos, asientos contables y otros documentos financieros.',
        attachTo: { element: '#tour-contabilidad', on: 'right' },
      },
      {
        id: 'tour-facturas',
        title: 'Facturas',
        text: 'Gestiona las facturas de tus clientes. Puedes crear nuevas facturas, ver el historial de facturación y más.',
        attachTo: { element: '#tour-facturas', on: 'right' },
      },
      {
        id: 'tour-recibos',
        title: 'Recibos',
        text: 'Gestiona los recibos de tus transacciones. Desde aquí, puedes emitir nuevos recibos y revisar los existentes.',
        attachTo: { element: '#tour-recibos', on: 'right' },
      },
      {
        id: 'tour-asientos',
        title: 'Asientos Contables',
        text: 'Gestiona los asientos contables. Esta sección te permite registrar y revisar las transacciones contables de tu empresa.',
        attachTo: { element: '#tour-asientos', on: 'right' },
      },
      {
        id: 'tour-clientes',
        title: 'Clientes',
        text: 'Gestiona la información de tus clientes. Puedes agregar nuevos clientes, actualizar sus datos o eliminar aquellos que ya no necesites.',
        attachTo: { element: '#tour-clientes', on: 'right' },
      },
      {
        id: 'tour-ecommerce',
        title: 'E-Commerce',
        text: 'Gestiona tu tienda en línea y las ventas realizadas a través de ella. Aquí puedes ver los ventas, productos, categorías y más.',
        attachTo: { element: '#tour-ecommerce', on: 'right' },
      },
      {
        id: 'tour-pedidos',
        title: 'Ventas',
        text: 'Gestiona los pedidos de tus clientes. Esta sección te permite ver el estado de los ventas, actualizarlos y gestionar su cumplimiento.',
        attachTo: { element: '#tour-pedidos', on: 'right' },
      },
      {
        id: 'tour-productos',
        title: 'Productos',
        text: 'Gestiona tus productos. Puedes agregar nuevos productos, actualizar información de productos existentes y más.',
        attachTo: { element: '#tour-productos', on: 'right' },
      },
      {
        id: 'tour-variaciones',
        title: 'Variaciones',
        text: 'Gestiona los variaciones de tus productos. Aquí puedes agregar nuevos variaciones, editar los existentes o eliminarlos.',
        attachTo: { element: '#tour-variaciones', on: 'right' },
      },
      {
        id: 'tour-categorias',
        title: 'Categorías de Productos',
        text: 'Gestiona las categorías de tus productos. Puedes crear nuevas categorías, editar las existentes y organizarlas según tus necesidades.',
        attachTo: { element: '#tour-categorias', on: 'right' },
      },
      {
        id: 'tour-marketing-ecommerce',
        title: 'Marketing E-Commerce',
        text: 'Gestiona tus campañas de marketing para tu tienda en línea. Aquí puedes crear nuevas campañas, analizar su rendimiento y más.',
        attachTo: { element: '#tour-marketing-ecommerce', on: 'right' },
      },
      {
        id: 'tour-configuracion-ecommerce',
        title: 'Configuración E-Commerce',
        text: 'Configura tu tienda en línea. Esta sección te permite ajustar la configuración de tu tienda, incluyendo métodos de pago, envíos y más.',
        attachTo: { element: '#tour-configuracion-ecommerce', on: 'right' },
      },
      {
        id: 'tour-marketing',
        title: 'Marketing',
        text: 'Gestiona tus estrategias de marketing. Aquí puedes crear y administrar campañas de marketing, promociones y más.',
        attachTo: { element: '#tour-marketing', on: 'right' },
      },
      {
        id: 'tour-cupones',
        title: 'Cupones',
        text: 'Gestiona los cupones de descuento. Puedes crear nuevos cupones, editar los existentes y revisar su uso.',
        attachTo: { element: '#tour-cupones', on: 'right' },
      },
      {
        id: 'tour-omnicanalidad',
        title: 'Omnicanalidad',
        text: 'Gestiona los canales de comunicación con tus clientes. Esta sección incluye la administración de chats, correos electrónicos y otros canales.',
        attachTo: { element: '#tour-omnicanalidad', on: 'right' },
      },
      {
        id: 'tour-chats-omnicanalidad',
        title: 'Chats',
        text: 'Gestiona los chats con tus clientes. Puedes ver las conversaciones, responder a consultas y mantener una comunicación fluida.',
        attachTo: { element: '#tour-chats-omnicanalidad', on: 'right' },
      },
      {
        id: 'tour-configuracion-omnicanalidad',
        title: 'Configuración Omnicanalidad',
        text: 'Configura los canales de comunicación de tu empresa. Puedes ajustar las preferencias de los chats, correos electrónicos y otros medios.',
        attachTo: { element: '#tour-configuracion-omnicanalidad', on: 'right' },
      },
      {
        id: 'tour-datacenter',
        title: 'Datacenter',
        text: 'Gestiona la información del datacenter. Esta sección te permite ver y administrar los datos relacionados con tus operaciones.',
        attachTo: { element: '#tour-datacenter', on: 'right' },
      },
      {
        id: 'tour-crm',
        title: 'CRM',
        text: 'Gestiona la relación con tus clientes. Aquí puedes ver el historial de interacciones, administrar oportunidades de ventas y más.',
        attachTo: { element: '#tour-crm', on: 'right' },
      },
      {
        id: 'tour-tiendas',
        title: 'Empresas',
        text: 'Gestiona tus tiendas físicas. Puedes ver el estado de cada tienda, actualizar su información y más.',
        attachTo: { element: '#tour-tiendas', on: 'right' },
      },
      {
        id: 'tour-roles',
        title: 'Roles',
        text: 'Gestiona los roles de usuarios. Esta sección te permite definir y asignar roles, así como ajustar permisos según las necesidades de tu empresa.',
        attachTo: { element: '#tour-roles', on: 'right' },
      },
      {
        id: 'tour-configuracion',
        title: 'Configuración',
        text: 'Configura la información de tu empresa. Aquí puedes ajustar los detalles de tu empresa, preferencias de usuario y más.',
        attachTo: { element: '#tour-configuracion', on: 'right' },
      },
      {
        id: 'tour-locales',
        title: 'Locales',
        text: 'Número total de locales que tienes en tu aplicación.',
        attachTo: { element: '#tour-locales', on: 'top' },
      },
      {
        id: 'tour-clientes-registrados',
        title: 'Clientes Registrados',
        text: 'Número total de clientes que se han registrado en tu aplicación.',
        attachTo: { element: '#tour-clientes-registrados', on: 'top' },
      },
      {
        id: 'tour-ingresos-mes',
        title: 'Ingresos del Mes',
        text: 'Monto total de ingresos obtenidos en el mes actual.',
        attachTo: { element: '#tour-ingresos-mes', on: 'top' },
      },
      {
        id: 'tour-ingresos-perdidos',
        title: 'Ingresos Perdidos',
        text: 'Ingresos perdidos en el mes actual debido a pedidos cancelados u otras razones.',
        attachTo: { element: '#tour-ingresos-perdidos', on: 'top' },
      },
      {
        id: 'tour-pedidos-completados',
        title: 'Pedidos Completados',
        text: 'Número total de pedidos completados satisfactoriamente.',
        attachTo: { element: '#tour-pedidos-completados', on: 'top' },
      },
      {
        id: 'tour-pedidos-pendientes',
        title: 'Pedidos Pendientes',
        text: 'Número de pedidos que están pendientes de ser completados. Monitorear este indicador te ayuda a gestionar mejor tu inventario y tu equipo de trabajo.',
        attachTo: { element: '#tour-pedidos-pendientes', on: 'top' },
      },
      {
        id: 'tour-pedidos-cancelados',
        title: 'Pedidos Cancelados',
        text: 'Número de pedidos que han sido cancelados. Es importante analizar las razones detrás de las cancelaciones para mejorar la satisfacción del cliente.',
        attachTo: { element: '#tour-pedidos-cancelados', on: 'top' },
      },
      {
        id: 'tour-ticket-medio',
        title: 'Ticket Medio',
        text: 'Valor promedio de los tickets de compra. Este dato es útil para entender el comportamiento de compra de tus clientes.',
        attachTo: { element: '#tour-ticket-medio', on: 'top' },
      },
      {
        id: 'tour-ingresos-totales',
        title: 'Ingresos Totales',
        text: 'Total de ingresos de todas las tiendas combinadas. Es una visión global del rendimiento financiero de tu empresa.',
        attachTo: { element: '#tour-ingresos-totales', on: 'top' },
      },
      {
        id: 'tour-ingresos-fisico',
        title: 'Ingresos Físico',
        text: 'Ingresos generados a partir de las ventas en tiendas físicas. Te ayuda a evaluar el rendimiento de tus puntos de venta físicos.',
        attachTo: { element: '#tour-ingresos-fisico', on: 'top' },
      },
      {
        id: 'tour-ingresos-ecommerce',
        title: 'Ingresos E-Commerce',
        text: 'Ingresos generados a partir de las ventas en línea. Es esencial para entender el rendimiento de tu tienda online.',
        attachTo: { element: '#tour-ingresos-ecommerce', on: 'top' },
      },
      {
        id: 'tour-ingresos-total',
        title: 'Ingresos Totales Combinados',
        text: 'Total combinado de los ingresos físicos y en línea. Este dato ofrece una visión completa del rendimiento financiero de tu empresa.',
        attachTo: { element: '#tour-ingresos-total', on: 'top' },
      },
      {
        id: 'tour-pill-table',
        title: 'Tabla de Datos',
        text: 'Muestra diferentes categorías de datos en forma de tablas, incluyendo locales, productos y categorías. Es útil para analizar el rendimiento y hacer comparaciones.',
        attachTo: { element: '#tour-pill-table', on: 'top' },
      },
      {
        id: 'tour-locales-tab',
        title: 'Locales',
        text: 'Desglose detallado de las ventas por cada local. Ayuda a comparar el rendimiento de tus diferentes ubicaciones.',
        attachTo: { element: '#tour-locales-tab', on: 'top' },
      },
      {
        id: 'tour-productos-tab',
        title: 'Productos',
        text: 'Muestra el rendimiento de tus productos en términos de ventas. Ayuda a identificar los productos más vendidos y los que necesitan promoción.',
        attachTo: { element: '#tour-productos-tab', on: 'top' },
      },
      {
        id: 'tour-categorias-tab',
        title: 'Categorías',
        text: 'Desglose de las ventas por categoría de productos. Ayuda a analizar qué categorías son más populares entre tus clientes.',
        attachTo: { element: '#tour-categorias-tab', on: 'top' },
      },
      {
        id: 'tour-ventas-por-local',
        title: 'Ventas por Local',
        text: 'Gráfico que muestra las ventas por cada local. Permite comparar el rendimiento de tus diferentes ubicaciones.',
        attachTo: { element: '#tour-ventas-por-local', on: 'top' },
      },
      {
        id: 'tour-stores',
        title: 'Estado de las Empresa',
        text: 'Muestra el estado de tus Empresas físicas. Puedes ver si están abiertas, cerradas y modificar su estado.',
        attachTo: { element: '#tour-stores', on: 'left' },
      }
    ];

    steps.forEach(step => {
      tour.addStep(step);
    });

    return tour;
  }

  function startTourFromElement(elementId) {
    if (!tourVar) {
      tourVar = new Shepherd.Tour({
        defaultStepOptions: {
          scrollTo: false,
          cancelIcon: {
            enabled: true
          }
        },
        useModalOverlay: true
      });
      setupTour(tourVar);
    }
    const step = tourVar.steps.find(step => step.id === elementId);
    if (step) {
      tourVar.show(step.id);
    } else {
      console.warn(`No se encontró el paso para el elemento con id: ${elementId}`);
    }
  }

  if (helpModeBtn) {
    helpModeBtn.onclick = function () {
      helpModeActive = !helpModeActive;
      helpModeBtn.textContent = helpModeActive ? 'Desactivar Modo Ayuda' : 'Activar Modo Ayuda';
      if (helpModeActive) {
        document.body.addEventListener('click', handleElementClick);
      } else {
        document.body.removeEventListener('click', handleElementClick);
      }
    };
  }

  function handleElementClick(event) {
    const element = event.target.closest('[id]');
    if (element && element.id) {
      event.preventDefault();
      startTourFromElement(element.id);
    }
  }
})();

