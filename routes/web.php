<?php

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

Route::get('/', 'SentimenAnalysisController@index');
Route::get('/traning', 'SentimenAnalysisController@Traning');
Route::get('/k-means', 'SentimenAnalysisController@IndexKmeans');
Route::post('/k-means', 'SentimenAnalysisController@TambahDataKmeans');
Route::get('/knn', 'SentimenAnalysisController@Knn');
Route::post('/knn', 'SentimenAnalysisController@KnnProccess');
Route::get('/evaluasi', 'SentimenAnalysisController@Evaluasi');