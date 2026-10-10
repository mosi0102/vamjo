<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.index');
});
Route::get('/ads', function () {
    return view('website.list');
});
Route::view('/ads/{id}', 'website.details');   // بعداً: کنترلر + مدل

Route::get('/add', function () {
    return view('website.addNew');
});
Route::get('/userPanel', function () {
    return view('userPanel.index');
});
Route::get('/userPanel/requests', function () {
    return view('userPanel.requests.index');
});
