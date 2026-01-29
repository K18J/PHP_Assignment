/**
 * Main Dashboard JavaScript
 * 
 * Orchestrates all dashboard components and handles API communication
 */

// Import components (using ES6 modules)
import StatCard from './components/StatCard.js';
import DataTable from './components/DataTable.js';
import ChartWidget from './components/ChartWidget.js';

// Import Chart.js
import Chart from 'chart.js/auto';

// Make Chart available globally for ChartWidget
window.Chart = Chart;

class Dashboard {
    constructor() {
        this.statCards = {};
        this.dataTable = null;
        this.charts = {};
        this.apiBaseUrl = '/api/dashboard';
        this.refreshInterval = null;
        this.refreshIntervalTime = 30000; // 30 seconds
        
        this.init();
    }

    /**
     * Initialize dashboard
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup dashboard components
     */
    setup() {
        console.log('Initializing Dashboard...');
        
        // Initialize stat cards
        this.initStatCards();
        
        // Initialize data table
        this.initDataTable();
        
        // Initialize charts
        this.initCharts();
        
        // Load initial data
        this.loadDashboardData();
        
        // Setup auto-refresh
        this.setupAutoRefresh();
        
        // Setup manual refresh button if exists
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadDashboardData());
        }
    }

    /**
     * Initialize stat cards
     */
    initStatCards() {
        const statCardContainers = [
            { id: 'stat-total-users', title: 'Total Users', icon: 'fas fa-users', color: 'primary' },
            { id: 'stat-total-orders', title: 'Total Orders', icon: 'fas fa-shopping-cart', color: 'success' },
            { id: 'stat-revenue', title: 'Revenue', icon: 'fas fa-dollar-sign', color: 'info' },
            { id: 'stat-growth', title: 'Growth', icon: 'fas fa-chart-line', color: 'warning' }
        ];

        statCardContainers.forEach(stat => {
            this.statCards[stat.id] = new StatCard({
                containerId: stat.id,
                title: stat.title,
                value: 0,
                icon: stat.icon,
                color: stat.color,
                loading: true
            });
        });
    }

    /**
     * Initialize data table
     */
    initDataTable() {
        const tableContainer = document.getElementById('data-table-container');
        if (!tableContainer) return;

        this.dataTable = new DataTable({
            containerId: 'data-table-container',
            columns: [
                { key: 'id', label: 'ID', sortable: true },
                { key: 'name', label: 'Name', sortable: true },
                { key: 'email', label: 'Email', sortable: true },
                { 
                    key: 'status', 
                    label: 'Status', 
                    sortable: true,
                    render: (value) => {
                        const badgeClass = value === 'active' ? 'success' : 'secondary';
                        return `<span class="badge badge-${badgeClass}">${value}</span>`;
                    }
                },
                { 
                    key: 'created_at', 
                    label: 'Created', 
                    sortable: true,
                    render: (value) => {
                        return new Date(value).toLocaleDateString();
                    }
                }
            ],
            data: [],
            itemsPerPage: 10,
            searchable: true,
            loading: true,
            actions: [
                { key: 'view', label: 'View', icon: 'fas fa-eye', color: 'info' },
                { key: 'edit', label: 'Edit', icon: 'fas fa-edit', color: 'primary' },
                { key: 'delete', label: 'Delete', icon: 'fas fa-trash', color: 'danger' }
            ],
            onRowClick: (rowData, rowElement) => {
                console.log('Row clicked:', rowData);
                // Handle row click
            },
            onAction: (action, rowData, button) => {
                console.log('Action clicked:', action, rowData);
                // Handle action button click
                this.handleTableAction(action, rowData);
            }
        });
    }

    /**
     * Initialize charts
     */
    initCharts() {
        // Sales Chart (Line Chart)
        const salesChartContainer = document.getElementById('sales-chart');
        if (salesChartContainer) {
            this.charts.sales = new ChartWidget({
                containerId: 'sales-chart',
                type: 'line',
                title: 'Sales Over Time',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },
                loading: true
            });
        }

        // Revenue Chart (Bar Chart)
        const revenueChartContainer = document.getElementById('revenue-chart');
        if (revenueChartContainer) {
            this.charts.revenue = new ChartWidget({
                containerId: 'revenue-chart',
                type: 'bar',
                title: 'Revenue by Category',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                },
                loading: true
            });
        }

        // Distribution Chart (Pie Chart)
        const distributionChartContainer = document.getElementById('distribution-chart');
        if (distributionChartContainer) {
            this.charts.distribution = new ChartWidget({
                containerId: 'distribution-chart',
                type: 'pie',
                title: 'User Distribution',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                },
                loading: true
            });
        }
    }

    /**
     * Load dashboard data from API
     */
    async loadDashboardData() {
        try {
            // Load stats
            await this.loadStats();
            
            // Load table data
            await this.loadTableData();
            
            // Load chart data
            await this.loadChartData();
            
            console.log('Dashboard data loaded successfully');
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.handleError(error);
        }
    }

    /**
     * Load statistics data
     */
    async loadStats() {
        try {
            const response = await axios.get(`${this.apiBaseUrl}/stats`);
            const stats = response.data;

            // Update stat cards
            if (this.statCards['stat-total-users']) {
                this.statCards['stat-total-users'].update({
                    value: stats.total_users || 0,
                    trend: stats.users_trend || null,
                    trendValue: stats.users_trend_value || null,
                    loading: false
                });
            }

            if (this.statCards['stat-total-orders']) {
                this.statCards['stat-total-orders'].update({
                    value: stats.total_orders || 0,
                    trend: stats.orders_trend || null,
                    trendValue: stats.orders_trend_value || null,
                    loading: false
                });
            }

            if (this.statCards['stat-revenue']) {
                this.statCards['stat-revenue'].update({
                    value: stats.revenue || 0,
                    trend: stats.revenue_trend || null,
                    trendValue: stats.revenue_trend_value || null,
                    loading: false
                });
            }

            if (this.statCards['stat-growth']) {
                this.statCards['stat-growth'].update({
                    value: stats.growth || 0,
                    trend: stats.growth_trend || null,
                    trendValue: stats.growth_trend_value || null,
                    loading: false
                });
            }
        } catch (error) {
            console.error('Error loading stats:', error);
            Object.values(this.statCards).forEach(card => {
                card.setError('Failed to load data');
            });
        }
    }

    /**
     * Load table data
     */
    async loadTableData() {
        if (!this.dataTable) return;

        try {
            this.dataTable.setLoading(true);
            const response = await axios.get(`${this.apiBaseUrl}/table-data`);
            this.dataTable.updateData(response.data);
            this.dataTable.setLoading(false);
        } catch (error) {
            console.error('Error loading table data:', error);
            this.dataTable.setError('Failed to load table data. Please try again.');
        }
    }

    /**
     * Load chart data
     */
    async loadChartData() {
        try {
            const response = await axios.get(`${this.apiBaseUrl}/chart-data`);
            const chartData = response.data;

            // Update sales chart
            if (this.charts.sales && chartData.sales) {
                this.charts.sales.updateData({
                    labels: chartData.sales.labels || [],
                    datasets: chartData.sales.datasets || []
                });
                this.charts.sales.setLoading(false);
            }

            // Update revenue chart
            if (this.charts.revenue && chartData.revenue) {
                this.charts.revenue.updateData({
                    labels: chartData.revenue.labels || [],
                    datasets: chartData.revenue.datasets || []
                });
                this.charts.revenue.setLoading(false);
            }

            // Update distribution chart
            if (this.charts.distribution && chartData.distribution) {
                this.charts.distribution.updateData({
                    labels: chartData.distribution.labels || [],
                    datasets: chartData.distribution.datasets || []
                });
                this.charts.distribution.setLoading(false);
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
            Object.values(this.charts).forEach(chart => {
                chart.setError('Failed to load chart data');
            });
        }
    }

    /**
     * Handle table actions
     */
    handleTableAction(action, rowData) {
        switch (action) {
            case 'view':
                alert(`Viewing: ${rowData.name || rowData.id}`);
                break;
            case 'edit':
                alert(`Editing: ${rowData.name || rowData.id}`);
                break;
            case 'delete':
                if (confirm(`Are you sure you want to delete ${rowData.name || rowData.id}?`)) {
                    this.deleteRow(rowData.id);
                }
                break;
        }
    }

    /**
     * Delete a row
     */
    async deleteRow(id) {
        try {
            await axios.delete(`${this.apiBaseUrl}/table-data/${id}`);
            this.loadTableData(); // Reload table
        } catch (error) {
            console.error('Error deleting row:', error);
            alert('Failed to delete item. Please try again.');
        }
    }

    /**
     * Setup auto-refresh
     */
    setupAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        this.refreshInterval = setInterval(() => {
            console.log('Auto-refreshing dashboard data...');
            this.loadDashboardData();
        }, this.refreshIntervalTime);
    }

    /**
     * Handle errors
     */
    handleError(error) {
        const errorMessage = error.response?.data?.message || error.message || 'An error occurred';
        
        // Show error notification (you can replace this with a toast library)
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        errorAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        errorAlert.innerHTML = `
            <strong>Error:</strong> ${errorMessage}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        document.body.appendChild(errorAlert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorAlert.remove();
        }, 5000);
    }

    /**
     * Cleanup on page unload
     */
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
    }
}

// Initialize dashboard when script loads
const dashboard = new Dashboard();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    dashboard.destroy();
});

// Export for use in other modules if needed
export default Dashboard;
