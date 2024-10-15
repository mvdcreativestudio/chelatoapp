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

  const deliveryExceptionsChartEl = document.querySelector('#deliveryExceptionsChart');

  if (deliveryExceptionsChartEl) {
    fetch('api/sales-by-store')
      .then(response => response.json())
      .then(data => {
        const labels = data.map(item => item.store);
        const series = data.map(item => parseFloat(item.percent.replace(',', '.')));

        // Identifica el local con más ventas
        let maxIndex = series.findIndex(value => value === Math.max(...series));
        let topStoreName = labels[maxIndex];
        let topStorePercent = series[maxIndex];

        const deliveryExceptionsChartConfig = {
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
              colors: '#181818',
              useSeriesColors: false
            }
          },
          tooltip: {
            theme: 'light',
            style: {
              fontSize: '13px',
              fontFamily: 'Public Sans',
              color: '#181818' // Cambiar el color del texto del tooltip a #181818
            },
            y: {
              formatter: function(value) {
                return `${value}%`;
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
                      return parseInt(val) + '%';
                    }
                  },
                  name: {
                    offsetY: 20,
                    fontFamily: 'Public Sans',
                    color: '#181818'
                  },
                  total: {
                    show: true,
                    fontSize: '0.7rem',
                    label: 'Empresa con más ventas: ' + topStoreName,
                    color: labelColor,
                    formatter: function (w) {
                      return topStorePercent + '%';
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

        const deliveryExceptionsChart = new ApexCharts(deliveryExceptionsChartEl, deliveryExceptionsChartConfig);
        deliveryExceptionsChart.render();
      })
      .catch(error => console.error('Error loading the data: ', error));
  }
})();
