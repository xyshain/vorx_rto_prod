<?php

/*
|--------------------------------------------------------------------------
| Reports Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('reports/list-generator', 'Reports\ReportsController@list_generator');

// For Generate Student List Module
Route::get('reports/student-list-generator', 'Reports\ReportsController@index')->name('reports.generate-list');
Route::post('reports/generate-list', 'Reports\ReportsController@student_list');
Route::post('/reports/export-excel-list', 'Reports\ReportsController@list_generator');
Route::get('/reports/download/{type}/{file}', 'Reports\ReportsController@download');
Route::get('/reports/download/{type}/{file}/{rename}', 'Reports\ReportsController@download');
Route::get('reports/student-list', 'Reports\ReportsController@student_list');

// send progress report
Route::post('/report/send/course-progress', 'Reports\ReportsController@send_course_progress');
