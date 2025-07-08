<?php

namespace App\Http\Controllers;

use App\Models\SegmentMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;

class GradeTransitionController extends Controller
{
  public function index(Request $request)
  {
    $start = $request->query('start');
    $end = $request->query('end');

    if (!$start || !$end) {
      $end = date('Y-m-d');
      $start = date('Y-m-d', strtotime('-6 days'));
    }

    $segments = SegmentMaster::pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentIds = array_keys($segments);

    $dates = DB::table('T_USER_GRADE_CHANGE_LOG')
      ->whereBetween('SNAPSHOT_DATE', [$start, $end])
      ->distinct()
      ->pluck('SNAPSHOT_DATE')
      ->toArray();

    $allCombination = [];
    foreach ($dates as $date) {
      foreach ($segmentIds as $before) {
        foreach ($segmentIds as $after) {
        }
      }
    }


    $data = DB::table('T_USER_GRADE_CHANGE_LOG')
      ->select('SNAPSHOT_DATE', 'SEGMENT_BEFORE_ID', 'SEGMENT_AFTER_ID', DB::raw('COUNT(*) as transition_count'))
      ->whereBetween('SNAPSHOT_DATE', [$start, $end])
      ->groupBy('SNAPSHOT_DATE', 'SEGMENT_BEFORE_ID', 'SEGMENT_AFTER_ID')
      ->orderBy('SNAPSHOT_DATE')
      ->orderBy('SEGMENT_BEFORE_ID')
      ->orderBy('SEGMENT_AFTER_ID')
      ->get()
      //{"start":"2025-06-15","end":"2025-06-20","transitions":[{"SNAPSHOT_DATE":"2025-06-16","SEGMENT_BEFORE_ID":null,"SEGMENT_AFTER_ID":"1","transition_count":"366"},{"SNAPSHOT_DATE":"2025-06-16","SEGMENT_BEFORE_ID":null,"SEGMENT_AFTER_ID":"2","transition_count":"428"},{"SNAPSHOT_DATE":"2025-06-16","SEGMENT_BEFORE_ID":null,"SEGMENT_AFTER_ID":"3","transition_count":"1168"},{"SNAPSHOT_DATE":"2025-06-16","SEGMENT_BEFORE_ID":null,"SEGMENT_AFTER_ID":"4","transition_count":"1790"},{"SNAPSHOT_DATE":"2025-06-16","SEGMENT_BEFORE_ID":null,"SEGMENT_AFTER_ID":"5","transition_count":"124439"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"1","SEGMENT_AFTER_ID":"1","transition_count":"366"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"2","SEGMENT_AFTER_ID":"2","transition_count":"426"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"2","SEGMENT_AFTER_ID":"3","transition_count":"2"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"3","SEGMENT_AFTER_ID":"3","transition_count":"1160"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"3","SEGMENT_AFTER_ID":"4","transition_count":"8"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"4","SEGMENT_AFTER_ID":"4","transition_count":"1786"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"4","SEGMENT_AFTER_ID":"5","transition_count":"4"},{"SNAPSHOT_DATE":"2025-06-17","SEGMENT_BEFORE_ID":"5","SEGMENT_AFTER_ID":"5","transition_count":"124439"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"1","SEGMENT_AFTER_ID":"1","transition_count":"363"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"1","SEGMENT_AFTER_ID":"2","transition_count":"3"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"2","SEGMENT_AFTER_ID":"2","transition_count":"425"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"2","SEGMENT_AFTER_ID":"3","transition_count":"1"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"3","SEGMENT_AFTER_ID":"3","transition_count":"1159"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"3","SEGMENT_AFTER_ID":"4","transition_count":"3"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"4","SEGMENT_AFTER_ID":"4","transition_count":"1787"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"4","SEGMENT_AFTER_ID":"5","transition_count":"7"},{"SNAPSHOT_DATE":"2025-06-18","SEGMENT_BEFORE_ID":"5","SEGMENT_AFTER_ID":"5","transition_count":"124443"}]}
      ->map(function ($item) use ($segments) {
        return [
          'SNAPSHOT_DATE' => $item->SNAPSHOT_DATE,
          'before_segment_id' => $item->SEGMENT_BEFORE_ID,
          'before_segment_name' => $segments[$item->SEGMENT_BEFORE_ID] ?? 'NULL',
          'after_segment_id' => $item->SEGMENT_AFTER_ID,
          'after_segment_name' => $segments[$item->SEGMENT_AFTER_ID] ?? 'NULL',
          'transition_count' => (int)$item->transition_count,
        ];
      });

    return response()->json([
      'start' => $start,
      'end' => $end,
      'transitions' => $data,
    ]);
  }

