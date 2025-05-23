<!-- public/js/chart-data.js -->
document.addEventListener('DOMContentLoaded', function() {
    // Chart data for different time periods
    const chartData = {
        'all': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            createdData: [15, 25, 20, 30, 22, 35, 45, 40, 35, 50, 55, 60],
            activeData: [10, 20, 15, 25, 18, 30, 35, 30, 28, 40, 45, 50]
        },
        '1m': {
            labels: Array.from({length: 30}, (_, i) => i + 1),
            createdData: Array.from({length: 30}, () => Math.floor(Math.random() * 10) + 1),
            activeData: Array.from({length: 30}, () => Math.floor(Math.random() * 8) + 1)
        },
        '6m': {
            labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
            createdData: [28, 32, 36, 40, 45, 50],
            activeData: [22, 25, 30, 32, 38, 42]
        },
        '1y': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            createdData: [15, 25, 20, 30, 22, 35, 45, 40, 35, 50, 55, 60],
            activeData: [10, 20, 15, 25, 18, 30, 35, 30, 28, 40, 45, 50]
        },
        'ytd': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            createdData: [15, 25, 20, 30, 22],
            activeData: [10, 20, 15, 25, 18]
        }
    };

    // Create chart
    const ctx = document.getElementById('accountsChart').getContext('2d');
    const accountsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData['all'].labels,
            datasets: [
                {
                    label: 'Created Accounts',
                    data: chartData['all'].createdData,
                    borderColor: '#5A55D2',
                    backgroundColor: 'rgba(90, 85, 210, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#5A55D2',
                    pointRadius: 0,
                    pointHoverRadius: 4
                },
                {
                    label: 'Active Accounts',
                    data: chartData['all'].activeData,
                    borderColor: '#00DEA3',
                    backgroundColor: 'rgba(0, 222, 163, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#00DEA3',
                    pointRadius: 0,
                    pointHoverRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#FFF',
                    titleColor: '#11142D',
                    bodyColor: '#808191',
                    borderColor: '#E2E8F0',
                    borderWidth: 1,
                    displayColors: false
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Tab switching logic
    const tabs = document.querySelectorAll('.chart-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Get the period from data attribute
            const period = this.getAttribute('data-period');

            // Update chart data
            accountsChart.data.labels = chartData[period].labels;
            accountsChart.data.datasets[0].data = chartData[period].createdData;
            accountsChart.data.datasets[1].data = chartData[period].activeData;

            // Update chart
            accountsChart.update();
        });
    });
});