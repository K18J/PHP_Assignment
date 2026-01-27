<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// The framework already applies the /api prefix and api middleware to this file.
if (file_exists(base_path('Modules/Cms/Routes/api.php'))) {
    require base_path('Modules/Cms/Routes/api.php');
}

