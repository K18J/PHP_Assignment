<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interactive Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-pie mr-2"></i>
                Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-light btn-sm ml-2" id="refresh-dashboard">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard Container -->
    <div class="container-fluid dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Interactive Dashboard</h1>
            <p class="subtitle">Real-time data visualization and analytics</p>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="dashboard-tab" data-toggle="tab" href="#dashboard-pane" role="tab">
                    <i class="fas fa-chart-pie mr-1"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="assets-tab" data-toggle="tab" href="#assets-pane" role="tab">
                    <i class="fas fa-boxes mr-1"></i> Assets
                </a>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            <!-- Dashboard Tab Pane -->
            <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel">
        <!-- Stat Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div id="stat-total-users"></div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div id="stat-total-orders"></div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div id="stat-revenue"></div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div id="stat-growth"></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Sales Chart -->
            <div class="col-lg-8 mb-3">
                <div class="chart-container">
                    <div id="sales-chart"></div>
                </div>
            </div>
            
            <!-- Distribution Chart -->
            <div class="col-lg-4 mb-3">
                <div class="chart-container">
                    <div id="distribution-chart"></div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-container">
                    <div id="revenue-chart"></div>
                </div>
            </div>
        </div>

        <!-- Data Table Row -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-table mr-2"></i>
                            Data Table
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="data-table-container"></div>
                    </div>
                </div>
            </div>
        </div>
            </div>
            <!-- /Dashboard Tab Pane -->

            <!-- Assets Tab Pane -->
            <div class="tab-pane fade" id="assets-pane" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-boxes mr-2"></i>Asset Management</h5>
                        <button type="button" class="btn btn-light btn-sm" id="btn-add-asset">
                            <i class="fas fa-plus"></i> Add Asset
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="assets-alert"></div>
                        <div id="assets-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2 text-muted">Loading assets...</p>
                        </div>
                        <div id="assets-table-container" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Serial #</th>
                                            <th>Category</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                            <th>Purchase Date</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="assets-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Assets Tab Pane -->
        </div>
    </div>

    <!-- Asset Form Modal -->
    <div class="modal fade" id="assetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assetModalTitle">Add Asset</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="assetForm">
                        <input type="hidden" id="asset_id" name="id">
                        <div class="form-group">
                            <label for="asset_name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="asset_name" name="name" required maxlength="255" placeholder="Asset name">
                        </div>
                        <div class="form-group">
                            <label for="asset_serial_number">Serial Number</label>
                            <input type="text" class="form-control" id="asset_serial_number" name="serial_number" maxlength="100" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label for="asset_category_id">Category <span class="text-danger">*</span></label>
                            <select class="form-control" id="asset_category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="asset_value">Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="asset_value" name="value" required min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label for="asset_status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="asset_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="asset_purchase_date">Purchase Date</label>
                            <input type="date" class="form-control" id="asset_purchase_date" name="purchase_date">
                        </div>
                        <div class="form-group">
                            <label for="asset_description">Description</label>
                            <textarea class="form-control" id="asset_description" name="description" rows="2" placeholder="Optional"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-save-asset">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Set up Axios CSRF token -->
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
    
    <!-- Custom Dashboard JavaScript -->
    <script src="{{ mix('js/dashboard.js') }}"></script>
    <!-- Assets CRUD -->
    <script>
    (function() {
        const API_BASE = '/api/assets';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showAlert(message, type) {
            const el = document.getElementById('assets-alert');
            el.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>';
            setTimeout(function() { el.innerHTML = ''; }, 5000);
        }

        function loadCategories() {
            return axios.get(API_BASE + '/categories').then(r => r.data);
        }

        function loadAssets() {
            document.getElementById('assets-loading').classList.remove('d-none');
            document.getElementById('assets-table-container').classList.add('d-none');
            return axios.get(API_BASE).then(r => {
                document.getElementById('assets-loading').classList.add('d-none');
                document.getElementById('assets-table-container').classList.remove('d-none');
                return r.data;
            }).catch(err => {
                document.getElementById('assets-loading').classList.add('d-none');
                showAlert('Failed to load assets.', 'danger');
                return [];
            });
        }

        function renderAssets(assets) {
            const tbody = document.getElementById('assets-tbody');
            if (!assets || assets.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No assets found. Click "Add Asset" to create one.</td></tr>';
                return;
            }
            tbody.innerHTML = assets.map(a => `
                <tr>
                    <td>${escapeHtml(a.name)}</td>
                    <td>${escapeHtml(a.serial_number || '-')}</td>
                    <td>${escapeHtml(a.category_name || '')}</td>
                    <td>${formatCurrency(a.value)}</td>
                    <td><span class="badge badge-${statusBadge(a.status)}">${a.status}</span></td>
                    <td>${a.purchase_date || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-edit-asset" data-id="${a.id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete-asset" data-id="${a.id}" data-name="${escapeHtml(a.name)}" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
            bindAssetRowEvents();
        }

        function escapeHtml(s) {
            if (!s) return '';
            const div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        function formatCurrency(n) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);
        }

        function statusBadge(s) {
            const map = { active: 'success', inactive: 'secondary', maintenance: 'warning', retired: 'danger' };
            return map[s] || 'secondary';
        }

        function bindAssetRowEvents() {
            document.querySelectorAll('.btn-edit-asset').forEach(btn => {
                btn.addEventListener('click', function() { openEditModal(parseInt(this.getAttribute('data-id'))); });
            });
            document.querySelectorAll('.btn-delete-asset').forEach(btn => {
                btn.addEventListener('click', function() {
                    deleteAsset(parseInt(this.getAttribute('data-id')), this.getAttribute('data-name'));
                });
            });
        }

        function fillCategoryDropdown(selectedId) {
            const sel = document.getElementById('asset_category_id');
            sel.innerHTML = '<option value="">-- Select Category --</option>';
            return loadCategories().then(cats => {
                cats.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (selectedId && parseInt(c.id) === parseInt(selectedId)) opt.selected = true;
                    sel.appendChild(opt);
                });
            });
        }

        function openAddModal() {
            document.getElementById('assetModalTitle').textContent = 'Add Asset';
            document.getElementById('asset_id').value = '';
            document.getElementById('asset_name').value = '';
            document.getElementById('asset_serial_number').value = '';
            document.getElementById('asset_value').value = '';
            document.getElementById('asset_status').value = 'active';
            document.getElementById('asset_purchase_date').value = '';
            document.getElementById('asset_description').value = '';
            fillCategoryDropdown(null).then(() => $('#assetModal').modal('show'));
        }

        function openEditModal(id) {
            axios.get(API_BASE + '/' + id).then(r => {
                const a = r.data;
                document.getElementById('assetModalTitle').textContent = 'Edit Asset';
                document.getElementById('asset_id').value = a.id;
                document.getElementById('asset_name').value = a.name;
                document.getElementById('asset_serial_number').value = a.serial_number || '';
                document.getElementById('asset_value').value = a.value;
                document.getElementById('asset_status').value = a.status;
                document.getElementById('asset_purchase_date').value = a.purchase_date || '';
                document.getElementById('asset_description').value = a.description || '';
                fillCategoryDropdown(a.category_id).then(() => $('#assetModal').modal('show'));
            }).catch(() => showAlert('Failed to load asset.', 'danger'));
        }

        function saveAsset() {
            const id = document.getElementById('asset_id').value;
            const data = {
                name: document.getElementById('asset_name').value.trim(),
                serial_number: document.getElementById('asset_serial_number').value.trim() || null,
                category_id: parseInt(document.getElementById('asset_category_id').value),
                value: parseFloat(document.getElementById('asset_value').value) || 0,
                status: document.getElementById('asset_status').value,
                purchase_date: document.getElementById('asset_purchase_date').value || null,
                description: document.getElementById('asset_description').value.trim() || null
            };
            const promise = id ? axios.put(API_BASE + '/' + id, data) : axios.post(API_BASE, data);
            promise.then(() => {
                $('#assetModal').modal('hide');
                showAlert(id ? 'Asset updated successfully.' : 'Asset created successfully.', 'success');
                loadAssets().then(renderAssets);
            }).catch(err => {
                const msg = err.response && err.response.data && err.response.data.errors
                    ? Object.values(err.response.data.errors).flat().join(' ')
                    : (err.response && err.response.data && err.response.data.message) || 'Failed to save asset.';
                showAlert(msg, 'danger');
            });
        }

        function deleteAsset(id, name) {
            if (!confirm('Delete asset "' + name + '"?')) return;
            axios.delete(API_BASE + '/' + id).then(() => {
                showAlert('Asset deleted.', 'success');
                loadAssets().then(renderAssets);
            }).catch(() => showAlert('Failed to delete asset.', 'danger'));
        }

        document.getElementById('btn-add-asset').addEventListener('click', openAddModal);
        document.getElementById('btn-save-asset').addEventListener('click', saveAsset);

        document.getElementById('assets-tab').addEventListener('shown.bs.tab', function() {
            loadCategories();
            loadAssets().then(renderAssets);
        });
    })();
    </script>
</body>
</html>
