<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SegmentMaster;
use App\Models\UserGradeSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SegmentDistributionController extends Controller
{
    public function index(Request $request)
    {
        // 1) 날짜 쿼리 파라미터 받기, 없으면 오늘 날짜
        $date = $request->query('date', Carbon::today()->toDateString());

        // 2) 활성화된 세그먼트 목록 조회 (segment_id, segment_name)
        $segments = SegmentMaster::where('IS_ACTIVE', 1)
                    ->pluck('SEGMENT_NAME', 'SEGMENT_ID');  // ['segment_id' => 'segment_name']

        // 3) 해당 날짜에 대한 등급별 유저 수 집계
        // segment_id 별로 count 집계하여 [segment_id => count] 형태로 가져오기
        $counts = UserGradeSnapshot::select('SEGMENT_ID', DB::raw('COUNT(*) as total'))
            ->where('SNAPSHOT_DATE', $date)
            ->groupBy('SEGMENT_ID')
            ->pluck('total', 'SEGMENT_ID');

        // 4) 세그먼트 이름별로 결과 배열 생성
        $result = [];
        foreach ($segments as $id => $name) {
            // 해당 세그먼트의 유저 수가 없으면 0으로 처리
            $result[$name] = $counts[$id] ?? 0;
        }

        // 5) JSON 형식으로 결과 반환
        return response()->json([
            'date' => $date,
            'distribution' => $result,
        ]);
    }
}
