'use strict';

(function () {
  let cardColor, headingColor, labelColor, shadeColor, borderColor, legendColor, heatMap1, heatMap2, heatMap3, heatMap4;

  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    borderColor = config.colors_dark.borderColor;
    legendColor = config.colors_dark.bodyColor;
    shadeColor = 'dark';
    heatMap1 = '#4f51c0';
    heatMap2 = '#595cd9';
    heatMap3 = '#8789ff';
    heatMap4 = '#c3c4ff';
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    borderColor = config.colors.borderColor;
    legendColor = config.colors.bodyColor;
    shadeColor = '';
    heatMap1 = '#e1e2ff';
    heatMap2 = '#c3c4ff';
    heatMap3 = '#a5a7ff';
    heatMap4 = '#696cff';
  }

  // Función para cargar los datos de ingresos totales con filtros temporales
  function loadTotalExpensesData(timeRange) {
    // Realizar una solicitud al servidor con el filtro temporal seleccionado
    fetch('api/monthly-expenses?time_range=' + timeRange)
      .then(response => response.json())
      .then(data => {
        // Aquí deberías actualizar la visualización de los datos con los nuevos datos recibidos
        console.log('Total income data:', data);
        console.log('Time range:', timeRange);
      })
      .catch(error => console.error('Error loading the data: ', error));
  }

  // Manejar el evento de clic en los elementos del menú desplegable
  document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
      // Obtener el valor del filtro temporal seleccionado
      const timeRange = this.textContent.trim();

      // Cargar los datos correspondientes al filtro temporal seleccionado
      loadTotalExpensesData(timeRange);
    });
  });

  // Ingresos totales
  // --------------------------------------------------------------------
  const totalExpensesEl = document.querySelector('#totalExpensesChart');

  if (totalExpensesEl) {
    fetch('api/monthly-expenses')
      .then(response => response.json())
      .then(data => {
        const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
        const totalExpenses = Array(12).fill(0); // Inicializa todos los ingresos a 0

        data.forEach(item => {
          const monthIndex = item.month - 1; // Convertir el mes a índice del arreglo (0-11)
          totalExpenses[monthIndex] = parseInt(item.total);
        });

        const maxValue = Math.max(...totalExpenses);
        const idealTickCount = 10;
        let tickInterval = Math.ceil(maxValue / idealTickCount / 1000) * 2000;

        if (tickInterval < 2000) tickInterval = 2000;

        const topYaxisValue = Math.ceil(maxValue / tickInterval) * tickInterval;

        const totalExpensesConfig = {
          chart: {
            height: 220,
            type: 'area',
            toolbar: false,
            dropShadow: {
              enabled: true,
              top: 14,
              left: 2,
              blur: 3,
              color: config.colors.primary,
              opacity: 0.15
            }
          },
          series: [{
            name: "Ingresos",
            data: totalExpenses
          }],
          labels: monthNames,
          dataLabels: {
            enabled: false
          },
          stroke: {
            width: 2,
            curve: 'straight'
          },
          colors: [config.colors.primary],
          fill: {
            type: 'gradient',
            gradient: {
              shade: 'light',
              shadeIntensity: 0.8,
              opacityFrom: 0.7,
              opacityTo: 0.25,
              stops: [0, 95, 100]
            }
          },
          grid: {
            show: true,
            borderColor: '#90A4AE',
            padding: {
              top: -15,
              bottom: -10,
              left: 0,
              right: 0
            }
          },
          xaxis: {
            categories: monthNames,
            labels: {
              offsetX: 0,
              style: {
                colors: '#757575',
                fontSize: '13px'
              }
            }
          },
          yaxis: {
            labels: {
              offsetX: -15,
              formatter: function (val) {
                return '$' + val.toLocaleString();
              },
              style: {
                fontSize: '13px',
                colors: '#757575'
              }
            },
            min: 0,
            max: topYaxisValue,
            tickAmount: topYaxisValue / tickInterval
          },
          tooltip: {
            x: {
              show: true,
              format: 'dd MMM',
              formatter: function (value, { series, seriesIndex, dataPointIndex, w }) {
                return monthNames[dataPointIndex]; // Muestra el nombre del mes en el tooltip
              }
            },
            y: {
              formatter: function (val, opts) {
                return '$' + val.toLocaleString(); // Formatea el valor monetario
              }
            }
          }
        };

        const totalExpenseChart = new ApexCharts(totalExpensesEl, totalExpensesConfig);
        totalExpenseChart.render();
      })
      .catch(error => console.error('Error loading the data: ', error));
  }
})();
