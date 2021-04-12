<?php
Route::get('sms/list', 'SMS\SmsController@list');
Route::post('sms/reply', 'SMS\SmsController@reply');
Route::resource('sms', 'SMS\SmsController');
