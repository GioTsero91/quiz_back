<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::prefix('api')->group(function () {
    Route::get('quiz', 'QuizController@startQuiz');
    Route::get('quiz/answer/{opt}', 'QuizController@answerQuestion');
    Route::get('quiz/mode/{mode}', 'QuizController@changeMode');
    Route::get('quiz/restart', 'QuizController@restartQuiz');
});

