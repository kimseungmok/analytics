<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGradeChangeLog extends Model
{
    protected $table = 'T_USER_GRADE_CHANGE_LOG'; // ← 여기를 명시!
    
    // (필요 시 primaryKey, timestamps false 등도 추가)
    protected $primaryKey = 'LOG_ID';
    public $timestamps = false;
}