  public function compare(Request $request)
  {
    $start = $request->query('start');
    $end = $request->query('end');

    if (!$start || !$end) {
      return response()->json(['error' => 'start and end query parameters are required.'], 400);
    }

    $segments = SegmentMaster::pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $gradePriority = [
      'core' => 1,
      'middle' => 2,
      'light' => 3,
      'dormant' => 4,
      'churned' => 5,
    ];

    $data = DB::table('T_USER_GRADE_CHANGE_LOG')
      ->select('SNAPSHOT_DATE', 'SEGMENT_BEFORE_ID', 'SEGMENT_AFTER_ID', DB::raw('COUNT(*) as transition_count'))
      ->whereBetween('SNAPSHOT_DATE', [$start, $end])
      ->groupBy('SNAPSHOT_DATE', 'SEGMENT_BEFORE_ID', 'SEGMENT_AFTER_ID')
      ->orderBy('SNAPSHOT_DATE')
      ->orderBy('SEGMENT_BEFORE_ID')
      ->orderBy('SEGMENT_AFTER_ID')
      ->get()
      ->map(function ($item) use ($segments, $gradePriority) {
        $beforeName = $segments[$item->SEGMENT_BEFORE_ID] ?? 'NULL';
        $afterName = $segments[$item->SEGMENT_AFTER_ID] ?? 'NULL';

        $direction = 'same';
        if ($beforeName !== 'NULL' && $afterName !== 'NULL') {
          $beforeRank = $gradePriority[$beforeName] ?? 999;
          $afterRank = $gradePriority[$afterName] ?? 999;

          if ($afterRank < $beforeRank) {
            $direction = 'up';
          } else {
            $direction = 'down';
          }
        }

        return [
          'SNAPSHOT_DATE'       => $item->SNAPSHOT_DATE,
          'before_segment_id'   => $item->SEGMENT_BEFORE_ID,
          'before_segment_name' => $beforeName,
          'after_segment_id'    => $item->SEGMENT_AFTER_ID,
          'after_segment_name'  => $afterName,
          'transition_count'    => (int)$item->transition_count,
          'direction'           => $direction,
        ];
      });

    return response()->json([
      'start' => $start,
      'end' => $end,
      'transitions' => $data,
    ]);
  }

  public function compare3(Request $request)
  {
    $start = $request->query('start');
    $end   = $request->query('end');

    if (!$start || !$end) {
      return response()->json(['error' => 'start and end are required.'], 400);
    }

    $segments = SegmentMaster::pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $period = collect();
    $current = Carbon::parse($start);
    $final = Carbon::parse($end);
    while ($current->lt($final)) {
      $period->push($current->format('Y-m-d'));
      $current->addDay();
    }

    $results = [];

    foreach ($period as $date) {
      $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

      $transitions = DB::table('T_USER_GRADE_SNAPSHOT as s1')
        ->join('T_USER_GRADE_SNAPSHOT as s2', function ($join) use ($date, $nextDate) {
          $join->on('s1.TICKET', '=', 's2.TICKET')
            ->where('s1.SNAPSHOT_DATE', '=', $date)
            ->where('s2.SNAPSHOT_DATE', '=', $nextDate);
        })
        ->select(
          DB::raw("'$date' as base_date"),
          DB::raw("'$nextDate' as target_date"),
          's1.SEGMENT_ID as before_segment_id',
          's2.SEGMENT_ID as after_segment_id',
          DB::raw('COUNT(*) as transition_count')
        )
        ->groupBy('s1.SEGMENT_ID', 's2.SEGMENT_ID')
        ->get()
        ->map(function ($item) use ($segments) {
          return [
            'base_date' => $item->base_date,
            'target_date' => $item->target_date,
            'before_segment_id' => $item->before_segment_id,
            'before_segment_name' => $segments[$item->before_segment_id] ?? 'NULL',
            'after_segment_id' => $item->after_segment_id,
            'after_segment_name' => $segments[$item->after_segment_id] ?? 'NULL',
            'transition_count' => (int)$item->transition_count,
          ];
        });

      $results = array_merge($results, $transitions->toArray());
    }

    return response()->json([
      'start' => $start,
      'end'   => $end,
      'transitions' => $results,
    ]);
  }

