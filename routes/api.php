<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\GradeAnalyticsController;
use App\Http\Controllers\Api\GradeAnalyticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/grade-compare',[GradeAnalyticsController::class, 'compare']);
Route::get('/kpi-comparison', [GradeAnalyticsController::class, 'getKpiComparison']);
Route::get('/segment-migration', [GradeAnalyticsController::class, 'getSegmentMigrationMatrix']);
Route::get('/segment-composition', [GradeAnalyticsController::class, 'getSegmentComposition']);