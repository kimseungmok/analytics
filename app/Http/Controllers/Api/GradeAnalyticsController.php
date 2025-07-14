<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;;
use Carbon\Carbon;

class GradeAnalyticsController extends Controller
{
  public function getKpiComparison(Request $request)
  {
    // 1. 요청 파라미터 유효성 검사
    $request->validate([
      'current_date' => 'required|date_format:Y-m-d',
      'previous_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $currentDate = Carbon::parse($request->input('current_date'));
    $previousDate = Carbon::parse($request->input('previous_date'));

    // 2. 현재 날짜의 모든 KPI 계산 (DB에서 직접 집계)
    // 이 함수는 이제 해당 날짜의 '전월 대비' 비율까지 모두 계산하여 반환합니다.
    $currentKpis = $this->calculateAllKpisForDate($currentDate);

    // 3. 이전 날짜의 모든 KPI 계산 (DB에서 직접 집계)
    $previousKpis = $this->calculateAllKpisForDate($previousDate);

    // 4. 전월 대비 변화 계산
    // 이제 각 KPI 값은 calculateAllKpisForDate에서 이미 계산되었으므로,
    // 여기서는 단순히 두 날짜의 KPI 값을 비교하여 변화량을 계산합니다.
    $activeUsersChange = $currentKpis['activeUsers'] - $previousKpis['activeUsers'];
    $promotionRateChange = $currentKpis['promotionRate'] - $previousKpis['promotionRate'];
    $retentionRateChange = $currentKpis['retentionRate'] - $previousKpis['retentionRate'];
    $churnRateChange = $currentKpis['churnRate'] - $previousKpis['churnRate'];
    $churnedUsersCumulativeChange = $currentKpis['churnedUsersCumulative'] - $previousKpis['churnedUsersCumulative'];

    // 5. 응답 데이터 구성
    $kpiData = [
      [
        'metric' => 'アクティブユーザー数（当月）',
        'value' => number_format($currentKpis['activeUsers']) . '人',
        'change' => ($activeUsersChange >= 0 ? '+' : '') . number_format($activeUsersChange) . '人',
        'note' => '直近365日内1回以上',
        'changeType' => $activeUsersChange >= 0 ? 'positive' : 'negative'
      ],
      [
        'metric' => '昇格率',
        'value' => sprintf('%.1f%%', $currentKpis['promotionRate']),
        'change' => ($promotionRateChange >= 0 ? '+' : '') . sprintf('%.1fpt', $promotionRateChange),
        'note' => '全体ユーザーの昇格率',
        'changeType' => $promotionRateChange >= 0 ? 'positive' : 'negative'
      ],
      [
        'metric' => '維持率',
        'value' => sprintf('%.1f%%', $currentKpis['retentionRate']),
        'change' => ($retentionRateChange >= 0 ? '+' : '') . sprintf('%.1fpt', $retentionRateChange),
        'note' => '同一セグメント維持',
        'changeType' => $retentionRateChange >= 0 ? 'positive' : 'negative'
      ],
      [
        'metric' => '流出率',
        'value' => sprintf('%.1f%%', $currentKpis['churnRate']),
        'change' => ($churnRateChange >= 0 ? '+' : '') . sprintf('%.1fpt', $churnRateChange),
        'note' => '全体ユーザーの降格率',
        'changeType' => $churnRateChange >= 0 ? 'negative' : 'positive' // 유출률은 증가하면 부정적
      ],
      [
        'metric' => '離反ユーザー数（累積）',
        'value' => number_format($currentKpis['churnedUsersCumulative']) . '人',
        'change' => ($churnedUsersCumulativeChange >= 0 ? '+' : '') . number_format($churnedUsersCumulativeChange) . '人',
        'note' => '直近730日利用なし',
        'changeType' => $churnedUsersCumulativeChange >= 0 ? 'positive' : 'negative'
      ],
    ];

    // 최종 JSON 응답 반환
    return response()->json([
      'status' => 'success',
      'data' => $kpiData,
      'current_date' => $currentDate->format('Y-m-d'),
      'previous_date' => $previousDate->format('Y-m-d'),
    ]);
  }



















































  private function calculateAllKpisForDate(Carbon $date)
  {
    // 1. 해당 날짜의 기본 KPI (활성 사용자, 이탈 사용자) 계산
    $baseKpis = DB::table('T_GRADE_SNAPSHOT')
      ->whereDate('SNAPSHOT_DATE', $date)
      ->select(
        //DB::raw('COUNT(CASE WHEN SEGMENT_ID IN (1, 2, 3) THEN 1 END) AS active_users'),
        DB::raw('COUNT(CASE WHEN SEGMENT_ID IN (1, 2, 3) THEN 1 END) AS active_users'),
        DB::raw('COUNT(CASE WHEN SEGMENT_ID = 5 THEN 1 END) AS churned_users_cumulative')
      )
      ->first();

    $activeUsers = $baseKpis->active_users ?? 0;
    $churnedUsersCumulative = $baseKpis->churned_users_cumulative ?? 0;

    // 2. 해당 날짜와 그 전월의 스냅샷을 비교하여 승급/유지/강등 사용자 수 계산
    $previousMonthDate = $date->copy()->subMonth();

    $gradeChanges = DB::table('T_GRADE_SNAPSHOT AS current_snap')
      ->join('T_GRADE_SNAPSHOT AS prev_snap', function ($join) use ($date, $previousMonthDate) {
        $join->on('current_snap.TICKET', '=', 'prev_snap.TICKET')
          ->whereDate('current_snap.SNAPSHOT_DATE', $date)
          ->whereDate('prev_snap.SNAPSHOT_DATE', $previousMonthDate);
      })
      ->select(
        DB::raw('SUM(CASE WHEN current_snap.SEGMENT_ID < prev_snap.SEGMENT_ID THEN 1 ELSE 0 END) AS promoted_users'),
        DB::raw('SUM(CASE WHEN current_snap.SEGMENT_ID = prev_snap.SEGMENT_ID THEN 1 ELSE 0 END) AS retained_users'),
        DB::raw('SUM(CASE WHEN current_snap.SEGMENT_ID > prev_snap.SEGMENT_ID THEN 1 ELSE 0 END) AS demoted_users'),
        DB::raw('COUNT(current_snap.TICKET) AS total_users_compared')
      )
      ->first();

    // 3. 승급/유지/강등 비율 계산
    $totalUsersCompared = $gradeChanges->total_users_compared ?? 0;
    $promotedUsers = $gradeChanges->promoted_users ?? 0;
    $retainedUsers = $gradeChanges->retained_users ?? 0;
    $demotedUsers = $gradeChanges->demoted_users ?? 0;

    $promotionRate = $totalUsersCompared > 0 ? ($promotedUsers / $totalUsersCompared) * 100 : 0;
    $retentionRate = $totalUsersCompared > 0 ? ($retainedUsers / $totalUsersCompared) * 100 : 0;
    $churnRate = $totalUsersCompared > 0 ? ($demotedUsers / $totalUsersCompared) * 100 : 0;

    // 4. 모든 KPI 값을 배열로 반환
    return [
      'activeUsers' => $activeUsers,
      'churnedUsersCumulative' => $churnedUsersCumulative,
      'promotionRate' => $promotionRate,
      'retentionRate' => $retentionRate,
      'churnRate' => $churnRate,
    ];
  }























































  

  public function getSegmentMigrationMatrix(Request $request){
    $request->validate([
      'current_date' => 'required|date_format:Y-m-d',
      'previous_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $currentDate = Carbon::parse($request->input('current_date'));
    $previousDate = Carbon::parse($request->input('previous_date'));

    $relevantSegmentIds = [1, 2, 3, 4, 5];

    $segments = DB::table('SEGMENT_MASTER')
      ->whereIn('SEGMENT_ID', $relevantSegmentIds)
      ->orderBy('SEGMENT_ID')
      ->get(['SEGMENT_ID', 'SEGMENT_NAME']);

    //$segmentNames = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentNamesRaw = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $translationMap = [
      'core'    => 'コア',
      'middle'  => 'ミドル',
      'light'   => 'ライト',
      'dormant' => '休眠',
      'churned' => '離反',
    ];

    $segmentIdsOrdered = $segments->pluck('SEGMENT_ID')->toArray();

    $translatedSegmentHeaders = [];
    foreach ($segmentIdsOrdered as $id) {
        $name = $segmentNamesRaw[$id] ?? null;
        if ($name) {
            $translatedSegmentHeaders[] = [
                'id' => $id,
                'name' => $translationMap[strtolower($name)] ?? $name,
            ];
        }
    }

    $matrix = [];
    foreach ($segmentIdsOrdered as $prevId) {
      $row = [];
      foreach ($segmentIdsOrdered as $currId) {
        $row[$currId] = 0;
      }
      $matrix[$prevId] = $row;
    }

    $transitions = DB::table('T_GRADE_SNAPSHOT AS current_snap')
      ->join('T_GRADE_SNAPSHOT AS prev_snap', function ($join) use ($currentDate, $previousDate) {
        $join->on('current_snap.TICKET', '=', 'prev_snap.TICKET')
          ->whereDate('current_snap.SNAPSHOT_DATE', $currentDate)
          ->whereDate('prev_snap.SNAPSHOT_DATE', $previousDate);
      })
      ->whereIn('prev_snap.SEGMENT_ID', $relevantSegmentIds)
      ->whereIn('current_snap.SEGMENT_ID', $relevantSegmentIds)
      ->select(
        'prev_snap.SEGMENT_ID AS previous_segment_id',
        'current_snap.SEGMENT_ID AS current_segment_id',
        DB::raw('COUNT(current_snap.TICKET) AS user_count')
      )
      ->groupBy('prev_snap.SEGMENT_ID','current_snap.SEGMENT_ID')
      ->get();

    foreach ($transitions as $transition){
      if(isset($matrix[$transition->previous_segment_id][$transition->current_segment_id])){
        $matrix[$transition->previous_segment_id][$transition->current_segment_id] = $transition->user_count;
      }
    }

    $formattedMatrix = [];
    foreach ($segmentIdsOrdered as $prevId) {
      $row=[];
      foreach($segmentIdsOrdered as $currId) {
        $row[] = $matrix[$prevId][$currId];
      }
      $formattedMatrix[] = $row;
    }

    return response()->json([
      'status' => 'success',
      'previous_date' => $previousDate->format('Y-m-d'),
      'current_date' => $currentDate->format('Y-m-d'),
      'row_headers' => $translatedSegmentHeaders,
      'col_headers' => $translatedSegmentHeaders,
      'matrix_data' => $formattedMatrix,
    ]);
  }

}
