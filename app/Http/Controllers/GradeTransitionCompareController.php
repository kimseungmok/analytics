<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SegmentMaster;
use Inertia\Inertia;

class GradeTransitionCompareController extends Controller
{
  public function show(Request $request){
    return Inertia::render('GradeTransitionCompare');
  }

  public function compare(Request $request)
  {
    $baseDate = $request->query('base_date');
    $targetDate = $request->query('target_date');

    if (!$baseDate || !$targetDate) {
      return response()->json(['error' => 'base_date and target_date are required'], 400);
    }

    $segments = SegmentMaster::pluck('segment_name', 'segment_id')->toArray();

    // ✅ 등급 우선순위 정의 (하드코딩 버전)
    $priority = ['churned', 'dormant', 'light', 'middle', 'core'];

    $data = DB::table('T_USER_GRADE_SHAPSHOT as base')
      ->rightJoin('T_USER_GRADE_SNAPSHOT as target', function ($join) use ($targetDate) {
        $join->on('base.TICKET','=','target.TICKET')
          ->where('target.SNAPSHOT_DATE', '=', $targetDate);
      })
      ->where(function ($query) use ($baseDate) {
        $query->where('base.SNAPSHOT_DATE', '=', $baseDate)
          ->orWhereNull('base.SNAPSHOT_DATE');
      })
      ->groupBy('base.SEGMENT_ID', 'target.SEGMENT_ID')
      ->orderBy('base.SEGMENT_ID')
      ->orderBy('target.SEGMENT_ID')
      ->selectRaw('
        base.SEGMENT_ID as before_segment_id,
        target.SEGMENT_ID as after_segment_id,
        COUNT(*) as transition_count
      ')
      ->get()
      ->map(function ($item) use ($segments, $priority) {
        $beforeName = $segments[$item->before_segment_id] ?? null;
        $afterName = $segments[$item->after_segment_id] ?? null;

        $fromIdx = array_search($beforeName, $priority);
        $toIdx = array_search($afterName, $priority);

        $direction = ($fromIdx === false || $toIdx === false) ? 'unknown' : ($fromIdx < $toIdx ? 'up' : ($fromIdx > $toIdx ? 'down' : 'same'));

        return [
          'before_segment_id'   => $item->before_segment_id,
          'before_segment_name' => $beforeName ?? 'NULL',
          'after_segment_id'    => $item->after_segment_id,
          'after_segment_name'  => $afterName ?? 'NULL',
          'direction'           => $direction,
        ];
      });

      return response()->json([
        'base' => $baseDate,
        'target' => $targetDate,
        'trasitions' => $data,
      ]);
  }

  public function compare_bak(Request $request)
  {
    $baseDate = $request->query('base_date');
    $targetDate = $request->query('target_date');

    if (!$baseDate || !$targetDate) {
      return response()->json(['error' => 'base_date and target_date are required'], 400);
    }

    $segments = SegmentMaster::pluck('segment_name', 'segment_id')->toArray();

    $data = DB::table('T_USER_GRADE_SNAPSHOT as base')
        ->rightJoin('T_USER_GRADE_SNAPSHOT as target', function ($join) use ($targetDate) {
            $join->on('base.TICKET', '=', 'target.TICKET')
                 ->where('target.SNAPSHOT_DATE', '=', $targetDate);
        })
        ->where(function ($query) use ($baseDate) {
            $query->where('base.SNAPSHOT_DATE', '=', $baseDate)
                  ->orWhereNull('base.SNAPSHOT_DATE');
        })
        ->groupBy('base.SEGMENT_ID', 'target.SEGMENT_ID')
        ->orderBy('base.SEGMENT_ID')
        ->orderBy('target.SEGMENT_ID')
        ->selectRaw('
            base.SEGMENT_ID as before_segment_id,
            target.SEGMENT_ID as after_segment_id,
            COUNT(*) as transition_count
        ')
        ->get()
        ->map(function ($item) use ($segments) {
            return [
                'before_segment_id'   => $item->before_segment_id,
                'before_segment_name' => $segments[$item->before_segment_id] ?? 'NULL',
                'after_segment_id'    => $item->after_segment_id,
                'after_segment_name'  => $segments[$item->after_segment_id] ?? 'NULL',
                'transition_count'    => (int) $item->transition_count,
            ];
        });

    return response()->json([
        'base' => $baseDate,
        'target' => $targetDate,
        'transitions' => $data,
    ]);
  }

  public function compare2(Request $request)
{
    $sample = [
        ['before_segment_name' => 'NULL',      'after_segment_name' => 'light',   'transition_count' => 2000],
        ['before_segment_name' => 'NULL',      'after_segment_name' => 'middle',  'transition_count' => 1000],
        ['before_segment_name' => 'NULL',      'after_segment_name' => 'core',    'transition_count' => 300],
        ['before_segment_name' => 'light',   'after_segment_name' => 'light',   'transition_count' => 1200],
        ['before_segment_name' => 'light',   'after_segment_name' => 'middle',  'transition_count' => 400],
        ['before_segment_name' => 'light',   'after_segment_name' => 'dormant', 'transition_count' => 100],
        ['before_segment_name' => 'middle',  'after_segment_name' => 'middle',  'transition_count' => 800],
        ['before_segment_name' => 'middle',  'after_segment_name' => 'core',    'transition_count' => 150],
        ['before_segment_name' => 'middle',  'after_segment_name' => 'dormant', 'transition_count' => 50],
        ['before_segment_name' => 'core',    'after_segment_name' => 'core',    'transition_count' => 250],
        ['before_segment_name' => 'core',    'after_segment_name' => 'middle',  'transition_count' => 30],
        ['before_segment_name' => 'dormant', 'after_segment_name' => 'dormant', 'transition_count' => 400],
        ['before_segment_name' => 'dormant', 'after_segment_name' => 'churned', 'transition_count' => 100],
        ['before_segment_name' => 'churned', 'after_segment_name' => 'churned', 'transition_count' => 1000],
    ];

    $transitions = [
  ['before_segment_name' => 'NULL',  'after_segment_name' => 'core', 'transition_count' => 2000],
  ['before_segment_name' => 'NULL',  'after_segment_name' => 'middle', 'transition_count' => 1000],
  ['before_segment_name' => 'NULL',  'after_segment_name' => 'light', 'transition_count' => 300],
  ['before_segment_name' => 'NULL',  'after_segment_name' => 'dormant', 'transition_count' => 1200],
  ['before_segment_name' => 'NULL',  'after_segment_name' => 'churned', 'transition_count' => 400],
  ['before_segment_name' => 'core', 'after_segment_name' => 'core', 'transition_count' => 100],
  ['before_segment_name' => 'core', 'after_segment_name' => 'middle', 'transition_count' => 800],
  ['before_segment_name' => 'middle', 'after_segment_name' => 'middle', 'transition_count' => 150],
  ['before_segment_name' => 'middle',  'after_segment_name' => 'light', 'transition_count' => 50],
  ['before_segment_name' => 'light', 'after_segment_name' => 'middle', 'transition_count' => 250],
  ['before_segment_name' => 'light',  'after_segment_name' => 'dormant', 'transition_count' => 30],
  ['before_segment_name' => 'dormant', 'after_segment_name' => 'dormant', 'transition_count' => 400],
  ['before_segment_name' => 'dormant', 'after_segment_name' => 'churned', 'transition_count' => 100],
  ['before_segment_name' => 'churned', 'after_segment_name' => 'churned', 'transition_count' => 111],
];

    return response()->json([
        'base' => '2025-06-16',
        'target' => '2025-06-18',
        'transitions' => $transitions
    ]);
}

}
