<?php

namespace App\Services;

use App\Models\GradeSnapshot;
use App\Models\SegmentMaster;
use App\Services\BranchService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GradeAnalyticsService
{
  protected $branchService;

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

  public function __construct(BranchService $branchService)
  {
    $this->branchService = $branchService;
  }

  public function getKpiComparison(Carbon $currentDate, Carbon $previousDate, $branchIds): array
  {
    // 1) 기본 Active/累積離反 집계용 함수 (모델 scope 활용)
    $baseQuery = function (Carbon $date) use ($branchIds) {
      return GradeSnapshot::query()
        ->bySnapshotDate($date)
        ->byBranches($branchIds);
    };

    $currBaseQuery = $baseQuery($currentDate);
    $prevBaseQuery = $baseQuery($previousDate);

    $currBase = (clone $currBaseQuery)
      ->selectRaw('COUNT(CASE WHEN SEGMENT_ID IN (1,2,3) THEN 1 END) AS active')
      ->selectRaw('COUNT(CASE WHEN SEGMENT_ID = 5 THEN 1 END) AS churned_cum')
      ->first();

    $prevBase = (clone $prevBaseQuery)
      ->selectRaw('COUNT(CASE WHEN SEGMENT_ID IN (1,2,3) THEN 1 END) AS active')
      ->selectRaw('COUNT(CASE WHEN SEGMENT_ID = 5 THEN 1 END) AS churned_cum')
      ->first();

    // 2) 新規ユーザー 집계
    $newUsersCount = GradeSnapshot::query()
      ->bySnapshotDate($currentDate)
      ->byBranches($branchIds)
      ->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->whereNotIn('MEMBER_ID', function ($query) use ($previousDate, $branchIds) {
        $query->select('MEMBER_ID')
          ->from('T_GRADE_SNAPSHOT')
          ->whereDate('SNAPSHOT_DATE', $previousDate);
        if (!empty($branchIds)) {
          $query->whereIn('LAST_VISITED_SHOP', $branchIds);
        }
      })
      ->count();

    $newUserRate = $currBase->active > 0 ? $newUsersCount / $currBase->active * 100 : 0;

    // 3) 승격·유지·감퇴·분모(이전アクティブ) 집계
    $joinQuery = GradeSnapshot::query()
      ->from('T_GRADE_SNAPSHOT as prev')
      ->join('T_GRADE_SNAPSHOT as curr', function ($join) use ($previousDate, $currentDate, $branchIds) {
        $join->on('prev.MEMBER_ID', '=', 'curr.MEMBER_ID')
          ->whereDate('prev.SNAPSHOT_DATE', $previousDate)
          ->whereDate('curr.SNAPSHOT_DATE', $currentDate);

        if (!empty($branchIds)) {
          $join->whereIn('prev.LAST_VISITED_SHOP', $branchIds);
          $join->whereIn('curr.LAST_VISITED_SHOP', $branchIds);
        }
      });

    $t = $joinQuery->select([
      DB::raw('SUM(CASE WHEN prev.SEGMENT_ID IN (4,5) AND curr.SEGMENT_ID IN (1,2,3) THEN 1 ELSE 0 END) AS promoted'),
      DB::raw('SUM(CASE WHEN prev.SEGMENT_ID IN (1,2,3) AND curr.SEGMENT_ID IN (1,2,3) THEN 1 ELSE 0 END) AS retained'),
      DB::raw('SUM(CASE WHEN prev.SEGMENT_ID IN (1,2,3) AND curr.SEGMENT_ID IN (4,5) THEN 1 ELSE 0 END) AS demoted'),
      DB::raw('COUNT(CASE WHEN prev.SEGMENT_ID IN (1,2,3) THEN prev.MEMBER_ID END) AS prev_active_count'),
    ])->first();

    $prevActiveCount = $t->prev_active_count ?: 0;
    $promotedCount = $t->promoted;
    $retainedCount = $t->retained;
    $demotedCount = $t->demoted;

    $promotionRate = $prevActiveCount > 0 ? $promotedCount / $prevActiveCount * 100 : 0;
    $retentionRate = $prevActiveCount > 0 ? $retainedCount / $prevActiveCount * 100 : 0;
    $churnRate = $prevActiveCount > 0 ? $demotedCount / $prevActiveCount * 100 : 0;

    // 4) 累積離反 증감
    $activeUsersChange = $currBase->active - $prevBase->active;
    $churnedUsersCumulativeChange = $currBase->churned_cum - $prevBase->churned_cum;

    // 5) KPI 배열 구성
    return [
      [
        'metric'     => '新規ユーザー（当月）',
        'value'      => number_format($newUsersCount) . '人',
        'change'     => sprintf('%.1f%%', $newUserRate),
        'note'       => $previousDate->format('Y-m-d') . '〜' . $currentDate->format('Y-m-d'),
        'changeType' => 'positive',
      ],
      [
        'metric'     => 'アクティブユーザー数（当月）',
        'value'      => number_format($currBase->active) . '人',
        'change'     => ($activeUsersChange >= 0 ? '+' : '') . number_format($activeUsersChange) . '人',
        'note'       => '直近365日内1回以上',
        'changeType' => $activeUsersChange >= 0 ? 'positive' : 'negative',
      ],
      [
        'metric'     => '昇格率',
        'value'      => number_format($promotedCount) . '人',
        'change'     => sprintf('%.1f%%', $promotionRate),
        'note'       => 'T1非アクティブ→T2アクティブ',
        'changeType' => 'positive',
      ],
      [
        'metric'     => '維持率',
        'value'      => number_format($retainedCount) . '人',
        'change'     => sprintf('%.1f%%', $retentionRate),
        'note'       => 'T1アクティブ→T2アクティブ',
        'changeType' => 'neutral',
      ],
      [
        'metric'     => '流出率',
        'value'      => number_format($demotedCount) . '人',
        'change'     => sprintf('%.1f%%', $churnRate),
        'note'       => 'T1アクティブ→T2非アクティブ',
        'changeType' => 'negative',
      ],
      [
        'metric'     => '離反ユーザー数（累積）',
        'value'      => number_format($currBase->churned_cum) . '人',
        'change'     => ($churnedUsersCumulativeChange >= 0 ? '+' : '') . number_format($churnedUsersCumulativeChange) . '人',
        'note'       => '直近730日利用なし',
        'changeType' => $churnedUsersCumulativeChange >= 0 ? 'negative' : 'positive',
      ],
    ];
  }









































  public function getSegmentMigrationMatrix(Carbon $currentDate, Carbon $previousDate, array $branchIds = []): array
  {
    $translationMap = [
      'core'    => 'コア',
      'middle'  => 'ミドル',
      'light'   => 'ライト',
      'dormant' => '休眠',
      'churned' => '離反',
      'never'   => '利用なし',
      'new'     => '新規',
      'other_branch' => '他店舗',
    ];

    // 1) 세그먼트 목록 조회 및 정렬
    $segments = DB::table('SEGMENT_MASTER')
      ->orderBy('SEGMENT_ID')
      ->get(['SEGMENT_ID', 'SEGMENT_NAME']);

    $segmentNamesRaw = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentIdsOrdered = $segments->pluck('SEGMENT_ID')->map(fn($id) => (string)$id)->toArray();

    // 'new' 및 '他店舗' 키 조건부 추가
    $segmentNamesRaw['new'] = 'new';
    $segmentIdsOrdered[] = 'new';

    $includeOtherBranch = !empty($branchIds);
    if ($includeOtherBranch) {
      unset($segmentNamesRaw[6]);
      $segmentIdsOrdered = array_diff($segmentIdsOrdered, ['6']);
      
      $segmentNamesRaw['other_branch'] = 'other_branch';
      $segmentIdsOrdered[] = 'other_branch';
    }

    // 2) 헤더 번역
    $translated = [];
    foreach ($segmentIdsOrdered as $id) {
      $name = is_array($segmentNamesRaw[$id]) ? ($segmentNamesRaw[$id]['name'] ?? 'unknown') : $segmentNamesRaw[$id];
      $translated[$id] = [
        'id'   => $id,
        'name' => $translationMap[strtolower($name)] ?? $name,
      ];
    }

    // 3) 행/열 ID 구성
    $rowIds = $segmentIdsOrdered;
    $colIds = array_filter($rowIds, fn($id) => $id !== 'new');

    // 4) 매트릭스 초기화
    $matrix = [];
    foreach ($rowIds as $prev) {
      foreach ($colIds as $curr) {
        $matrix[(string)$prev][(string)$curr] = 0;
      }
    }

    // 5) 기본 전환 쿼리
    $transitions = GradeSnapshot::from('T_GRADE_SNAPSHOT AS cur')
      ->leftJoin('T_GRADE_SNAPSHOT as prev', function ($join) use ($previousDate, $branchIds) {
        $join->on('cur.MEMBER_ID', '=', 'prev.MEMBER_ID')
          ->whereDate('prev.SNAPSHOT_DATE', $previousDate);
        if (!empty($branchIds)) {
          $join->whereIn('prev.LAST_VISITED_SHOP', $branchIds);
        }
      })
      ->when(!empty($branchIds), function ($query) use ($branchIds) {
        $query->whereIn('cur.LAST_VISITED_SHOP', $branchIds);
      })
      ->whereDate('cur.SNAPSHOT_DATE', $currentDate)
      ->select([
        DB::raw('prev.SEGMENT_ID as previous_segment_id'),
        DB::raw('cur.SEGMENT_ID AS current_segment_id'),
        DB::raw('COUNT(cur.MEMBER_ID) AS user_count'),
      ])
      ->groupBy('prev.SEGMENT_ID', 'cur.SEGMENT_ID')
      ->get();

    foreach ($transitions as $t) {
      $prev = $t->previous_segment_id === null ? 'new' : (string)$t->previous_segment_id;
      $curr = (string)$t->current_segment_id;
      $matrix[$prev][$curr] += (int)$t->user_count;
    }

    // 6) 他店舗 → 유입
    if ($includeOtherBranch) {
      $inflow = GradeSnapshot::from('T_GRADE_SNAPSHOT AS cur')
        ->join('T_GRADE_SNAPSHOT as prev', function ($join) use ($previousDate) {
          $join->on('cur.MEMBER_ID', '=', 'prev.MEMBER_ID')
            ->whereDate('prev.SNAPSHOT_DATE', $previousDate);
        })
        ->whereDate('cur.SNAPSHOT_DATE', $currentDate)
        ->whereIn('cur.LAST_VISITED_SHOP', $branchIds)
        ->whereNotIn('prev.LAST_VISITED_SHOP', $branchIds)
        ->select([
          DB::raw('cur.SEGMENT_ID AS current_segment_id'),
          DB::raw('COUNT(cur.MEMBER_ID) AS user_count'),
        ])
        ->groupBy('cur.SEGMENT_ID')
        ->get();

      foreach ($inflow as $i) {
        $curr = (string)$i->current_segment_id;
        $matrix['other_branch'][$curr] += (int)$i->user_count;
      }

      // 7) 他店舗 ← 유출
      $outflow = GradeSnapshot::from('T_GRADE_SNAPSHOT AS cur')
        ->join('T_GRADE_SNAPSHOT as prev', function ($join) use ($previousDate) {
          $join->on('cur.MEMBER_ID', '=', 'prev.MEMBER_ID')
            ->whereDate('prev.SNAPSHOT_DATE', $previousDate);
        })
        ->whereDate('cur.SNAPSHOT_DATE', $currentDate)
        ->whereNotIn('cur.LAST_VISITED_SHOP', $branchIds)
        ->whereIn('prev.LAST_VISITED_SHOP', $branchIds)
        ->select([
          DB::raw('prev.SEGMENT_ID AS previous_segment_id'),
          DB::raw('COUNT(cur.MEMBER_ID) AS user_count'),
        ])
        ->groupBy('prev.SEGMENT_ID')
        ->get();

      foreach ($outflow as $o) {
        $prev = (string)$o->previous_segment_id;
        $matrix[$prev]['other_branch'] += (int)$o->user_count;
      }
    }

    // 8) 합계 계산
    $rowTotals = [];
    $colTotals = array_fill_keys($colIds, 0);
    $grandTotal = 0;

    foreach ($rowIds as $prev) {
      $sumRow = 0;

      // 他店舗 행은 합계 계산에서 제외
      if ($prev === 'other_branch') continue;

      foreach ($colIds as $curr) {
        if ($curr === 'other_branch') continue; // 他店舗 열 제외
        $sumRow += $matrix[$prev][$curr];
        $colTotals[$curr] += $matrix[$prev][$curr];
      }

      $rowTotals[$prev] = $sumRow;
      $grandTotal += $sumRow;
    }

    // 9) 포맷팅
    $formatted = [];
    foreach ($rowIds as $prev) {
      $row = [];
      foreach ($colIds as $curr) {
        $row[] = number_format($matrix[$prev][$curr]);
      }
      $row[] = isset($rowTotals[$prev]) ? number_format($rowTotals[$prev]) : '0';
      $formatted[] = $row;
    }

    // 10) 열합계 행
    $totalRow = [];
    foreach ($colIds as $curr) {
      $totalRow[] = number_format($colTotals[$curr]);
    }
    $totalRow[] = number_format($grandTotal);
    $formatted[] = $totalRow;

    // 11) 헤더 구성
    $totalT1Header = ['id' => 'total', 'name' => '合計(T1)'];
    $totalT2Header = ['id' => 'total', 'name' => '合計(T2)'];

    $rowHeaders = array_map(fn($id) => $translated[$id], $rowIds);
    $rowHeaders[] = $totalT2Header;

    $colHeaders = array_map(fn($id) => $translated[$id], $colIds);
    $colHeaders[] = $totalT1Header;

    return [
      'previous_date' => $previousDate->format('Y-m-d'),
      'current_date'  => $currentDate->format('Y-m-d'),
      'row_headers' => array_values($rowHeaders),
      'col_headers' => array_values($colHeaders),
      'matrix_data'   => $formatted,
    ];
  }



















































  public function getSegmentComposition(Carbon $snapshotDate, $branchIds): array
  {
    $segments = DB::table('SEGMENT_MASTER')
      //->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->orderBy('SEGMENT_ID')
      ->get(['SEGMENT_ID', 'SEGMENT_NAME']);

    //$segmentNames = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentNamesRaw = $segments->pluck('SEGMENT_NAME', 'SEGMENT_ID')->toArray();
    $segmentIdsOrdered = $segments->pluck('SEGMENT_ID')->toArray();

    $translatedSegmentHeaders = [];
    foreach ($segmentIdsOrdered as $id) {
      $name = $segmentNamesRaw[$id] ?? null;
      if ($name) {
        $translatedSegmentHeaders[$id] = $this->translationMap[strtolower($name)];
      }
    }

    $query = GradeSnapshot::from('T_GRADE_SNAPSHOT AS gs')
      ->join('T_MEMBER_INFO AS tmi', 'gs.MEMBER_ID', '=', 'tmi.ID')
      ->byBranches($branchIds, 'gs')
      ->bySnapshotDate($snapshotDate, 'gs');
    //->whereIn('gs.SEGMENT_ID', $this->relevantSegmentIds);

    $ageGroupSql = "
      CASE
        WHEN tmi.BIRTHDAY IS NULL THEN '不明'
      ELSE
        CASE
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 10 AND 19 THEN '10代'
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 20 AND 29 THEN '20代'
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 30 AND 39 THEN '30代'
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 40 AND 49 THEN '40代'
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') BETWEEN 50 AND 59 THEN '50代'
          WHEN DATEDIFF(year, tmi.BIRTHDAY, '{$snapshotDate->format('Y-m-d')}') >= 60 THEN '60代以上'
          ELSE '不明'
        END
      END
    ";

    $genderSql = "
      CASE
        WHEN tmi.SEX = 0 THEN '男性'
        WHEN tmi.SEX = 1 THEN '女性'
        ELSE 'その他'
      END
    ";

    $selectColumns = [
      DB::raw("{$ageGroupSql} AS age_group_val"),
      DB::raw("{$genderSql} AS gender_val"),
      'gs.SEGMENT_ID',
      DB::raw('COUNT(gs.MEMBER_ID) AS user_count')
    ];
    $groupByColumns = [DB::raw($ageGroupSql), DB::raw($genderSql), 'gs.SEGMENT_ID'];

    $results = $query
      ->select($selectColumns)
      ->groupBy($groupByColumns)
      ->get();

    $tempComposition = [];
    $totalUsersByAttributeCombination = [];
    foreach ($results as $row) {
      $key = $row->age_group_val . '_' . $row->gender_val;
      $totalUsersByAttributeCombination[$key] = ($totalUsersByAttributeCombination[$key] ?? 0) + $row->user_count;
    }

    foreach ($totalUsersByAttributeCombination as $key => $totalCount) {
      // 키에서 연령대와 성별을 다시 추출 (예: "10代_男性" -> "10代", "男性")
      list($ageGroup, $gender) = explode('_', $key, 2);

      $tempComposition[$key] = [
        '年代' => $ageGroup,
        '性別' => $gender,
      ];

      // 모든 세그먼트 비율을 0.0%로 초기화
      foreach ($translatedSegmentHeaders as $segmentName) {
        $tempComposition[$key]["{$segmentName}"] = sprintf('%.1f%%', 0);
      }
    }

    foreach ($results as $row) {
      $key = $row->age_group_val . '_' . $row->gender_val;
      $segmentName = $translatedSegmentHeaders[$row->SEGMENT_ID];
      $ratio = $totalUsersByAttributeCombination[$key] > 0 ? ($row->user_count / $totalUsersByAttributeCombination[$key]) * 100 : 0;
      $tempComposition[$key]["{$segmentName}"] = sprintf('%.1f%%', $ratio);
    }

    $ageGroupOrder = ['10代', '20代', '30代', '40代', '50代', '60代以上', '不明'];
    $genderOrder = ['男性', '女性', 'その他'];

    usort($tempComposition, function ($a, $b) use ($ageGroupOrder, $genderOrder) {
      $ageGroupA = $a['年代'];
      $ageGroupB = $b['年代'];
      $genderGroupA = $a['性別'];
      $genderGroupB = $b['性別'];

      $ageGroupCompare = array_search($ageGroupA, $ageGroupOrder) <=> array_search($ageGroupB, $ageGroupOrder);
      if ($ageGroupCompare !== 0) {
        return $ageGroupCompare;
      }

      return array_search($genderGroupA, $genderOrder) <=> array_search($genderGroupB, $genderOrder);
    });

    $formattedComposition = array_values($tempComposition);

    $finalHeaders = ['年代', '性別'];
    foreach ($translatedSegmentHeaders as $segmentName) {
      $finalHeaders[] = "{$segmentName}";
    }

    return [
      'headers' => $finalHeaders,
      'data' => $formattedComposition,
    ];
  }

















































  public function getSegmentSummary(Carbon $snapshotDate, $branchIds): array
  {
    $segmentCounts = GradeSnapshot::query()
      ->select('SEGMENT_ID', DB::raw('COUNT(SEGMENT_ID) AS cnt'))
      ->byBranches($branchIds)
      ->bySnapshotDate($snapshotDate)
      //->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->groupBy('SEGMENT_ID')
      ->pluck('cnt', 'SEGMENT_ID')
      ->toArray();

    $segmentMaster = GradeSnapshot::from('SEGMENT_MASTER')
      //->whereIn('SEGMENT_ID', $this->relevantSegmentIds)
      ->get(['SEGMENT_ID', 'SEGMENT_NAME', 'DESCRIPTION'])
      ->keyBy('SEGMENT_ID')
      ->toArray();

    $getSegmentCount = function ($segmentId) use ($segmentCounts) {
      return $segmentCounts[$segmentId] ?? 0;
    };

    $getSegmentDescription = function ($segmentId) use ($segmentMaster) {
      return $segmentMaster[$segmentId]['DESCRIPTION'] ?? '';
    };

    $coreUsers = $getSegmentCount(1);
    $middleUsers = $getSegmentCount(2);
    $lightUsers = $getSegmentCount(3);
    $dormantUsers = $getSegmentCount(4);
    $churnedUsers = $getSegmentCount(5);
    $neverUsers = $getSegmentCount(6);

    $activeUsers = $coreUsers + $middleUsers + $lightUsers;
    $inactiveUsers = $dormantUsers + $churnedUsers;
    $conversionUsers = $activeUsers + $inactiveUsers;

    $data = [
      'conversion' => [
        'name' => 'コンバージョンユーザー',
        'count' => number_format($conversionUsers),
        'description' => '利用があった人',
      ],
      'active' => [
        'name' => 'アクティブユーザー',
        'count' => number_format($activeUsers),
        'description' => '直近365日の内、1度でも利用があったユーザー',
      ],
      'core' => [
        'name' => 'コアユーザー',
        'count' => number_format($coreUsers),
        'description' => $getSegmentDescription(1),
      ],
      'middle' => [
        'name' => 'ミドルユーザー',
        'count' => number_format($middleUsers),
        'description' => $getSegmentDescription(2),
      ],
      'light' => [
        'name' => 'ライトユーザー',
        'count' => number_format($lightUsers),
        'description' => $getSegmentDescription(3),
      ],
      'inactive' => [
        'name' => '非アクティブユーザー',
        'count' => number_format($inactiveUsers),
        'description' => '直近365日の内、1度も利用がないユーザー',
      ],
      'dormant' => [
        'name' => '休眠ユーザー',
        'count' => number_format($dormantUsers),
        'description' => $getSegmentDescription(4),
      ],
      'churned' => [
        'name' => '離反ユーザー',
        'count' => number_format($churnedUsers),
        'description' => $getSegmentDescription(5),
      ],
      'never' => [
        'name' => '非コンバージョンユーザー',
        'count' => number_format($neverUsers),
        'description' => $getSegmentDescription(6),
      ],
    ];

    return $data;
  }
}
