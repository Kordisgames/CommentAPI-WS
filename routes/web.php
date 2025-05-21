<?php

use Illuminate\Support\Facades\Route;
use App\Models\News;

Route::get('/', function () {
    $news = News::with('comments')->first();
    return view('websocket-demo', ['news' => $news]);
});
