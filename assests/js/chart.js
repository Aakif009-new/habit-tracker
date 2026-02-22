// assets/js/chart.js
// Subject: Web Technology - Data Visualization

class HabitChart {
    constructor() {
        this.chart = null;
        this.init();
    }

    async init() {
        await this.loadWeeklyData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Refresh chart when habits are updated
        document.addEventListener('habitUpdated', () => {
            this.loadWeeklyData();
        });
    }

    async loadWeeklyData() {
        try {
            const response = await fetch('api/get_stats.php', { credentials: 'include' });
            const data = await response.json();
            
            if (data.success && data.stats) {
                this.renderChart(data.stats);
            } else {
                this.renderChart({ dates: [], habits: [] });
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
            this.renderChart({ dates: [], habits: [] });
        }
    }

    renderChart(stats) {
        const canvas = document.getElementById('weeklyChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }

        // Ensure stats has required structure
        const dates = Array.isArray(stats.dates) ? stats.dates : [];
        const habits = Array.isArray(stats.habits) ? stats.habits : [];

        const labels = dates.map(date => {
            const d = new Date(date + 'T12:00:00'); // avoid timezone shift
            return d.toLocaleDateString('en-US', { weekday: 'short' });
        });

        const labelCount = labels.length;
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        const datasets = habits.map((habit) => {
            let data = Array.isArray(habit.daily_completions) ? habit.daily_completions : [];
            data = data.slice(0, labelCount).map(v => Number(v));
            while (data.length < labelCount) data.push(0);
            const color = habit.color || '#4CAF50';
            return {
                label: habit.name || 'Habit',
                data: data,
                backgroundColor: color + '40',
                borderColor: color,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: color,
                pointBorderColor: isDark ? '#2d2d2d' : '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            };
        });

        // If no habits, add placeholder so chart area still renders
        if (datasets.length === 0 && labels.length > 0) {
            datasets.push({
                label: 'No habits yet',
                data: labels.map(() => 0),
                backgroundColor: 'rgba(200, 200, 200, 0.1)',
                borderColor: 'rgba(200, 200, 200, 0.3)',
                borderWidth: 1,
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 0
            });
        }

        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Allow layout to complete so canvas has dimensions (double rAF for reliable layout)
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
            try {
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    color: isDark ? 'rgba(255,255,255,0.9)' : 'rgba(0,0,0,0.8)'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Weekly Habit Completion',
                                font: { size: 16, weight: 'bold' },
                                padding: 20,
                                color: isDark ? 'rgba(255,255,255,0.9)' : 'rgba(0,0,0,0.8)'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: '#fff',
                                bodyColor: '#ddd',
                                borderColor: '#666',
                                borderWidth: 1
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 1,
                                grid: { color: isDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)' },
                                ticks: {
                                    color: isDark ? 'rgba(255,255,255,0.9)' : 'rgba(0,0,0,0.7)',
                                    stepSize: 1,
                                    callback: function(value) {
                                        return value === 1 ? 'Completed' : 'Not Completed';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Completion Status',
                                    color: isDark ? 'rgba(255,255,255,0.8)' : 'rgba(0,0,0,0.7)'
                                }
                            },
                            x: {
                                grid: { color: isDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)' },
                                ticks: { color: isDark ? 'rgba(255,255,255,0.9)' : 'rgba(0,0,0,0.7)' },
                                title: {
                                    display: true,
                                    text: 'Day of Week',
                                    color: isDark ? 'rgba(255,255,255,0.8)' : 'rgba(0,0,0,0.7)'
                                }
                            }
                        }
                    }
                });
            } catch (err) {
                console.error('Chart render error:', err);
            }
            });
        });
    }
}

// Initialize chart
document.addEventListener('DOMContentLoaded', () => {
    new HabitChart();
});