  public function compare4(Request $request)
  {
    $start = $request->query('start');
    $end   = $request->query('end');

    if (!$start || !$end) {
      return response()->json(['error' => 'start and end are required.'], 400);
    }

    $segments = SegmentMaster::pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $period = collect();
    $current = Carbon::parse($start);
    $final = Carbon::parse($end);
    while ($current->lt($final)) {
      $period->push($current->format('Y-m-d'));
      $current->addDay();
    }

    $results = [];
    $sankeyData = [];

    foreach ($period as $date) {
      $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

      $transitions = DB::table('T_USER_GRADE_SNAPSHOT as s1')
        ->join('T_USER_GRADE_SNAPSHOT as s2', function ($join) use ($date, $nextDate) {
          $join->on('s1.TICKET', '=', 's2.TICKET')
            ->where('s1.SNAPSHOT_DATE', '=', $date)
            ->where('s2.SNAPSHOT_DATE', '=', $nextDate);
        })
        ->select(
          DB::raw("'$date' as base_date"),
          DB::raw("'$nextDate' as target_date"),
          's1.SEGMENT_ID as before_segment_id',
          's2.SEGMENT_ID as after_segment_id',
          DB::raw('COUNT(*) as transition_count')
        )
        ->groupBy('s1.SEGMENT_ID', 's2.SEGMENT_ID')
        ->get()
        ->map(function ($item) use ($segments, $date, $nextDate, &$sankeyData) {
          $beforeName = $segments[$item->before_segment_id] ?? 'null';
          $afterName = $segments[$item->after_segment_id] ?? 'null';

          $dateFormatted = Carbon::parse($date)->format('ymd');
          $nextDateFormatted = Carbon::parse($nextDate)->format('ymd');

          $sankeyData[] = [
            'from' => "{$dateFormatted}_{$beforeName}",
            'to' => "{$nextDateFormatted}_{$afterName}",
            "value" => (int)$item->transition_count,
          ];

          return [
            'base_date' => $item->base_date,
            'target_date' => $item->target_date,
            'before_segment_id' => $item->before_segment_id,
            'before_segment_name' => $segments[$item->before_segment_id] ?? 'NULL',
            'after_segment_id' => $item->after_segment_id,
            'after_segment_name' => $segments[$item->after_segment_id] ?? 'NULL',
            'transition_count' => (int)$item->transition_count,
          ];
        });

      $results = array_merge($results, $transitions->toArray());
    }

    return response()->json([
      'start' => $start,
      'end'   => $end,
      'transitions' => $results,
      'sankey' => $sankeyData,
    ]);
  }

  public function sankey(Request $request)
  {
    $start = $request->query('start');
    $end   = $request->query('end');
    $interval = (int)$request->query('interval', 1); // 기본값 1
    $intervalType = $request->query('interval_type', 'day'); // 기본값 day

    if (!$start || !$end) {
      return response()->json(['error' => 'start and end are required.'], 400);
    }

    $segments = SegmentMaster::pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    // 📌 간격에 따른 날짜쌍 계산
    $datePairs = [];
    $current = Carbon::parse($start);
    $final = Carbon::parse($end);

    while (true) {
      $next = $current->copy()->add($interval, $intervalType);
      if ($next->gte($final)) {
        // 마지막은 강제로 종료점(end)을 포함
        if ($current->lt($final)) {
          $datePairs[] = [$current->format('Y-m-d'), $final->format('Y-m-d')];
        }
        break;
      }
      $datePairs[] = [$current->format('Y-m-d'), $next->format('Y-m-d')];
      $current = $next;
    }

    $results = [];

    foreach ($datePairs as [$baseDate, $targetDate]) {
      $transitions = DB::table('T_USER_GRADE_SNAPSHOT as s1')
        ->join('T_USER_GRADE_SNAPSHOT as s2', function ($join) use ($baseDate, $targetDate) {
          $join->on('s1.TICKET', '=', 's2.TICKET')
            ->where('s1.SNAPSHOT_DATE', '=', $baseDate)
            ->where('s2.SNAPSHOT_DATE', '=', $targetDate);
        })
        ->select(
          DB::raw("'$baseDate' as base_date"),
          DB::raw("'$targetDate' as target_date"),
          's1.SEGMENT_ID as before_segment_id',
          's2.SEGMENT_ID as after_segment_id',
          DB::raw('COUNT(*) as transition_count')
        )
        ->groupBy('s1.SEGMENT_ID', 's2.SEGMENT_ID')
        ->get()
        ->map(function ($item) use ($segments, $baseDate, $targetDate) {
          $beforeName = $segments[$item->before_segment_id] ?? 'null';
          $afterName = $segments[$item->after_segment_id] ?? 'null';

          $dateFormatted = Carbon::parse($baseDate)->format('ymd');
          $nextDateFormatted = Carbon::parse($targetDate)->format('ymd');

          return [
            'from' => "{$dateFormatted}_{$beforeName}",
            'to'   => "{$nextDateFormatted}_{$afterName}",
            'value' => (int)$item->transition_count,
          ];
        });

      $results = array_merge($results, $transitions->toArray());
    }

    return response()->json([
      'start' => $start,
      'end'   => $end,
      'interval' => $interval,
      'interval_type' => $intervalType,
      'sankey' => $results,
    ]);
  }
}
