<?php

use App\Http\Controllers\API\Agent\AgentController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Student\StudentController;
use App\Models\Agent;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/check-rto/{org_id}', 'Auth\LoginController@check_rto');


Route::post('login',[LoginController::class,'login']);
Route::delete('logout',[LoginController::class,'logout']);

Route::middleware(['auth:sanctum'])->group(function(){
    // default fetching 
    Route::get('defaults/all',[AgentController::class,'defaults']);

    Route::get('profile/',[AgentController::class,'profile']);
    Route::patch('/profile/update/{id}',[ AgentController::class, 'update' ]);

    /* Agent attachment */
    Route::get('profile/Attachment/fetch',[ AgentController::class, 'fetchAllAttachment']);
    Route::get('profile/Attachment/fetch/{id}',[ AgentController::class, 'fetchAttachment']);
    Route::post('profile/Attachment/save',[ AgentController::class, 'saveAttachment']);


    Route::get('/student/{student}',[ StudentController::class, 'show' ]);
    Route::get('/students/{user}',[ StudentController::class, 'index' ]);
});
