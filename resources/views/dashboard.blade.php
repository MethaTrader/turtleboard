<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Account Management Cards Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- MEXC Accounts Card -->
            <div class="account-card bg-white p-6 rounded-xl shadow-sm animate-fadeInUp">
                <div class="flex justify-between items-center mb-4">
                    <div class="card-icon h-10 w-10 rounded-lg bg-[#5A55D2]/10 flex items-center justify-center">
                        <i class="fas fa-wallet text-[#5A55D2]"></i>
                    </div>
                    <div class="text-sm text-[#808191] flex items-center">
                        <span>MEXC</span>
                        <i class="fas fa-exchange-alt mx-2 text-xs"></i>
                        <span>Accounts</span>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">{{ $stats['mexcAccounts'] }}</h3>
                    </div>
                    <div class="flex items-center text-[#00DEA3]">
                        <i class="fas fa-arrow-up mr-1 text-xs"></i>
                        <span class="text-sm font-medium">{{ $stats['activePercentages']['mexc'] }}%</span>
                    </div>
                </div>
                <div class="mt-4 h-10">
                    <svg viewBox="0 0 100 20" class="w-full h-full">
                        <path d="M0,10 Q25,5 50,10 T100,10" fill="none" stroke="#00DEA3" stroke-width="1" class="animated-line"/>
                    </svg>
                </div>
            </div>

            <!-- Email Accounts Card -->
            <div class="account-card bg-white p-6 rounded-xl shadow-sm animate-fadeInUp delay-100">
                <div class="flex justify-between items-center mb-4">
                    <div class="card-icon h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-envelope text-purple-500"></i>
                    </div>
                    <div class="text-sm text-[#808191] flex items-center">
                        <span>Email</span>
                        <i class="fas fa-exchange-alt mx-2 text-xs"></i>
                        <span>Accounts</span>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">{{ $stats['emailAccounts'] }}</h3>
                    </div>
                    <div class="flex items-center text-purple-500">
                        <i class="fas fa-arrow-up mr-1 text-xs"></i>
                        <span class="text-sm font-medium">{{ $stats['activePercentages']['email'] }}%</span>
                    </div>
                </div>
                <div class="mt-4 h-10">
                    <svg viewBox="0 0 100 20" class="w-full h-full">
                        <path d="M0,10 Q30,15 45,5 T100,10" fill="none" stroke="#7A76E6" stroke-width="1" class="animated-line"/>
                    </svg>
                </div>
            </div>

            <!-- Proxies Card -->
            <div class="account-card bg-white p-6 rounded-xl shadow-sm animate-fadeInUp delay-200">
                <div class="flex justify-between items-center mb-4">
                    <div class="card-icon h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-server text-orange-500"></i>
                    </div>
                    <div class="text-sm text-[#808191] flex items-center">
                        <span>Proxy</span>
                        <i class="fas fa-exchange-alt mx-2 text-xs"></i>
                        <span>Servers</span>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">{{ $stats['proxies'] }}</h3>
                    </div>
                    <div class="flex items-center text-[#00DEA3]">
                        <i class="fas fa-check-circle mr-1 text-xs"></i>
                        <span class="text-sm font-medium">Valid</span>
                    </div>
                </div>
                <div class="mt-4 h-10">
                    <svg viewBox="0 0 100 20" class="w-full h-full">
                        <path d="M0,10 Q25,5 50,10 T100,10" fill="none" stroke="#00DEA3" stroke-width="1" class="animated-line"/>
                    </svg>
                </div>
            </div>

            <!-- Web3 Wallets Card -->
            <div class="account-card bg-white p-6 rounded-xl shadow-sm animate-fadeInUp delay-300">
                <div class="flex justify-between items-center mb-4">
                    <div class="card-icon h-10 w-10 rounded-lg bg-amber-900/10 flex items-center justify-center">
                        <i class="fas fa-link text-amber-800"></i>
                    </div>
                    <div class="text-sm text-[#808191] flex items-center">
                        <span>Web3</span>
                        <i class="fas fa-exchange-alt mx-2 text-xs"></i>
                        <span>Wallets</span>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">{{ $stats['web3Wallets'] }}</h3>
                    </div>
                    <div class="flex items-center text-[#00DEA3]">
                        <i class="fas fa-link mr-1 text-xs"></i>
                        <span class="text-sm font-medium">{{ $stats['connectedWallets'] }} Connected</span>
                    </div>
                </div>
                <div class="mt-4 h-10">
                    <svg viewBox="0 0 100 20" class="w-full h-full">
                        <path d="M0,10 Q25,5 50,10 T100,10" fill="none" stroke="#00DEA3" stroke-width="1" class="animated-line"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Account Overview & Balance Row -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Account Overview Chart -->
            <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-3 animate-fadeInUp delay-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-[#11142D]">Account Overview</h3>
                        <p class="text-sm text-[#00DEA3]">{{ $stats['mexcAccounts'] + $stats['emailAccounts'] + $stats['proxies'] + $stats['web3Wallets'] }} Total Accounts</p>
                    </div>

                    <div class="flex space-x-2" id="chart-tabs">
                        <button class="chart-tab px-4 py-1 rounded-full text-sm active" data-period="all">ALL</button>
                        <button class="chart-tab px-4 py-1 rounded-full text-sm border border-gray-200" data-period="1m">1M</button>
                        <button class="chart-tab px-4 py-1 rounded-full text-sm border border-gray-200" data-period="6m">6M</button>
                        <button class="chart-tab px-4 py-1 rounded-full text-sm border border-gray-200" data-period="1y">1Y</button>
                        <button class="chart-tab px-4 py-1 rounded-full text-sm border border-gray-200" data-period="ytd">YTD</button>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="accountsChart" height="240"></canvas>
                </div>

                <div class="flex items-center justify-end space-x-6 mt-3">
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-[#5A55D2] rounded-full mr-2"></span>
                        <span class="text-sm text-[#808191]">Created</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-[#00DEA3] rounded-full mr-2"></span>
                        <span class="text-sm text-[#808191]">Active</span>
                    </div>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm animate-fadeInUp delay-200">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-[#11142D]">Balance</h3>
                    <button class="bg-[#EFF3FD] text-[#5A55D2] h-8 w-8 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div class="balance-card p-6 mb-6">
                    <div class="text-white/80 text-sm mb-1">Dollar</div>
                    <div class="text-white text-2xl font-bold mb-4">$0.00</div>

                    <div class="credit-card mt-4 h-32 flex flex-col justify-end">
                        <div class="text-white/70 text-xs">****  ****  ****  3921</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities & Team Row -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Recent Activities -->
            <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-3 animate-fadeInUp delay-200">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-[#11142D]">Your Recent Activities</h3>
                </div>

                <div class="space-y-4">
                    <!-- Activity 1 -->
                    <div class="flex items-center border-b border-gray-100 pb-4">
                        <div class="h-10 w-10 rounded-lg bg-[#5A55D2]/10 flex items-center justify-center mr-4">
                            <i class="fas fa-wallet text-[#5A55D2]"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <span class="font-medium">Added MEXC Account</span>
                                <span class="text-sm text-[#808191]">2 hours ago</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-sm text-[#808191]">wallet_34521@gmail.com</span>
                                <span class="status-tag status-completed">Completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity 2 -->
                    <div class="flex items-center border-b border-gray-100 pb-4">
                        <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-purple-500"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <span class="font-medium">Created Email Account</span>
                                <span class="text-sm text-[#808191]">Yesterday at 9:15 AM</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-sm text-[#808191]">new_account@outlook.com</span>
                                <span class="status-tag status-completed">Completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity 3 -->
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center mr-4">
                            <i class="fas fa-link text-amber-800"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <span class="font-medium">Connected Web3 Wallet</span>
                                <span class="text-sm text-[#808191]">May 22, 2023 at 2:30 PM</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-sm text-[#808191]">0x742...8F31</span>
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm animate-fadeInUp delay-300">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-[#11142D]">Team</h3>
                </div>

                <div class="space-y-4">
                    <!-- Team Member 1 -->
                    <div class="flex items-center p-3 rounded-xl bg-[#EFF3FD]">
                        <div class="h-10 w-10 rounded-lg bg-[#5A55D2] flex items-center justify-center mr-3">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-medium">Total Admin</div>
                            <div class="text-sm text-[#808191]">6</div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="flex items-center p-3 rounded-xl bg-[#EFF3FD]">
                        <div class="h-10 w-10 rounded-lg bg-[#00DEA3] flex items-center justify-center mr-3">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div>
                            <div class="font-medium">Team Member</div>
                            <div class="text-sm text-[#808191]">12</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js implementation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data
            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const currentMonth = new Date().getMonth();

            // Data for different time periods
            const chartData = {
                'all': {
                    labels: monthLabels,
                    createdData: [15, 25, 20, 30, 22, 35, 45, 40, 35, 50, 55, 60],
                    activeData: [10, 20, 15, 25, 18, 30, 35, 30, 28, 40, 45, 50]
                },
                '1m': {
                    labels: [...Array(30)].map((_, i) => i + 1),
                    createdData: [...Array(30)].map(() => Math.floor(Math.random() * 10) + 1),
                    activeData: [...Array(30)].map(() => Math.floor(Math.random() * 8) + 1)
                },
                '6m': {
                    labels: monthLabels.slice(currentMonth - 5 >= 0 ? currentMonth - 5 : (currentMonth + 7), currentMonth + 1),
                    createdData: [28, 32, 36, 40, 45, 50],
                    activeData: [22, 25, 30, 32, 38, 42]
                },
                '1y': {
                    labels: monthLabels,
                    createdData: [15, 25, 20, 30, 22, 35, 45, 40, 35, 50, 55, 60],
                    activeData: [10, 20, 15, 25, 18, 30, 35, 30, 28, 40, 45, 50]
                },
                'ytd': {
                    labels: monthLabels.slice(0, currentMonth + 1),
                    createdData: [15, 25, 20, 30, 22, 35].slice(0, currentMonth + 1),
                    activeData: [10, 20, 15, 25, 18, 30].slice(0, currentMonth + 1)
                }
            };

            // Get chart context
            const ctx = document.getElementById('accountsChart').getContext('2d');

            // Create the chart
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
                            displayColors: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                }
                            }
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
    </script>
@endsection