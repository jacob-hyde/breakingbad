<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use BreakingBad\Data\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
Route::get('/users', function (Request $request) {
    return factory('BreakingBad\Data\Models\User', 10)->make();
});

Route::get('/characters', 'CharacterController@index')->name('api.characters.index');

Route::middleware('auth:airlock')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:airlock')->post('/logout', function (Request $request) {
    $request->user()->tokens()->delete();

    return response('Loggedout', 200);
});

Route::post('/airlock/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken($request->device_name)->plainTextToken;

    $response = [
        'user' => $user,
        'token' => $token,
    ];

    return response($response, 201);
});

Route::get('/{any}', function (Request $request) {
    return response()->json(['error' => 'Whoops wrong endpoint!'], 404);
})->where('any', '.*');
