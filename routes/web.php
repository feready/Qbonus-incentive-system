<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\BonusSubmissionController;


// PUBLIC ROUTES

Route::get('/', function () {
    return view('welcome');
});


// AUTHENTICATED ROUTES

Route::middleware(['auth'])->group(function () {

    // UPLOAD & VALIDASI BONUS

    Route::get('/upload', [UploadController::class, 'index'])
        ->name('upload.index');

    Route::post('/upload', [UploadController::class, 'upload'])
        ->name('upload.process');


    // DOWNLOAD TEMPLATE EXCEL

    Route::get('/download-template', function () {
        return Storage::download(
            'templates/templateqbonusv1.xlsx',
            'QBonus_Template_v1.xlsx'
        );
    })->name('template.download');

// SUBMISSION SUMMARY

Route::get(
    '/bonus-submissions',
    [BonusSubmissionController::class, 'index']
)->name('bonus-submissions.index');

// EXPORT SUBMISSION KE EXCEL

Route::get(
    '/bonus-submissions/export/excel',
    [BonusSubmissionController::class, 'exportExcel']
)->name('bonus-submissions.export.excel');


// DETAIL SUBMISSION

Route::get(
    '/bonus-submissions/{submission}',
    [BonusSubmissionController::class, 'show']
)->name('bonus-submissions.show');

    // APPROVE PER KARYAWAN

    Route::post(
        '/bonus-submission-items/{item}/approve',
        [BonusSubmissionController::class, 'approve']
    )->name('bonus-submission-items.approve');


    // APPEAL PER KARYAWAN

    Route::post(
        '/bonus-submission-items/{item}/appeal',
        [BonusSubmissionController::class, 'appeal']
    )->name('bonus-submission-items.appeal');


    // DIVISIONS

    Route::resource('divisions', DivisionController::class);


    // EMPLOYEES

    Route::resource('employees', EmployeeController::class);


    // USER PROFILE

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});


// DASHBOARD

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__.'/auth.php';
