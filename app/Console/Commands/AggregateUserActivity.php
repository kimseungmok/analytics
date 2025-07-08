<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AggregateUserActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-user-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ユーザーのアクティビティ（DAU / WAU / MAU / HAU）を日次で集計する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 今日の日付を取得（Asia/Tokyo 基準）
        $today = Carbon::now('Asia/Tokyo')->startOfDay();

         // 週・月・半期の開始日を計算
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $monthStart = $today->copy()->startOfMonth();
        $halfYearStart = $today->month <= 6 ? Carbon::create($today->yaer, 1, 1) : Carbon::create($today->yaer, 7, 1);
        
        // 登録されているすべての支店コードを取得
        $branches = DB::table('branch')->pluck('branch_code');
        
        foreach($branches as $branchCode){
            // DAU (日次アクティブユーザー)
            $dau = DB::table('user_login_log')
                ->where('branch_code',$branchCode)
                ->whereDate('login_datetime',$today)
                ->distinct('user_id')
                ->count('user_id');

               // WAU (週次アクティブユーザー)
            $wau = DB::table('user_login_log')
                ->where('branch_code',$branchCode)
                ->whereDate('login_datetime',[$weekStart, $today->copy()->endOfDay()])
                ->distinct('user_id')
                ->count('user_id');

        }

        $date = now()->toDateString();
        DB::statement("
            DECLARE @base_date DATE = '$date';
            -- IF 2025-06-10
            DECLARE @base_date DATE = '2025-06-10';

            -- 기준일 속한 주의 월요일
            DECLARE @week_start DATE = DATEADD(DAY, 1 - DATEPART(WEEKDAY, @base_date), @base_date);
            -- 기준일 속한 달의 첫날
            DECLARE @month_start DATE = DATEFROMPARTS(YEAR(@base_date), MONTH(@base_date), 1);
            -- 기준일 포함 반기의 첫날 (1~6월 or 7~12월)
            DECLARE @halfyear_start DATE = DATEFROMPARTS(YEAR(@base_date), IIF(MONTH(@base_date) <= 6, 1, 7), 1);

            SELECT
                branch_code,
                
                -- DAU: 기준일 로그인 유저 수
                COUNT(DISTINCT CASE WHEN CONVERT(DATE, login_datetime) = @base_date THEN user_id END) AS dau,

                -- WAU: 기준 주 로그인 유저 수
                COUNT(DISTINCT CASE WHEN login_datetime >= @week_start AND login_datetime < DATEADD(DAY, 7, @week_start) THEN user_id END) AS wau,

                -- MAU: 기준 월 로그인 유저 수
                COUNT(DISTINCT CASE WHEN login_datetime >= @month_start AND login_datetime < DATEADD(MONTH, 1, @month_start) THEN user_id END) AS mau,

                -- HAU (Half-yearly AU): 반기별
                COUNT(DISTINCT CASE WHEN login_datetime >= @halfyear_start AND login_datetime < DATEADD(MONTH, 6, @halfyear_start) THEN user_id END) AS hau

            FROM user_login_log
            WHERE login_datetime < DATEADD(DAY, 1, @base_date)
            GROUP BY branch_code;
        ");
        //
    }
}
