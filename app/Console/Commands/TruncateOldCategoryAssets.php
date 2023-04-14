<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CategoryAssets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateOldCategoryAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'category_assets:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Old Category Assets Record from DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CategoryAssets::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->subDays(30))->each(function ($item){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $item->forceDelete();
        });
    }
}
