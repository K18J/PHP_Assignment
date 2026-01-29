/**
 * Interactive Dashboard - Bundled JavaScript
 * This is a compiled version that works without webpack/mix
 * For production, run: npm run dev
 */

// StatCard Component
class StatCard {
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

    renderLoading() {
        this.container.innerHTML = `
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="skeleton-text mb-2" style="width: 60%; height: 14px; background: #e0e0e0; border-radius: 4px; animation: skeleton-loading 1.5s ease-in-out infinite;"></div>
                            <div class="skeleton-text mb-2" style="width: 40%; height: 32px; background: #e0e0e0; border-radius: 4px; animation: skeleton-loading 1.5s ease-in-out infinite;"></div>
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

    update(data) {
        Object.assign(this.config, data);
        this.render();
    }

    setLoading(loading) {
        this.config.loading = loading;
        this.render();
    }

    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.render();
    }

    getTrendColor() {
        if (this.config.trend === 'up') return 'success';
        if (this.config.trend === 'down') return 'danger';
        return 'muted';
    }

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

// DataTable Component (simplified for bundle)
class DataTable {
    constructor(config) {
        this.config = {
            containerId: config.containerId,
            columns: config.columns || [],
            data: config.data || [],
            itemsPerPage: config.itemsPerPage || 10,
            searchable: config.searchable !== false,
            loading: config.loading || false,
            error: config.error || null,
            onRowClick: config.onRowClick || null,
            onAction: config.onAction || null,
            actions: config.actions || []
        };

        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.filterText = '';
        this.filteredData = [...this.config.data];

        this.container = document.getElementById(this.config.containerId);
        if (!this.container) {
            console.error(`DataTable: Container with ID "${this.config.containerId}" not found`);
            return;
        }

        this.render();
        this.attachEvents();
    }

    render() {
        if (this.config.loading) {
            this.renderLoading();
            return;
        }

        if (this.config.error) {
            this.renderError();
            return;
        }

        const paginatedData = this.getPaginatedData();
        
        this.container.innerHTML = `
            <div class="data-table-wrapper">
                ${this.renderSearch()}
                ${this.renderTable(paginatedData)}
                ${this.renderPagination()}
            </div>
        `;

        this.attachEvents();
    }

    renderSearch() {
        if (!this.config.searchable) return '';

        return `
            <div class="mb-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="${this.config.containerId}-search"
                        placeholder="Search..."
                        value="${this.filterText}"
                    >
                </div>
            </div>
        `;
    }

    renderTable(data) {
        if (data.length === 0) {
            return `
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No data available
                </div>
            `;
        }

        return `
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            ${this.config.columns.map(col => `
                                <th 
                                    class="${col.sortable !== false ? 'sortable' : ''}"
                                    data-column="${col.key}"
                                    style="cursor: ${col.sortable !== false ? 'pointer' : 'default'};"
                                >
                                    ${col.label}
                                    ${this.getSortIcon(col.key)}
                                </th>
                            `).join('')}
                            ${this.config.actions.length > 0 ? '<th>Actions</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map((row, index) => this.renderRow(row, index)).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderRow(row, index) {
        const rowClass = this.config.onRowClick ? 'clickable-row' : '';
        return `
            <tr 
                class="${rowClass}" 
                data-row-index="${index}"
                data-row-id="${row.id || index}"
            >
                ${this.config.columns.map(col => `
                    <td>${this.renderCell(row, col)}</td>
                `).join('')}
                ${this.config.actions.length > 0 ? `
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${this.config.actions.map(action => `
                                <button 
                                    class="btn btn-${action.color || 'secondary'} btn-action"
                                    data-action="${action.key}"
                                    data-row-id="${row.id || index}"
                                    title="${action.label || action.key}"
                                >
                                    <i class="${action.icon || 'fas fa-ellipsis-h'}"></i>
                                </button>
                            `).join('')}
                        </div>
                    </td>
                ` : ''}
            </tr>
        `;
    }

    renderCell(row, column) {
        if (column.render && typeof column.render === 'function') {
            return column.render(row[column.key], row);
        }
        return row[column.key] || '-';
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredData.length / this.config.itemsPerPage);
        if (totalPages <= 1) return '';

        const pages = [];
        const maxVisible = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }

