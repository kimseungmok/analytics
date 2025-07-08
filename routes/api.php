<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SegmentDistributionController;
use App\Http\Controllers\GradeChangeTrendController;
use App\Http\Controllers\GradeTransitionCompareController;
use App\Http\Controllers\GradeTransitionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/segment-distribution', [SegmentDistributionController::class, 'index']);
Route::get('/grade-change-trend', [GradeChangeTrendController::class, 'index']);
Route::get('/grade-transitions',[GradeTransitionController::class, 'index']);
Route::get('/grade-transitions/compare',[GradeTransitionController::class, 'compare']);
Route::get('/grade-transition-compare', [GradeTransitionCompareController::class, 'compare']);
Route::get('/grade-transition-compare2', [GradeTransitionCompareController::class, 'compare2']);
Route::get('/grade-transition-compare3', [GradeTransitionController::class, 'compare3']);
Route::get('/grade-transition-compare4', [GradeTransitionController::class, 'compare4']);
Route::get('/grade-transition-sankey', [GradeTransitionController::class, 'sankey']);