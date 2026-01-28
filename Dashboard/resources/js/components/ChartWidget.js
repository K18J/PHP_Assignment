/**
 * ChartWidget Component
 * 
 * A wrapper component for Chart.js that provides easy chart creation,
 * updates, and responsive behavior.
 */
class ChartWidget {
    /**
     * @param {Object} config - Configuration object
     * @param {string} config.containerId - ID of the canvas container element
     * @param {string} config.type - Chart type (line, bar, pie, doughnut, etc.)
     * @param {Object} config.data - Chart.js data object {labels, datasets}
     * @param {Object} config.options - Chart.js options object
     * @param {string} config.title - Chart title
     */
    constructor(config) {
        this.config = {
            containerId: config.containerId,
            type: config.type || 'line',
            data: config.data || { labels: [], datasets: [] },
            options: config.options || {},
            title: config.title || '',
            loading: config.loading || false,
            error: config.error || null
        };

        this.chart = null;
        this.container = document.getElementById(this.config.containerId);
        
        if (!this.container) {
            console.error(`ChartWidget: Container with ID "${this.config.containerId}" not found`);
            return;
        }

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('ChartWidget: Chart.js library is not loaded');
            this.renderError('Chart.js library is required');
            return;
        }

        this.render();
    }

    /**
     * Render the chart
     */
    render() {
        if (this.config.loading) {
            this.renderLoading();
            return;
        }

        if (this.config.error) {
            this.renderError();
            return;
        }

        // Create canvas if it doesn't exist
        let canvas = this.container.querySelector('canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            this.container.innerHTML = '';
            this.container.appendChild(canvas);
        }

        // Destroy existing chart if it exists
        if (this.chart) {
            this.chart.destroy();
        }

        // Merge default options with custom options
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    enabled: true
                },
                title: this.config.title ? {
                    display: true,
                    text: this.config.title,
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                } : {}
            },
            scales: this.config.type !== 'pie' && this.config.type !== 'doughnut' ? {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            } : {}
        };

        const mergedOptions = this.deepMerge(defaultOptions, this.config.options);

        // Create new chart
        const ctx = canvas.getContext('2d');
        this.chart = new Chart(ctx, {
            type: this.config.type,
            data: this.config.data,
            options: mergedOptions
        });
    }

    /**
     * Render loading state
     */
    renderLoading() {
        this.container.innerHTML = `
            <div class="chart-loading text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading chart...</span>
                </div>
                <p class="mt-3 text-muted">Loading chart data...</p>
            </div>
        `;
    }

    /**
     * Render error state
     */
    renderError(message = null) {
        const errorMsg = message || this.config.error || 'Failed to load chart';
        this.container.innerHTML = `
            <div class="alert alert-danger m-0">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Chart Error:</strong> ${errorMsg}
            </div>
        `;
    }

    /**
     * Update chart data
     * @param {Object} data - New chart data
     */
    updateData(data) {
        if (!this.chart) {
            this.config.data = data;
            this.render();
            return;
        }

        this.config.data = data;
        this.chart.data = data;
        this.chart.update();
    }

    /**
     * Update chart options
     * @param {Object} options - New chart options
     */
    updateOptions(options) {
        if (!this.chart) {
            this.config.options = { ...this.config.options, ...options };
            this.render();
            return;
        }

        this.config.options = { ...this.config.options, ...options };
        this.chart.options = this.deepMerge(this.chart.options, options);
        this.chart.update();
    }

    /**
     * Add new data point (for line/bar charts)
     * @param {string} label - Label for the new point
     * @param {Array} values - Values for each dataset
     */
    addDataPoint(label, values) {
        if (!this.chart) return;

        this.chart.data.labels.push(label);
        this.chart.data.datasets.forEach((dataset, index) => {
            dataset.data.push(values[index] || 0);
        });
        this.chart.update();
    }

    /**
     * Remove data point by index
     * @param {number} index - Index of the point to remove
     */
    removeDataPoint(index) {
        if (!this.chart) return;

        this.chart.data.labels.splice(index, 1);
        this.chart.data.datasets.forEach(dataset => {
            dataset.data.splice(index, 1);
        });
        this.chart.update();
    }

    /**
     * Set loading state
     * @param {boolean} loading - Loading state
     */
    setLoading(loading) {
        this.config.loading = loading;
        if (loading) {
            this.renderLoading();
        } else {
            this.render();
        }
    }

    /**
     * Set error state
     * @param {string|null} error - Error message or null to clear
     */
    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.renderError(error);
    }

    /**
     * Destroy the chart instance
     */
    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }

    /**
     * Get the Chart.js instance
     * @returns {Chart|null} Chart instance
     */
    getChart() {
        return this.chart;
    }

    /**
     * Deep merge objects
     * @private
     */
    deepMerge(target, source) {
        const output = { ...target };
        if (this.isObject(target) && this.isObject(source)) {
            Object.keys(source).forEach(key => {
                if (this.isObject(source[key])) {
                    if (!(key in target)) {
                        Object.assign(output, { [key]: source[key] });
                    } else {
                        output[key] = this.deepMerge(target[key], source[key]);
                    }
                } else {
                    Object.assign(output, { [key]: source[key] });
                }
            });
        }
        return output;
    }

    /**
     * Check if value is an object
     * @private
     */
    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    }

    /**
     * Resize chart (useful for responsive containers)
     */
    resize() {
        if (this.chart) {
            this.chart.resize();
        }
    }
}

// Export for ES6 modules
export default ChartWidget;
