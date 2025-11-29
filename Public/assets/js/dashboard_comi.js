const chartBar = document.getElementById("barChart");
const chartPie = document.getElementById("pieChart");
const chartPolar = document.getElementById("polarChart");

if (chartPolar) {
    new Chart(chartPolar, {
        type: 'polarArea',
        data: {
            labels: [
                'Peticiones',
                'Quejas',
                'Reclamos',
                'Sugerencia',
                'Denuncia'
            ],
            datasets: [{
                label: 'Cantidad',
                data: [25, 30, 67, 12, 4],
                backgroundColor: [
                    'rgb(79, 70, 229, 0.7)',
                    'rgb(239, 68, 68, 0.7)',
                    'rgb(245, 158, 11, 0.7)',
                    'rgb(16, 185, 129, 0.7)',
                    'rgb(54, 162, 235, 0.7)'
                ],
                borderColor: 'black',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    display: false
                }
            }
        }
    });
}

if (chartPie) {
    new Chart(chartPie, {
        type: 'pie',
        data: {
            labels: [
                'No atendido',
                'Por atender',
                'atendido'
            ],
            datasets: [{
                label: 'Casos por estado',
                data: [10, 15, 20,],
                backgroundColor: [
                    'rgb(239, 68, 68)',
                    'rgb(79, 70, 229)',
                    'rgb(16, 185, 129)'
                ],
                borderColor: 'black',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

if (chartBar) {
    new Chart(chartBar, {
        type: 'bar',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            datasets: [{
                label: 'Casos Registrados',
                data: [12, 19, 3, 5, 2, 4, 12, 28, 12, 32, 12, 4],
                backgroundColor: 'rgb(54, 162, 235)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
