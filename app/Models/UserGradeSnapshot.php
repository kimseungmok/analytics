<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGradeSnapshot extends Model
{
    protected $table = 'T_USER_GRADE_SNAPSHOT'; // ← 여기를 명시!
    
    // (필요 시 primaryKey, timestamps false 등도 추가)
    protected $primaryKey = 'SNAPSHOT_ID';
    public $timestamps = false;
}
