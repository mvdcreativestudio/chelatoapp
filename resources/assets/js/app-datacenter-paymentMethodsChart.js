'use strict';

(function () {
  let cardColor, headingColor, labelColor, borderColor, legendColor;

  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    borderColor = config.colors_dark.borderColor;
    legendColor = config.colors_dark.bodyColor;
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    borderColor = config.colors.borderColor;
    legendColor = config.colors.bodyColor;
  }

  // Obtener los parámetros del filtro
  const startDate = document.querySelector('input[name="start_date"]').value;
  const endDate = document.querySelector('input[name="end_date"]').value;
  const period = document.querySelector('select[name="period"]').value;
  const storeId = document.querySelector('select[name="store_id"]').value;

  // Construir la URL con los parámetros seleccionados
  const url = `${window.paymentMethodsUrl}?start_date=${startDate}&end_date=${endDate}&period=${period}&store_id=${storeId}`;

  fetch(url)
    .then(response => response.json())
    .then(paymentMethodsData => {
      const paymentMethodsChartEl = document.querySelector('#paymentMethodsChart');

      if (!paymentMethodsChartEl) {
        console.error('Elemento #paymentMethodsChart no encontrado en el DOM.');
        return;
      }

      // Verificar si todos los valores son 0
      const allValuesZero = Object.values(paymentMethodsData).every(method => method.amount === 0 && method.percent === 0);

      if (allValuesZero) {
        // Si todos los valores son 0, mostrar mensaje de "No hay datos disponibles"
        paymentMethodsChartEl.innerHTML = '<div class="no-data">No hay datos disponibles</div>';
        paymentMethodsChartEl.style.display = 'flex';
        paymentMethodsChartEl.style.justifyContent = 'center';
        paymentMethodsChartEl.style.alignItems = 'center';
        paymentMethodsChartEl.style.height = '420px';
        paymentMethodsChartEl.style.fontSize = '1.5rem';
        paymentMethodsChartEl.style.color = labelColor;
        return;
      }

      const labels = Object.keys(paymentMethodsData);
      const series = Object.values(paymentMethodsData).map(method => method.percent);
      const amounts = Object.values(paymentMethodsData).map(method => method.amount);

      // Identifica el método de pago con más ventas
      let maxIndex = series.findIndex(value => value === Math.max(...series));
      let topMethodName = labels[maxIndex];
      let topMethodPercent = series[maxIndex];

      const paymentMethodsChartConfig = {
        chart: {
          height: 420,
          parentHeightOffset: 0,
          type: 'donut'
        },
        labels: labels,
        series: series,
        colors: [
          config.colors.success,
          'rgba(113, 221, 55, 0.6)',
          'rgba(113, 221, 55, 0.4)',
          'rgba(113, 221, 55, 0.2)'
        ],
        stroke: {
          width: 0
        },
        dataLabels: {
          enabled: false,
          formatter: function (val, opt) {
            return parseInt(val) + '%';
          }
        },
        legend: {
          show: true,
          position: 'bottom',
          offsetY: 10,
          markers: {
            width: 8,
            height: 8,
            offsetX: -3
          },
          itemMargin: {
            horizontal: 15,
            vertical: 5
          },
          fontSize: '13px',
          fontFamily: 'Public Sans',
          fontWeight: 400,
          labels: {
            colors: headingColor,
            useSeriesColors: false
          }
        },
        tooltip: {
          theme: 'light',
          style: {
            fontSize: '13px',
            fontFamily: 'Public Sans',
            color: '#181818'
          },
          y: {
            formatter: function (value, { series, seriesIndex, dataPointIndex, w }) {
              return `$${amounts[dataPointIndex].toFixed(2)} (${value.toFixed(2)}%)`;
            }
          }
        },
        grid: {
          padding: {
            top: 15
          }
        },
        plotOptions: {
          pie: {
            donut: {
              size: '75%',
              labels: {
                show: true,
                value: {
                  fontSize: '26px',
                  fontFamily: 'Public Sans',
                  color: headingColor,
                  fontWeight: 500,
                  offsetY: -30,
                  formatter: function (val) {
                    return val.toFixed(2) + '%';
                  }
                },
                name: {
                  offsetY: 20,
                  fontFamily: 'Public Sans'
                },
                total: {
                  show: true,
                  fontSize: '0.7rem',
                  label: 'Método con más ventas: ' + topMethodName,
                  color: labelColor,
                  formatter: function (w) {
                    return topMethodPercent.toFixed(2) + '%';
                  }
                }
              }
            }
          }
        },
        responsive: [
          {
            breakpoint: 420,
            options: {
              chart: {
                height: 360
              }
            }
          }
        ]
      };

      const paymentMethodsChart = new ApexCharts(paymentMethodsChartEl, paymentMethodsChartConfig);
      paymentMethodsChart.render();
    })
    .catch(error => console.error('Error al obtener datos de métodos de pago:', error));
})();
