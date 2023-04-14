<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Old Users Record from DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->subDays(30))->each(function ($item){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $item->forceDelete();
        });
    }
}
