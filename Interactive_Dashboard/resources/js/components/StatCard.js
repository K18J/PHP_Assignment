/**
 * StatCard Component
 * 
 * A reusable component for displaying metrics with icons, values, and labels.
 * Supports loading states, error handling, and conditional styling.
 */
class StatCard {
    /**
     * @param {Object} config - Configuration object
     * @param {string} config.containerId - ID of the container element
     * @param {string} config.title - Card title/label
     * @param {string|number} config.value - The metric value to display
     * @param {string} config.icon - Icon class (e.g., 'fas fa-users')
     * @param {string} config.color - Bootstrap color class (primary, success, warning, danger, info)
     * @param {string} config.trend - Optional trend indicator ('up', 'down', 'neutral')
     * @param {string} config.trendValue - Optional trend percentage value
     */
    constructor(config) {
        this.config = {
            containerId: config.containerId,
            title: config.title || '',
            value: config.value || 0,
            icon: config.icon || 'fas fa-chart-line',
            color: config.color || 'primary',
            trend: config.trend || null,
            trendValue: config.trendValue || null,
            loading: config.loading || false,
            error: config.error || null
        };
        
        this.container = document.getElementById(this.config.containerId);
        if (!this.container) {
            console.error(`StatCard: Container with ID "${this.config.containerId}" not found`);
            return;
        }
        
        this.render();
    }

    /**
     * Render the stat card HTML
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

        const trendHtml = this.config.trend 
            ? `<small class="text-${this.getTrendColor()}">
                 <i class="fas fa-arrow-${this.config.trend === 'up' ? 'up' : 'down'}"></i>
                 ${this.config.trendValue || ''}
               </small>`
            : '';

        this.container.innerHTML = `
            <div class="card stat-card border-${this.config.color} shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 text-uppercase small">${this.config.title}</h6>
                            <h3 class="mb-0 font-weight-bold text-${this.config.color}">
                                ${this.formatValue(this.config.value)}
                            </h3>
                            ${trendHtml}
                        </div>
                        <div class="stat-icon">
                            <i class="${this.config.icon} fa-2x text-${this.config.color}"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render loading state
     */
    renderLoading() {
        this.container.innerHTML = `
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="skeleton-text mb-2" style="width: 60%; height: 14px; background: #e0e0e0; border-radius: 4px;"></div>
                            <div class="skeleton-text mb-2" style="width: 40%; height: 32px; background: #e0e0e0; border-radius: 4px;"></div>
                        </div>
                        <div class="stat-icon">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render error state
     */
    renderError() {
        this.container.innerHTML = `
            <div class="card stat-card border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 text-uppercase small">${this.config.title}</h6>
                            <p class="text-danger mb-0">
                                <i class="fas fa-exclamation-triangle"></i> 
                                ${this.config.error}
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Update the card with new data
     * @param {Object} data - New data to update
     */
    update(data) {
        Object.assign(this.config, data);
        this.render();
    }

    /**
     * Set loading state
     * @param {boolean} loading - Loading state
     */
    setLoading(loading) {
        this.config.loading = loading;
        this.render();
    }

    /**
     * Set error state
     * @param {string|null} error - Error message or null to clear
     */
    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.render();
    }

    /**
     * Get trend color based on trend direction
     * @returns {string} Bootstrap color class
     */
    getTrendColor() {
        if (this.config.trend === 'up') return 'success';
        if (this.config.trend === 'down') return 'danger';
        return 'muted';
    }

    /**
     * Format value for display
     * @param {string|number} value - Value to format
     * @returns {string} Formatted value
     */
    formatValue(value) {
        if (typeof value === 'number') {
            if (value >= 1000000) {
                return (value / 1000000).toFixed(1) + 'M';
            } else if (value >= 1000) {
                return (value / 1000).toFixed(1) + 'K';
            }
            return value.toLocaleString();
        }
        return value;
    }
}

// Export for ES6 modules
export default StatCard;
