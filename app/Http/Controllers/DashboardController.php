<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGradeSnapshot;
use App\Models\SegmentMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $date = $request->input('date') ?? Carbon::today()->toDateString();

    $gradeCounts = UserGradeSnapshot::select('SEGMENT_ID', DB::raw('COUNT(*) as total'))
      ->where('SNAPSHOT_DATE', $date)
      ->groupBy('SEGMENT_ID')
      ->pluck('total', 'SEGMENT_ID')
      ->toArray();

    $segments = SegmentMaster::where('IS_ACTIVE', 1)->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();

    $result = [];
    foreach ($segments as $id => $name) {
      $result[$name] = $gradeCounts[$id] ?? 0;
    }

    return view('dashboard.index', compact('date', 'result'));
  }
}
