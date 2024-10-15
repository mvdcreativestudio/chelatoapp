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


  document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('averageOrdersByHourChart').getContext('2d');

    // Calcula el valor máximo en los datos
    const maxDataValue = Math.max(...averageOrdersByHourData.flatMap(storeData => storeData.data));

    // Ajuste para que el eje Y muestre un número entero más alto que el valor máximo
    const suggestedMax = Math.ceil(maxDataValue) + 1;

    const averageOrdersByHourChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [
          '00-01', '01-02', '02-03', '03-04', '04-05', '05-06', '06-07', '07-08', '08-09', '09-10',
          '10-11', '11-12', '12-13', '13-14', '14-15', '15-16', '16-17', '17-18', '18-19', '19-20',
          '20-21', '21-22', '22-23', '23-24'
        ],
        datasets: averageOrdersByHourData.map((storeData, index) => ({
          label: storeData.store,
          data: storeData.data,
          borderColor: chartColors.donut[`series${index + 1}`],
          borderWidth: 1,
          backgroundColor: chartColors.donut[`series${index + 1}`],
          fill: false,
          tension: 0,
          pointRadius: 5,
          pointHoverRadius: 20,
        }))
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              color: headingColor,
            }
          },
          tooltip: {
            backgroundColor: '#FFF',
            titleColor: '#000',
            bodyColor: '#000',
            borderColor: '#D1D5DB',
            borderWidth: 1,
            callbacks: {
              label: function (tooltipItem) {
                return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
            },
            ticks: {
              color: labelColor
            }
          },
          y: {
            grid: {
              color: borderColor,
            },
            ticks: {
              color: labelColor,
              stepSize: 1,
              beginAtZero: true,
              callback: function (val) {
                return Number.isFinite(val) ? val.toFixed(2) : val;
              }
            },
            suggestedMax: suggestedMax // Aquí configuramos el valor máximo sugerido
          }
        }
      }
    });

    // Renderizar la gráfica
    averageOrdersByHourChart.render();
  });
})();
