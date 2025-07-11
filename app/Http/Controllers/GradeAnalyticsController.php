<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeAnalyticsController extends Controller
{
  public function compare(Request $request)
  {
    $baseDate = $request->input('base_date');
    $targetDate = $request->input('target_date');

    $activeUsers = DB::table('T_MEMBER_INFO')
    ->where('IS_ACTIVE', 1)
    ->count();

    $baseGrades = DB::table('T_USER_GRADE_SNAPSHOT')
    ->where('SNAPSHOT_DATE', $baseDate)
    ->pluck('SEGMENT_ID', 'TICKET');

    $targetGrades = DB::table('T_SER_GRADE_SNAPSHOT')
    ->where('SNAPSHOT_DATE', $targetDate)
    ->pluck('SEGMENT_ID', 'TICKET');

    $promoted = 0;
    $retained = 0;
    $demoted = 0;

    foreach($baseGrades as $ticket => $baseGrade){
      $targetGrade = $targetGrades[$ticket] ?? null;
      if(!$targetGrade) continue;

      if($targetGrade > $baseGrade) $promoted++;
      elseif($targetGrade == $baseGrade) $retained++;
      elseif($targetGrade < $baseGrade) $demoted++;
    }

    return response()->json([
      'active_users' => $activeUsers,
      'promotion_rate' => round(($promoted / count($baseGrades)) * 100, 2),
      'retention_rate' => round(($retained / count($baseGrades)) * 100, 2),
      'demotion_rate' => round(($demoted / count($baseGrades)) * 100, 2),
      'base_grade_counts' => array_count_values($baseGrades->toArray()),
      'target_grade_counts' => array_count_values($targetGrades->toArray()),
    ]);
  }
}
