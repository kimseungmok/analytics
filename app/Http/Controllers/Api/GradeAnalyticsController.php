<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;;

use Carbon\Carbon;
use Database\Factories\UserFactory;
 use Illuminate\Support\Facades\Log;

class GradeAnalyticsController extends Controller
{
  private $relevantSegmentIds = [1, 2, 3, 4, 5];

  private $translationMap = [
    'core' => 'コア',
    'middle' => 'ミドル',
    'light' => 'ライト',
    'dormant' => '休眠',
    'churned' => '離反',
  ];

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

























































  public function getSegmentMigrationMatrix(Request $request)
  {
    $request->validate([
      'current_date' => 'required|date_format:Y-m-d',
      'previous_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $currentDate = Carbon::parse($request->input('current_date'));
    $previousDate = Carbon::parse($request->input('previous_date'));

    $segments = DB::table('SEGMENT_MASTER')
      ->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->orderBy('SEGMENT_ID')
      ->get(['SEGMENT_ID', 'SEGMENT_NAME']);

    //$segmentNames = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentNamesRaw = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $this->translationMap = [
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
          'name' => $this->translationMap[strtolower($name)] ?? $name,
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
      ->whereIn('prev_snap.SEGMENT_ID', $this->relevantSegmentIds)
      ->whereIn('current_snap.SEGMENT_ID', $this->relevantSegmentIds)
      ->select(
        'prev_snap.SEGMENT_ID AS previous_segment_id',
        'current_snap.SEGMENT_ID AS current_segment_id',
        DB::raw('COUNT(current_snap.TICKET) AS user_count')
      )
      ->groupBy('prev_snap.SEGMENT_ID', 'current_snap.SEGMENT_ID')
      ->get();

    foreach ($transitions as $transition) {
      if (isset($matrix[$transition->previous_segment_id][$transition->current_segment_id])) {
        $matrix[$transition->previous_segment_id][$transition->current_segment_id] = $transition->user_count;
      }
    }

    $formattedMatrix = [];
    foreach ($segmentIdsOrdered as $prevId) {
      $row = [];
      foreach ($segmentIdsOrdered as $currId) {
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























































  public function getSegmentComposition(Request $request)
  {
    $request->validate([
      'snapshot_date' => 'required|date_format:Y-m-d',
      'group_by' => 'required|in:age,gender,store',
    ]);

    $snapshotDate = Carbon::parse($request->input('snapshot_date'));
    $groupBy = $request->input('group_by');

    $segments = DB::table('SEGMENT_MASTER')
      ->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->orderBy('SEGMENT_ID')
      ->get(['SEGMENT_ID', 'SEGMENT_NAME']);

    $segmentNamesRaw = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentIdsOrdered = $segments->pluck('SEGMENT_ID')->toArray();

    $translatedSegmentNames = [];
    foreach ($segmentIdsOrdered as $id) {
      $name = $segmentNamesRaw[$id] ?? null;
      if ($name) {
        $translatedSegmentNames[$id] = $this->translationMap[strtolower($name)] ?? $name;
      }
    }

    $query = DB::table('T_GRADE_SNAPSHOT AS gs')
      ->whereDate('gs.SNAPSHOT_DATE', $snapshotDate)
      ->whereIn('gs.SEGMENT_ID', $this->relevantSegmentIds);

    $formattedComposition = [];
    $attributeHeaders = [];

    $ageGroupCase = "
      CASE
        WHEN tm.BIRTHDAY IS NULL THEN '不明'
        ELSE
          CASE
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 10 AND 19 THEN '10代'
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 20 AND 29 THEN '20代'
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 30 AND 39 THEN '30代'
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 40 AND 49 THEN '40代'
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 50 AND 59 THEN '50代'
            WHEN DATEDIFF(year, tm.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') >= 60 THEN '60代以上'
            ELSE '不在'
          END
        END
    ";

    $genderCase = "
      CASE
        WHEN tm.SEX = 0 THEN '男性'
        WHEN tm.SEX = 1 THEN '女性'
        ELSE '不明'
      END
    ";

    $storeNameCase = "
      CASE
        WHEN gs.LAST_VISITED_SHOP IS NULL OR gs.LAST_VISITED_SHOP = 0 THEN '不在店舗'
        ELSE CONCAT('店舗', gs.LAST_VISITED_SHOP)
      END
    ";

    $selectColumns = [];
    $groupByColumns = [];
    $rawAttributeCase = '';

    if ($groupBy === 'age' || $groupBy === 'gender') {
      $query->join('T_MEMBER_INFO AS tm', 'gs.TICKET', '=', 'tm.TICKET');

      if ($groupBy === 'age') {
        $rawAttributeCase = $ageGroupCase;
        $selectColumns = [
          DB::raw("{$ageGroupCase} AS attribute_value"),
          'gs.SEGMENT_ID',
          DB::raw("COUNT(gs.TICKET) AS user_count")
        ];
        $groupByColumns = [DB::raw($rawAttributeCase), 'gs.SEGMENT_ID'];
        $attributeHeaders = ['年代'];
      } elseif ($groupBy === 'gender') {
        $rawAttributeCase = $genderCase;
        $selectColumns = [
          DB::raw("{$genderCase} AS attribute_value"),
          'gs.SEGMENT_ID',
          DB::raw("COUNT(gc.TICKET) AS user_count")
        ];
        $groupByColumns = [DB::raw($rawAttributeCase), 'gs.SEGMENT_ID'];
        $attributeHeaders = ['性別'];
      }
    } elseif ($groupBy === 'store') {
      $rawAttributeCase = $storeNameCase;
      $selectColumns = [
        DB::raw("{$storeNameCase} AS attribute_value"),
        'gs.SEGMENT_ID',
        DB::raw('COUNT(gs.TICKET) AS user_count')
      ];
      $groupByColumns = [DB::raw($rawAttributeCase), 'gs.SEGMENT_ID'];
      $attributeHeaders = ['店舗'];
    }

    $results = $query
      ->select($selectColumns)
      ->groupBy($groupByColumns)
      ->get();

    $totalUsersByAttribute = [];
    foreach ($results as $row) {
      $key = $row->attribute_value;
      $totalUsersByAttribute[$key] = ($totalUsersByAttribute[$key] ?? 0) + $row->user_count;
    }

    $tempComposition = [];
    foreach ($results as $row) {
      $key = $row->attribute_value;
      if (!isset($tempComposition[$key])) {
        $tempComposition[$key] = [
          'attribute_value' => $row->attribute_value,
        ];
      }
      $segmentName = $translatedSegmentNames[$row->SEGMENT_ID];
      $ratio = $totalUsersByAttribute[$key] > 0 ? ($row->user_count / $totalUsersByAttribute[$key]) * 100 : 0;
      $tempComposition[$key][$segmentName] = sprintf('%.1f%%', $ratio);
    }

    if ($groupBy === 'age') {
      $ageOrder = ['10代', '20代', '30代', '40代', '50代', '60代以上', '不明'];
      usort($tempComposition, function ($a, $b) use ($ageOrder) {
        return array_search($a['attribute_value'], $ageOrder) <=> array_search($b['attribute_value'], $ageOrder);
      });
    } elseif ($groupBy === 'gender') {
      $genderOrder = ['男性', '女性', '不明'];
      usort($tempComposition, function ($a, $b) use ($genderOrder) {
        return array_search($a['attribute_value'], $genderOrder) <=> array_search($b['attribute_value'], $genderOrder);
      });
    } elseif ($groupBy === 'store') {
      usort($tempComposition, function ($a, $b) {
        return array_search($a['attribute_value'], $b['attribute_value']);
      });
    }

    $formattedComposition = array_values($tempComposition);

    return response()->json([
      'status' => 'success',
      'snapshot_date' => $snapshotDate->format('Y-m-d'),
      'group_by' => $groupBy,
      'attribute_headers' => $attributeHeaders,
      'segment_headers' => array_values($translatedSegmentNames),
      'data' => $formattedComposition,
    ]);
  }
}
