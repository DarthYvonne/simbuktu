<?php

use App\Http\Controllers\Api\CrowdController;
use Illuminate\Support\Facades\Route;

/*
| Crowd API — registered under the "/api/crowd" prefix with the
| VerifyCrowdApiToken middleware in bootstrap/app.php (withRouting `then`).
| Routes here are relative to that prefix.
*/

Route::get('/populations', [CrowdController::class, 'populations']);

Route::post('/courses/{course}/posts', [CrowdController::class, 'post']);
Route::get('/courses/{course}/reactions', [CrowdController::class, 'reactions']);
Route::get('/courses/{course}/personas', [CrowdController::class, 'personas']);

Route::get('/personas/{id}/avatar', [CrowdController::class, 'avatar']);
