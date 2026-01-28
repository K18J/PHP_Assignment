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
</body>
</html>
