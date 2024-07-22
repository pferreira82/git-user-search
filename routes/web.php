<?php

use App\Http\Controllers\GitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

//Route::get('/search-user', [GitController::class, 'searchUser']);
Route::get('/search-user', [GitController::class, 'showSearchForm'])->name('search-user.form');
Route::post('/search-user', [GitController::class, 'searchUser'])->name('search-user.submit');
Route::post('/load-more-followers', [GitController::class, 'loadMoreFollowers'])->name('load-more-followers');
