<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.index');
});
Route::get('/list', function () {
    return view('website.list');
});
Route::get('/details', function () {
    return view('website.details');
});
Route::get('/add', function () {
    return view('website.addNew');
});
Route::get('/dashboard', function () {
    return view('userPanel.index');
});
