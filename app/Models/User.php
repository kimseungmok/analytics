<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'T_USER'; // ← 여기를 명시!
    
    // (필요 시 primaryKey, timestamps false 등도 추가)
    protected $primaryKey = 'TICKET';
    public $timestamps = false;
}
