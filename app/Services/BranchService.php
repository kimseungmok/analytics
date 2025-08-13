<?php

namespace App\Services;

use App\Models\Branch;

class BranchService
{
  public static function getAvailableBranches()
  {
    return Branch::on('sqlsrv_192.168.140.241')
      ->select(['店舗番号 as id', '名称 as name'])
      ->where('IP', '<>', '')
      ->whereBetween('店舗番号', [1001, 1999])
      ->orderBy('店舗番号')
      ->get();
  }
}
