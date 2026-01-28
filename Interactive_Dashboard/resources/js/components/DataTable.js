/**
 * DataTable Component
 * 
 * A feature-rich data table component with sorting, filtering, and pagination.
 * Supports loading states, error handling, and custom row actions.
 */
class DataTable {
    /**
     * @param {Object} config - Configuration object
     * @param {string} config.containerId - ID of the container element
     * @param {Array} config.columns - Column definitions [{key, label, sortable, render}]
     * @param {Array} config.data - Array of data objects
     * @param {number} config.itemsPerPage - Items per page (default: 10)
     * @param {boolean} config.searchable - Enable search/filter (default: true)
     * @param {Function} config.onRowClick - Callback for row click events
     * @param {Function} config.onAction - Callback for action button clicks
     */
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

    /**
     * Render the complete table
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

    /**
     * Render search/filter input
     */
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

    /**
     * Render the table
     */
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

    /**
     * Render a table row
     */
    renderRow(row, index) {
        const rowClass = this.config.onRowClick ? 'clickable-row' : '';
        const rowId = `row-${index}`;

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

    /**
     * Render a table cell
     */
    renderCell(row, column) {
        if (column.render && typeof column.render === 'function') {
            return column.render(row[column.key], row);
        }
        return row[column.key] || '-';
    }

    /**
     * Render pagination controls
     */
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

    /**
     * Render loading state
     */
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

    /**
     * Render error state
     */
    renderError() {
        this.container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> ${this.config.error}
            </div>
        `;
    }

    /**
     * Attach event listeners
     */
    attachEvents() {
        // Search input
        const searchInput = document.getElementById(`${this.config.containerId}-search`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterText = e.target.value;
                this.currentPage = 1;
                this.applyFilter();
            });
        }

        // Sortable column headers
        const sortableHeaders = this.container.querySelectorAll('th.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-column');
                this.sort(column);
            });
        });

        // Pagination
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

        // Row click
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

        // Action buttons
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

    /**
     * Sort data by column
     */
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

            // Handle null/undefined
            if (aVal == null) aVal = '';
            if (bVal == null) bVal = '';

            // Handle numbers
            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Handle strings
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

    /**
     * Apply search filter
     */
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

        // Re-apply sorting if active
        if (this.sortColumn) {
            this.sort(this.sortColumn);
        } else {
            this.render();
        }
    }

    /**
     * Get paginated data
     */
    getPaginatedData() {
        const start = this.getStartIndex();
        const end = this.getEndIndex();
        return this.filteredData.slice(start, end);
    }

    /**
     * Get start index for current page
     */
    getStartIndex() {
        return (this.currentPage - 1) * this.config.itemsPerPage;
    }

    /**
     * Get end index for current page
     */
    getEndIndex() {
        return Math.min(this.currentPage * this.config.itemsPerPage, this.filteredData.length);
    }

    /**
     * Go to specific page
     */
    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.config.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }

    /**
     * Get sort icon for column
     */
    getSortIcon(column) {
        if (this.sortColumn !== column) {
            return '<i class="fas fa-sort text-muted ml-1"></i>';
        }
        const icon = this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        return `<i class="fas ${icon} text-primary ml-1"></i>`;
    }

    /**
     * Get row data by ID
     */
    getRowData(rowId) {
        return this.config.data.find(row => (row.id || row) === rowId) || null;
    }

    /**
     * Update table data
     */
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

    /**
     * Set loading state
     */
    setLoading(loading) {
        this.config.loading = loading;
        this.render();
    }

    /**
     * Set error state
     */
    setError(error) {
        this.config.error = error;
        this.config.loading = false;
        this.render();
    }
}

// Export for ES6 modules
export default DataTable;
