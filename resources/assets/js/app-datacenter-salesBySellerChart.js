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

  // Chart Colors
  const chartColors = {
    donut: {
      series1: config.colors.success,
      series2: 'rgba(113, 221, 55, 0.6)',
      series3: 'rgba(113, 221, 55, 0.4)',
      series4: 'rgba(113, 221, 55, 0.2)'
    }
  };

  // Obtener los parámetros del filtro
  const startDate = document.querySelector('input[name="start_date"]').value;
  const endDate = document.querySelector('input[name="end_date"]').value;
  const period = document.querySelector('select[name="period"]').value;
  const storeId = document.querySelector('select[name="store_id"]').value;

  // Construir la URL con los parámetros seleccionados
  const url = `datacenter/sales-by-seller?start_date=${startDate}&end_date=${endDate}&period=${period}&store_id=${storeId}`;

  const salesBySellerChartEl = document.querySelector('#salesBySellerChart');

  if (salesBySellerChartEl) {
    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
          // Si no hay datos, mostrar mensaje de "No hay datos disponibles"
          salesBySellerChartEl.innerHTML = '<div class="no-data">No hay datos disponibles</div>';
          salesBySellerChartEl.style.display = 'flex';
          salesBySellerChartEl.style.justifyContent = 'center';
          salesBySellerChartEl.style.alignItems = 'center';
          salesBySellerChartEl.style.height = '420px';
          salesBySellerChartEl.style.fontSize = '1.5rem';
          salesBySellerChartEl.style.color = labelColor;
          return;
        }

        const labels = data.map(item => item.seller);
        const series = data.map(item => parseFloat(item.totalSales));

        // Identificar el vendedor con más ventas
        let maxIndex = series.findIndex(value => value === Math.max(...series));
        let topSellerName = labels[maxIndex];
        let topSellerSales = series[maxIndex];

        const salesBySellerChartConfig = {
          chart: {
            height: 420,
            parentHeightOffset: 0,
            type: 'donut'
          },
          labels: labels,
          series: series,
          colors: [
            chartColors.donut.series1,
            chartColors.donut.series2,
            chartColors.donut.series3,
            chartColors.donut.series4
          ],
          stroke: {
            width: 0
          },
          dataLabels: {
            enabled: false
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
                      return `${window.currencySymbol}${val.toFixed(2)}`; // Concatenar símbolo de moneda
                    }
                  },
                  name: {
                    offsetY: 20,
                    fontFamily: 'Public Sans',
                    color: headingColor
                  },
                  total: {
                    show: true,
                    fontSize: '0.7rem',
                    label: 'Vendedor con más ventas: ' + topSellerName,
                    color: labelColor,
                    formatter: function () {
                      return `${window.currencySymbol}${topSellerSales.toFixed(2)}`; // Total de ventas del mejor vendedor con símbolo de moneda
                    }
                  }
                }
              }
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
              colors: '#181818',
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
              formatter: function (value) {
                return `${window.currencySymbol}${value.toFixed(2)}`; // Concatenar símbolo de moneda
              }
            }
          },
          grid: {
            padding: {
              top: 15
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

        const salesBySellerChart = new ApexCharts(salesBySellerChartEl, salesBySellerChartConfig);
        salesBySellerChart.render();
      })
      .catch(error => console.error('Error loading the data: ', error));
  }
})();
