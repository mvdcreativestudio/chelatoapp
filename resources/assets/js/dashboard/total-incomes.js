document.addEventListener('DOMContentLoaded', function() {
    const currentMonth = new Date().getMonth() + 1; // Get current month (1-12)
    let totalIncomeChart = null;

    // Chart options
    const chartOptions = {
        series: [{
            name: 'Ingresos',
            data: []
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            events: {
                beforeMount: function() {
                    console.log('Chart mounting...');
                },
                mounted: function() {
                    console.log('Chart mounted.');
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            type: 'numeric',
            categories: [],
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                show: true,
                formatter: function(value) {
                    return `Día ${value}`;
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return `$${value.toFixed(2)}`;
                }
            }
        },
        tooltip: {
            x: {
                formatter: function(value) {
                    return `Día ${value}`;
                }
            },
            y: {
                formatter: function(value) {
                    return `$${value.toFixed(2)}`;
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
                stops: [0, 90, 100]
            }
        }
    };

    // Inicializar el gráfico solo si no ha sido inicializado
    if (!totalIncomeChart) {
        totalIncomeChart = new ApexCharts(
            document.querySelector('#totalIncomeChart'),
            chartOptions
        );
        totalIncomeChart.render();
    }

    // Función para obtener datos mensuales
    const fetchMonthlyData = async (month) => {
        try {
            const response = await fetch(`${window.baseUrl}admin/dashboard/monthly-income/${month}`);
            const data = await response.json();

            const year = new Date().getFullYear();
            const daysInMonth = new Date(year, month, 0).getDate();
            
            const days = Array.from({length: daysInMonth}, (_, i) => i + 1);
            const incomes = new Array(daysInMonth).fill(0);

            data.forEach(item => {
                const dayIndex = parseInt(item.day) - 1;
                incomes[dayIndex] = parseFloat(item.total);
            });

            totalIncomeChart.updateOptions({
                xaxis: {
                    categories: days,
                    tickAmount: daysInMonth,
                    decimalsInFloat: 0,
                    labels: {
                        formatter: (value) => `Día ${Math.floor(value)}`
                    }
                }
            });
            
            totalIncomeChart.updateSeries([{
                name: 'Ingresos',
                data: incomes
            }]);

        } catch (error) {
            console.error('Error fetching monthly income data:', error);
        }
    };

    // Cargar datos iniciales
    fetchMonthlyData(currentMonth);

    // Manejar selección de mes en el dropdown
    const months = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach((item) => {
        item.addEventListener('click', function() {
            const selectedMonth = this.textContent;
            const monthIndex = months.indexOf(selectedMonth) + 1; // Corregir índice

            // Actualizar texto del botón
            this.closest('.btn-group').querySelector('.dropdown-toggle').textContent = selectedMonth;
            
            // Cargar datos para el mes seleccionado
            fetchMonthlyData(monthIndex);
        });
    });
});