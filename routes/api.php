<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\GradeAnalyticsController;
use App\Http\Controllers\Api\GradeAnalyticsController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\KpiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/grade-compare',[GradeAnalyticsController::class, 'compare']);
Route::post('/kpi-comparison', [GradeAnalyticsController::class, 'getKpiComparison']);
Route::post('/segment-migration', [GradeAnalyticsController::class, 'getSegmentMigrationMatrix']);
Route::post('/segment-composition', [GradeAnalyticsController::class, 'getSegmentComposition']);
Route::post('/segment-transition', [GradeAnalyticsController::class, 'getSegmentSankeyData']);
Route::post('/segment-summary', [GradeAnalyticsController::class, 'getSegmentSummary']);

Route::get('/branches', [BranchController::class, 'getBranchList']);

Route::get('/kpi/total-sales', [KpiController::class, 'totalSales']);
//Route::get('/chart/sales-trend', [ChartController::class, 'salesTrend']);

//Route::get('/analytics/segment-transition', [GradeAnalyticsController::class, 'getSegmentSankeyData']);

