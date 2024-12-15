<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [ImageController::class, 'index'])->name('images.index');
Route::post('/upload', [ImageController::class, 'upload'])->name('images.upload');
Route::delete('/delete/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
Route::get('/images/download/{id}', [ImageController::class, 'download'])->name('images.download');

Route::post('/images/bulk-delete', [ImageController::class, 'bulkDelete'])->name('images.bulkDelete');
Route::post('/images/bulk-download', [ImageController::class, 'bulkDownload'])->name('images.bulkDownload');

Route::get('/images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit'); // Show transformation form
Route::post('/images/{id}/transform', [ImageController::class, 'transform'])->name('images.transform');




Route::get('/test-flask', [ImageController::class, 'testFlaskAPI']);


Route::get('/images/{id}/descriptors', [App\Http\Controllers\ImageController::class, 'showDescriptors'])->name('images.showDescriptors');


Route::get('/images/search/{id}', [ImageController::class, 'search'])->name('images.search');
Route::get('/images/simple-search/{id}', [ImageController::class, 'simpleSearch'])->name('images.simpleSearch');
Route::get('/images/relevance-search/{id}', [ImageController::class, 'relevanceSearch'])->name('images.relevanceSearch');
