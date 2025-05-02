<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAuditController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('common.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    //Dashboard Route.
    Route::get('/dashboard', [DashboardController::class, 'display'])->name('dashboard');

    // Document Route
    Route::get('/document', [DocumentController::class, 'display'])->name('document');
    Route::get('/view-document/{id}', [DocumentController::class, 'view'])->name('view-document');
    Route::get('/download/{filename}', [DocumentController::class, 'download'])->name('download');
    Route::get('/export-document', [DocumentController::class, 'share'])->name('export-document');
    Route::get('/add-document', [DocumentController::class, 'create'])->name('add-document');
    Route::post('/add-document', [DocumentController::class, 'store'])->name('add-document');
    Route::post('/add-document-image', [DocumentController::class, 'uploadImage'])->name('add-document-image');
    Route::post('/remove-document-image', [DocumentController::class, 'removeImage'])->name('remove-document-image');
    Route::get('/edit-document/{id}', [DocumentController::class, 'edit'])->name('edit-document');
    Route::post('update-document', [DocumentController::class, 'update'])->name('update-document');
    Route::delete('/delete-document', [DocumentController::class, 'delete'])->name('delete-document');

    // Document Audit
    Route::get('/document-audit', [DocumentAuditController::class, 'display'])->name('document-audit');

    // Document-type Routes
    Route::get('/document-type', [DocumentTypeController::class, 'view'])->name('document-type');
    Route::get('/add-document-type', [DocumentTypeController::class, 'create'])->name('add-document-type');
    Route::post('/add-document-type', [DocumentTypeController::class, 'store'])->name('add-document-type');
    Route::get('/edit-document-type/{id}', [DocumentTypeController::class, 'edit'])->name('edit-document-type');
    Route::patch('/update-document-type/{id}', [DocumentTypeController::class, 'update'])->name('update-document-type');
    Route::delete('/delete-document-type/{id}', [DocumentTypeController::class, 'delete'])->name('delete-document-type');


    // Group Routes
    Route::get('/groups', [GroupController::class, 'display'])->name('groups');
    Route::get('/view-group/{id}', [GroupController::class, 'view'])->name('view-group');
    Route::get('/add-group', [GroupController::class, 'create'])->name('add-group');
    Route::post('/add-group', [GroupController::class, 'store'])->name('add-group');
    Route::get('/edit-group/{id}', [GroupController::class, 'edit'])->name('edit-group');
    Route::patch('/update-group/{id}', [GroupController::class, 'update'])->name('update-group');
    Route::delete('/delete-group/{id}', [GroupController::class, 'delete'])->name('delete-group');

    // User Routes
    Route::get('/users', [UserController::class, 'display'])->name('users');
    Route::get('/view-user/{id}', [UserController::class, 'view'])->name('view-user');
    Route::get('/add-user', [UserController::class, 'create'])->name('add-user');
    Route::post('/add-user', [UserController::class, 'store'])->name('add-user');
    Route::get('/edit-user/{id}', [UserController::class, 'edit'])->name('edit-user');
    Route::patch('/update-user/{id}', [UserController::class, 'update'])->name('update-user');
    Route::delete('/delete-user/{id}', [UserController::class, 'delete'])->name('delete-user');

    // Notification Routes
    Route::get('/setting', [NotificationController::class, 'display'])->name('setting');
    Route::post('/setting', [NotificationController::class, 'store'])->name('setting');
});

Route::get('document-types/{id}', [DocumentTypeController::class, 'getFields'])->name('document-types');


require __DIR__ . '/auth.php';
