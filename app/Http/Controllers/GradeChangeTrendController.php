<?php

namespace App\Http\Controllers;

use App\Models\UserGradeChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeChangeTrendController extends Controller
{
  public function index(Request $request)
  {
    $start = $request->query('start');
    $end = $request->query('end');

    if (!$start || !$end) {
      return response()->json(['error' => 'start, end 日付が必要です'], 400);
    }

    $changeStats = DB::table('T_USER_GRADE_CHANGE_LOG')
      ->select(
        'SNAPSHOT_DATE',
        DB::raw("SUM(CASE WHEN CHANGE_TYPE = 'UP' THEN 1 ELSE 0 END) as up"),
        DB::raw("SUM(CASE WHEN CHANGE_TYPE = 'DOWN' THEN 1 ELSE 0 END) as down"),
        DB::raw("SUM(CASE WHEN CHANGE_TYPE = 'NEW' THEN 1 ELSE 0 END) AS new")
      )
      ->whereBetween('SNAPSHOT_DATE', [$start, $end])
      ->groupBy('SNAPSHOT_DATE');

    $snapshotStats = DB::table('T_USER_GRADE_SNAPSHOT')
      ->select('SNAPSHOT_DATE', DB::raw('COUNT(*) as total'))
      ->whereBetween('SNAPSHOT_DATE', [$start, $end])
      ->groupBy('SNAPSHOT_DATE');

    $result = DB::table(DB::raw("({$changeStats->toSql()}) as changes"))
      ->mergeBindings($changeStats)
      ->joinSub($snapshotStats, 'snapshots', 'changes.SNAPSHOT_DATE', '=', 'snapshots.SNAPSHOT_DATE')
      ->select(
        'changes.SNAPSHOT_DATE',
        'up',
        'down',
        'new',
        DB::raw('(snapshots.total - (up + down + new)) as same')
      )
      ->orderBy('changes.SNAPSHOT_DATE')
      ->get();

    return response()->json([
      'start' => $start,
      'end' => $end,
      'trend' => $result,
    ]);
  }
}
