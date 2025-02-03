// Import Chart.js library
import Chart from 'chart.js/auto';

// Chart instances
const charts = new Map();

// Default chart options
const DEFAULT_OPTIONS = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                usePointStyle: true
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            padding: 12,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1
        }
    }
};

// Chart colors
const COLORS = {
    primary: '#007bff',
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    secondary: '#6c757d'
};

// Chart types
const CHART_TYPES = {
    LINE: 'line',
    BAR: 'bar',
    PIE: 'pie',
    DOUGHNUT: 'doughnut',
    AREA: 'line' // Area chart is a line chart with fill
};

// Create chart
const createChart = (containerId, type, data, options = {}) => {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container with id '${containerId}' not found`);
        return null;
    }

    // Destroy existing chart if it exists
    destroyChart(containerId);

    // Create canvas element
    const canvas = document.createElement('canvas');
    container.appendChild(canvas);

    // Merge default options with custom options
    const chartOptions = {
        ...DEFAULT_OPTIONS,
        ...options
    };

    // Create new chart
    const chart = new Chart(canvas, {
        type,
        data,
        options: chartOptions
    });

    // Store chart instance
    charts.set(containerId, chart);

    return chart;
};

// Update chart data
const updateChart = (containerId, newData, animate = true) => {
    const chart = charts.get(containerId);
    if (!chart) {
        console.error(`Chart with id '${containerId}' not found`);
        return;
    }

    chart.data = {
        ...chart.data,
        ...newData
    };

    chart.update(animate ? 'default' : 'none');
};

// Destroy chart
const destroyChart = (containerId) => {
    const chart = charts.get(containerId);
    if (chart) {
        chart.destroy();
        charts.delete(containerId);
    }
};

// Create line chart
const createLineChart = (containerId, data, options = {}) => {
    const defaultOptions = {
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    };

    return createChart(containerId, CHART_TYPES.LINE, data, {
        ...defaultOptions,
        ...options
    });
};

// Create bar chart
const createBarChart = (containerId, data, options = {}) => {
    const defaultOptions = {
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    };

    return createChart(containerId, CHART_TYPES.BAR, data, {
        ...defaultOptions,
        ...options
    });
};

// Create pie chart
const createPieChart = (containerId, data, options = {}) => {
    const defaultOptions = {
        cutout: 0,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };

    return createChart(containerId, CHART_TYPES.PIE, data, {
        ...defaultOptions,
        ...options
    });
};

// Create doughnut chart
const createDoughnutChart = (containerId, data, options = {}) => {
    const defaultOptions = {
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };

    return createChart(containerId, CHART_TYPES.DOUGHNUT, data, {
        ...defaultOptions,
        ...options
    });
};

// Create area chart
const createAreaChart = (containerId, data, options = {}) => {
    const defaultOptions = {
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    };

    // Ensure all datasets have fill option
    const modifiedData = {
        ...data,
        datasets: data.datasets.map(dataset => ({
            ...dataset,
            fill: true
        }))
    };

    return createChart(containerId, CHART_TYPES.AREA, modifiedData, {
        ...defaultOptions,
        ...options
    });
};

// Helper functions
const generateColors = (count) => {
    const baseColors = Object.values(COLORS);
    const colors = [];

    for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
    }

    return colors;
};

const formatNumber = (number) => {
    if (number >= 1000000) {
        return (number / 1000000).toFixed(1) + 'M';
    }
    if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'K';
    }
    return number.toString();
};

// Initialize charts module
export const initializeCharts = () => {
    // Add window resize handler for responsive charts
    window.addEventListener('resize', () => {
        charts.forEach(chart => {
            chart.resize();
        });
    });

    // Add theme change handler
    document.addEventListener('themeChanged', () => {
        charts.forEach(chart => {
            updateChartTheme(chart);
        });
    });
};

// Update chart theme
const updateChartTheme = (chart) => {
    const isDarkMode = document.body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#ffffff' : '#666666';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    if (chart.options.scales) {
        Object.values(chart.options.scales).forEach(scale => {
            if (scale.ticks) {
                scale.ticks.color = textColor;
            }
            if (scale.grid) {
                scale.grid.color = gridColor;
            }
        });
    }

    if (chart.options.plugins?.legend?.labels) {
        chart.options.plugins.legend.labels.color = textColor;
    }

    chart.update();
};

// Export charts module
export {
    createChart as create,
    updateChart as update,
    destroyChart as destroy,
    createLineChart as line,
    createBarChart as bar,
    createPieChart as pie,
    createDoughnutChart as doughnut,
    createAreaChart as area,
    generateColors,
    formatNumber
}; 