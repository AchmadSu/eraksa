<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Placements;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateOldPlacements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'placements:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Old Placements Record from DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Placements::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->subDays(30))->each(function ($item){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $item->forceDelete();
        });
    }
}