        return `
            <nav aria-label="Table pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    ${pages.map(page => `
                        <li class="page-item ${page === this.currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${page}">${page}</a>
                        </li>
                    `).join('')}
                    <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="text-center text-muted small">
                    Showing ${this.getStartIndex() + 1} to ${this.getEndIndex()} of ${this.filteredData.length} entries
                </div>
            </nav>
        `;
    }

    renderLoading() {
        this.container.innerHTML = `
            <div class="data-table-loading text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading data...</p>
            </div>
        `;
    }

    renderError() {
        this.container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> ${this.config.error}
            </div>
        `;
    }

    attachEvents() {
        const searchInput = document.getElementById(`${this.config.containerId}-search`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterText = e.target.value;
                this.currentPage = 1;
                this.applyFilter();
            });
        }

        const sortableHeaders = this.container.querySelectorAll('th.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-column');
                this.sort(column);
            });
        });

        const paginationLinks = this.container.querySelectorAll('.page-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.getAttribute('data-page');
                if (page === 'prev') {
                    this.goToPage(this.currentPage - 1);
                } else if (page === 'next') {
                    this.goToPage(this.currentPage + 1);
                } else {
                    this.goToPage(parseInt(page));
                }
            });
        });

        if (this.config.onRowClick) {
            const rows = this.container.querySelectorAll('.clickable-row');
            rows.forEach(row => {
                row.addEventListener('click', (e) => {
                    if (!e.target.closest('.btn-action')) {
                        const rowId = row.getAttribute('data-row-id');
                        const rowData = this.getRowData(rowId);
                        this.config.onRowClick(rowData, row);
                    }
                });
            });
        }

        const actionButtons = this.container.querySelectorAll('.btn-action');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = button.getAttribute('data-action');
                const rowId = button.getAttribute('data-row-id');
                const rowData = this.getRowData(rowId);
                if (this.config.onAction) {
                    this.config.onAction(action, rowData, button);
                }
            });
        });
    }

    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }

        this.filteredData.sort((a, b) => {
            let aVal = a[column];
            let bVal = b[column];

            if (aVal == null) aVal = '';
            if (bVal == null) bVal = '';

            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }

            aVal = String(aVal).toLowerCase();
            bVal = String(bVal).toLowerCase();

            if (this.sortDirection === 'asc') {
                return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            } else {
                return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
            }
        });

        this.currentPage = 1;
        this.render();
    }

    applyFilter() {
        if (!this.filterText) {
            this.filteredData = [...this.config.data];
        } else {
            const searchLower = this.filterText.toLowerCase();
            this.filteredData = this.config.data.filter(row => {
                return this.config.columns.some(col => {
                    const value = String(row[col.key] || '').toLowerCase();
                    return value.includes(searchLower);
                });
            });
        }

        if (this.sortColumn) {
            this.sort(this.sortColumn);
        } else {
            this.render();
        }
    }

    getPaginatedData() {
        const start = this.getStartIndex();
        const end = this.getEndIndex();
        return this.filteredData.slice(start, end);
    }

    getStartIndex() {
        return (this.currentPage - 1) * this.config.itemsPerPage;
    }

    getEndIndex() {
        return Math.min(this.currentPage * this.config.itemsPerPage, this.filteredData.length);
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.config.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }

    getSortIcon(column) {
        if (this.sortColumn !== column) {
            return '<i class="fas fa-sort text-muted ml-1"></i>';
        }
        const icon = this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        return `<i class="fas ${icon} text-primary ml-1"></i>`;
    }

    getRowData(rowId) {
        return this.config.data.find(row => (row.id || row) === rowId) || null;
    }

    updateData(data) {
        this.config.data = data;
        this.filteredData = [...data];
        this.currentPage = 1;
        this.filterText = '';
        const searchInput = document.getElementById(`${this.config.containerId}-search`);
        if (searchInput) {
            searchInput.value = '';
        }
        this.render();
    }

    setLoading(loading) {
        this.config.loading = loading;
        this.render();
    }

    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.render();
    }
}

// ChartWidget Component
class ChartWidget {
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

        if (typeof Chart === 'undefined') {
            console.error('ChartWidget: Chart.js library is not loaded');
            this.renderError('Chart.js library is required');
            return;
        }

        this.render();
    }

    render() {
        if (this.config.loading) {
            this.renderLoading();
            return;
        }

        if (this.config.error) {
            this.renderError();
            return;
        }

        let canvas = this.container.querySelector('canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            this.container.innerHTML = '';
            this.container.appendChild(canvas);
        }

        if (this.chart) {
            this.chart.destroy();
        }

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

        const ctx = canvas.getContext('2d');
        this.chart = new Chart(ctx, {
            type: this.config.type,
            data: this.config.data,
            options: mergedOptions
        });
    }

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

    renderError(message = null) {
        const errorMsg = message || this.config.error || 'Failed to load chart';
        this.container.innerHTML = `
            <div class="alert alert-danger m-0">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Chart Error:</strong> ${errorMsg}
            </div>
        `;
    }

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

    setLoading(loading) {
        this.config.loading = loading;
        if (loading) {
            this.renderLoading();
        } else {
            this.render();
        }
    }

    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.renderError(error);
    }

    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }

    deepMerge(target, source) {
        const output = Object.assign({}, target);
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

    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    }
}

// Main Dashboard Class
class Dashboard {
    constructor() {
        this.statCards = {};
        this.dataTable = null;
        this.charts = {};
        this.apiBaseUrl = '/api/dashboard';
        this.refreshInterval = null;
        this.refreshIntervalTime = 30000;
        
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        console.log('Initializing Dashboard...');
        
        this.initStatCards();
        this.initDataTable();
        this.initCharts();
        this.loadDashboardData();
        this.setupAutoRefresh();
        
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadDashboardData());
        }
    }

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

    initDataTable() {
        const tableContainer = document.getElementById('data-table-container');
        if (!tableContainer) return;

        this.dataTable = new DataTable({
            containerId: 'data-table-container',
            columns: [
                { key: 'id', label: 'ID', sortable: true },
                { key: 'name', label: 'Name', sortable: true },
                { key: 'category', label: 'Category', sortable: true },
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
            },
            onAction: (action, rowData, button) => {
                this.handleTableAction(action, rowData);
            }
        });
    }

    initCharts() {
        const salesChartContainer = document.getElementById('sales-chart');
        if (salesChartContainer) {
            this.charts.sales = new ChartWidget({
                containerId: 'sales-chart',
                type: 'line',
                title: 'Sales Over Time',
                data: { labels: [], datasets: [] },
                options: {
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                },
                loading: true
            });
        }

        const revenueChartContainer = document.getElementById('revenue-chart');
        if (revenueChartContainer) {
            this.charts.revenue = new ChartWidget({
                containerId: 'revenue-chart',
                type: 'bar',
                title: 'Revenue by Category',
                data: { labels: [], datasets: [] },
                options: {
                    plugins: { legend: { display: false } }
                },
                loading: true
            });
        }

        const distributionChartContainer = document.getElementById('distribution-chart');
        if (distributionChartContainer) {
            this.charts.distribution = new ChartWidget({
                containerId: 'distribution-chart',
                type: 'pie',
                title: 'User Distribution',
                data: { labels: [], datasets: [] },
                options: {
                    plugins: { legend: { position: 'right' } }
                },
                loading: true
            });
        }
    }

    async loadDashboardData() {
        try {
            await this.loadStats();
            await this.loadTableData();
            await this.loadChartData();
            console.log('Dashboard data loaded successfully');
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.handleError(error);
        }
    }

    async loadStats() {
        try {
            const response = await axios.get(`${this.apiBaseUrl}/stats`);
            const stats = response.data;

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

    async loadChartData() {
        try {
            const response = await axios.get(`${this.apiBaseUrl}/chart-data`);
            const chartData = response.data;

            if (this.charts.sales && chartData.sales) {
                this.charts.sales.updateData({
                    labels: chartData.sales.labels || [],
                    datasets: chartData.sales.datasets || []
                });
                this.charts.sales.setLoading(false);
            }

            if (this.charts.revenue && chartData.revenue) {
                this.charts.revenue.updateData({
                    labels: chartData.revenue.labels || [],
                    datasets: chartData.revenue.datasets || []
                });
                this.charts.revenue.setLoading(false);
            }

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

    async deleteRow(id) {
        try {
            await axios.delete(`${this.apiBaseUrl}/table-data/${id}`);
            this.loadTableData();
        } catch (error) {
            console.error('Error deleting row:', error);
            alert('Failed to delete item. Please try again.');
        }
    }

    setupAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        this.refreshInterval = setInterval(() => {
            console.log('Auto-refreshing dashboard data...');
            this.loadDashboardData();
        }, this.refreshIntervalTime);
    }

    handleError(error) {
        const errorMessage = error.response?.data?.message || error.message || 'An error occurred';
        
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

        setTimeout(() => {
            errorAlert.remove();
        }, 5000);
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
    }
}

const dashboard = new Dashboard();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    dashboard.destroy();
});
