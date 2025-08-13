<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradeSnapshot;
use App\Services\GradeAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\map;

class GradeAnalyticsController extends Controller
{
  protected $service;

  public function __construct(GradeAnalyticsService $service)
  {
    $this->service = $service;
  }

  private $relevantSegmentIds = [1, 2, 3, 4, 5];

  private $translationMap = [
    'core' => 'コア',
    'middle' => 'ミドル',
    'light' => 'ライト',
    'dormant' => '休眠',
    'churned' => '離反',
    'never' => '非コンバージョンユーザー',
    'new' => '新規',
  ];

  public function getKpiComparison(Request $request)
  {
    // 1. 요청 파라미터 검사
    $request->validate([
      'current_date'  => 'required|date_format:Y-m-d',
      'previous_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $currentDate  = Carbon::parse($request->input('current_date'));
    $previousDate = Carbon::parse($request->input('previous_date'));
    $branchIds = $request->input('selected_branches') ?? null;

    $kpiData = $this->service->getKpiComparison($currentDate, $previousDate, $branchIds);

    return response()->json([
      'status'        => 'success',
      'data'          => $kpiData,
      'current_date'  => $currentDate->format('Y-m-d'),
      'previous_date' => $previousDate->format('Y-m-d'),
      'selected_branches' => $branchIds,
    ]);
  }































  public function getSegmentMigrationMatrix(Request $request)
  {
    $request->validate([
      'current_date'  => 'required|date_format:Y-m-d',
      'previous_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $currentDate  = Carbon::parse($request->input('current_date'));
    $previousDate = Carbon::parse($request->input('previous_date'));
    $branchIds = $request->input('selected_branches') ?? null;

    $matrixData = $this->service->getSegmentMigrationMatrix($currentDate, $previousDate, $branchIds);

    return response()->json([
      'status'        => 'success',
      'data' => $matrixData,
      'current_date'  => $currentDate->format('Y-m-d'),
      'previous_date' => $previousDate->format('Y-m-d'),
      'selected_branches' => $branchIds,
    ]);
  }






























  public function getSegmentComposition(Request $request)
  {
    // 1. 요청 파라미터 유효성 검사
    $request->validate([
      'snapshot_date' => 'required|date_format:Y-m-d',
    ]);

    $snapshotDate = Carbon::parse($request->input('snapshot_date'));
    $branchIds = $request->input('selected_branches') ?? null;

    $formattedComposition = $this->service->getSegmentComposition($snapshotDate, $branchIds);

    return response()->json([
      'status' => 'success',
      'snapshot_date' => $snapshotDate->format('Y-m-d'),
      'data' => $formattedComposition,
      'selected_branches' => $branchIds,
    ]);
  }






























  public function getSegmentSankeyData(Request $request)
  {
    $request->validate([
      'start_date' => 'required|date_format:Y-m-d',
      'end_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $startDateStr = $request->input('start_date');
    $endDateStr = $request->input('end_date');
    $branchIds = $request->input('selected_branches') ?? null;

    try {
      $startDate = Carbon::parse($startDateStr);
      $endDate = Carbon::parse($endDateStr);
    } catch (\Exception $e) {
      return response()->json(['error' => '無効な日付形式です。'], 400);
    }

    if ($startDate->greaterThan($endDate)) {
      return response()->json(['error' => 'start_dateはend_dateより遅い日付にすることはできません。'], 400);
    }

    // 기존 유저 전이 데이터
    $allTransitions = GradeSnapshot::from('T_GRADE_SNAPSHOT AS t1')
      ->join('T_GRADE_SNAPSHOT AS t2', function ($join) use ($branchIds) {
        $join->on('t1.MEMBER_ID', '=', 't2.MEMBER_ID')
          ->whereRaw('t2.SNAPSHOT_DATE = DATEADD(month, 1, t1.SNAPSHOT_DATE)')
          ->when(!empty($branchIds), function ($query) use ($branchIds) {
            $query->whereIn('t2.LAST_VISITED_SHOP', $branchIds);
          });
      })
      ->join('SEGMENT_MASTER AS sm1', 't1.SEGMENT_ID', '=', 'sm1.SEGMENT_ID')
      ->join('SEGMENT_MASTER AS sm2', 't2.SEGMENT_ID', '=', 'sm2.SEGMENT_ID')
      ->select(
        'sm1.SEGMENT_NAME AS current_segment_name',
        'sm2.SEGMENT_NAME AS next_segment_name',
        't1.SNAPSHOT_DATE AS current_snapshot_date',
        't2.SNAPSHOT_DATE AS next_snapshot_date',
        DB::raw('COUNT(t1.MEMBER_ID) AS transition_count')
      )
      ->byBranches($branchIds, 't1')
      ->whereBetween('t1.SNAPSHOT_DATE', [$startDate->format('Y-m-d'), $endDate->copy()->subMonth()->format('Y-m-d')])
      ->whereBetween('t2.SNAPSHOT_DATE', [$startDate->copy()->subMonth()->format('Y-m-d'), $endDate])
      ->groupBy('sm1.SEGMENT_NAME', 'sm2.SEGMENT_NAME', 't1.SNAPSHOT_DATE', 't2.SNAPSHOT_DATE')
      ->orderBy('t1.SNAPSHOT_DATE')
      ->orderByRaw("
      CASE sm1.SEGMENT_NAME
        WHEN 'core' THEN 1
        WHEN 'middle' THEN 2
        WHEN 'light' THEN 3
        WHEN 'dormant' THEN 4
        WHEN 'churned' THEN 5
        WHEN 'never' THEN 6
        ELSE 99
      END
    ")
      ->orderByRaw("
      CASE sm2.SEGMENT_NAME
        WHEN 'core' THEN 1
        WHEN 'middle' THEN 2
        WHEN 'light' THEN 3
        WHEN 'dormant' THEN 4
        WHEN 'churned' THEN 5
        WHEN 'never' THEN 6
        ELSE 99
      END
    ")
      ->get();

    // 신규 유저 데이터 (endDate 기준 신규 유입)
    $newUsers = GradeSnapshot::from('T_GRADE_SNAPSHOT AS t')
      ->join('SEGMENT_MASTER AS sm', 't.SEGMENT_ID', '=', 'sm.SEGMENT_ID')
      ->where('t.SNAPSHOT_DATE', $endDate->format('Y-m-d'))
      ->whereNotIn('t.MEMBER_ID', function ($query) use ($startDate) {
        $query->select('MEMBER_ID')
          ->from('T_GRADE_SNAPSHOT')
          ->where('SNAPSHOT_DATE', $startDate->format('Y-m-d'));
      })
      ->select(
        DB::raw("'新規 (" . $endDate->format('Y-m-d') . ")' AS source_segment_name"),
        'sm.SEGMENT_NAME AS target_segment_name',
        DB::raw("COUNT(*) AS transition_count")
      )
      ->groupBy('sm.SEGMENT_NAME')
      ->get();

    $nodes = [];
    $links = [];
    $nodeMap = [];
    $nodeIdCounter = 0;

    // 전이된 기존 유저 데이터 처리
    foreach ($allTransitions as $transition) {
      $currentSnapshotDate = $transition->current_snapshot_date;
      $nextSnapshotDate = $transition->next_snapshot_date;

      $translatedCurrentSegmentName = $this->translationMap[strtolower($transition->current_segment_name)] ?? $transition->current_segment_name;
      $translatedNextSegmentName = $this->translationMap[strtolower($transition->next_segment_name)] ?? $transition->next_segment_name;

      $sourceNodeName = $translatedCurrentSegmentName . ' (' . $currentSnapshotDate . ')';
      $targetNodeName = $translatedNextSegmentName . ' (' . $nextSnapshotDate . ')';

      if (!isset($nodeMap[$sourceNodeName])) {
        $nodeMap[$sourceNodeName] = $nodeIdCounter;
        $nodes[] = ['id' => $nodeIdCounter, 'name' => $sourceNodeName];
        $nodeIdCounter++;
      }

      if (!isset($nodeMap[$targetNodeName])) {
        $nodeMap[$targetNodeName] = $nodeIdCounter;
        $nodes[] = ['id' => $nodeIdCounter, 'name' => $targetNodeName];
        $nodeIdCounter++;
      }

      $links[] = [
        'source' => $nodeMap[$sourceNodeName],
        'target' => $nodeMap[$targetNodeName],
        'value' => $transition->transition_count,
      ];
    }

    // 신규 유입 유저 Sankey 노드/링크 처리
    foreach ($newUsers as $entry) {
      $sourceNodeName = $entry->source_segment_name; // "new (YYYY-MM-DD)"
      $targetNodeName = ($this->translationMap[strtolower($entry->target_segment_name)] ?? $entry->target_segment_name) . ' (' . $endDate->format('Y-m-d') . ')';

      if (!isset($nodeMap[$sourceNodeName])) {
        $nodeMap[$sourceNodeName] = $nodeIdCounter;
        $nodes[] = ['id' => $nodeIdCounter, 'name' => $sourceNodeName];
        $nodeIdCounter++;
      }

      if (!isset($nodeMap[$targetNodeName])) {
        $nodeMap[$targetNodeName] = $nodeIdCounter;
        $nodes[] = ['id' => $nodeIdCounter, 'name' => $targetNodeName];
        $nodeIdCounter++;
      }

      $links[] = [
        'source' => $nodeMap[$sourceNodeName],
        'target' => $nodeMap[$targetNodeName],
        'value' => $entry->transition_count,
      ];
    }

    return response()->json([
      'nodes' => $nodes,
      'links' => $links,
    ]);
  }































  public function getSegmentSankeyData2(Request $request)
  {

    $request->validate([
      'start_date' => 'required|date_format:Y-m-d',
      'end_date' => 'required|date_format:Y-m-d|before:current_date',
    ]);

    $startDateStr = $request->input('start_date');
    $endDateStr = $request->input('end_date');

    if (!$startDateStr || !$endDateStr) {
      return response()->json(['error' => 'start_datetと end_dateがありません。'], 400);
    }

    try {
      $startDate = Carbon::parse($startDateStr);
      $endDate = Carbon::parse($endDateStr);
    } catch (\Exception $e) {
      return response()->json(['error' => '無効な日付形式です。'], 400);
    }

    if ($startDate->greaterThan($endDate)) {
      return response()->json(['error' => 'start_dateはend_dateより遅い日付にすることはできません。'], 400);
    }

    $snapshotDates = [];
    $currentDate = clone $startDate;

    while ($currentDate->lessThanOrEqualTo($endDate)) {
      $snapshotDates[] = $currentDate->format('Y-m-d');
      $currentDate->addMonthNoOverflow();
    }

    if (count($snapshotDates) < 2) {
      return response()->json(['nodes' => [], 'links' => []]);
    }

    $nodes = [];
    $links = [];
    $nodeMap = [];
    $nodeIdCounter = 0;

    for ($i = 0; $i < count($snapshotDates) - 1; $i++) {
      $currentSnapshotDate = $snapshotDates[$i];
      $nextSnapshotDate = $snapshotDates[$i + 1];

      $transitions = DB::connection('sqlsrv_192.168.180.105')
        ->table('T_GRADE_SNAPSHOT AS t1')
        ->join('T_GRADE_SNAPSHOT AS t2', 't1.MEMBER_ID', '=', 't2.MEMBER_ID')
        ->join('SEGMENT_MASTER AS sm1', 't1.SEGMENT_ID', '=', 'sm1.SEGMENT_ID')
        ->join('SEGMENT_MASTER AS sm2', 't2.SEGMENT_ID', '=', 'sm2.SEGMENT_ID')
        ->select(
          'sm1.SEGMENT_NAME AS current_segment_name',
          'sm2.SEGMENT_NAME AS next_segment_name',
          DB::raw('COUNT(t1.MEMBER_ID) AS transition_count')
        )
        ->where('t1.SNAPSHOT_DATE', $currentSnapshotDate)
        ->where('t2.SNAPSHOT_DATE', $nextSnapshotDate)
        ->groupBy('sm1.SEGMENT_NAME', 'sm2.SEGMENT_NAME')
        ->get();

      foreach ($transitions as $transition) {
        $translatedCurrentSegmentName = $this->translationMap[strtolower($transition->current_segment_name)] ?? $transition->current_segment_name;
        $translatedNextSegmentName = $this->translationMap[strtolower($transition->next_segment_name)] ?? $transition->next_segment_name;

        $sourceNodeName = $translatedCurrentSegmentName . ' (' . $currentSnapshotDate . ')';
        $targetNodeName = $translatedNextSegmentName . ' (' . $nextSnapshotDate . ')';

        if (!isset($nodeMap[$sourceNodeName])) {
          $nodeMap[$sourceNodeName] = $nodeIdCounter;
          $nodes[] = ['id' => $nodeIdCounter, 'name' => $sourceNodeName];
          $nodeIdCounter++;
        }

        if (!isset($nodeMap[$targetNodeName])) {
          $nodeMap[$targetNodeName] = $nodeIdCounter;
          $nodes[] = ['id' => $nodeIdCounter, 'name' => $targetNodeName];
          $nodeIdCounter++;
        }

        $links[] = [
          'source' => $nodeMap[$sourceNodeName],
          'target' => $nodeMap[$targetNodeName],
          'value' => $transition->transition_count,
        ];
      }
    }

    return response()->json([
      'nodes' => $nodes,
      'links' => $links,
    ]);
  }








































  public function getSegmentSummary(Request $request)
  {
    $request->validate([
      'snapshot_date' => 'required|date_format:Y-m-d',
    ]);

    $snapshotDate = Carbon::parse($request->input('snapshot_date'));
    $branchIds = $request->input('selected_branches') ?? null;

    $data = $this->service->getSegmentSummary($snapshotDate, $branchIds);

    return response()->json([
      'status' => 'success',
      'snapshot_date' => $snapshotDate->format('Y-m-d'),
      'data' => $data,
    ]);
  }
}
