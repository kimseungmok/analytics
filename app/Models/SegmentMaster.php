<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SegmentMaster extends Model
{
    protected $table = 'SEGMENT_MASTER'; // ← 여기를 명시!
    
    // (필요 시 primaryKey, timestamps false 등도 추가)
    protected $primaryKey = 'SEGMENT_ID';
    public $timestamps = false;
}
