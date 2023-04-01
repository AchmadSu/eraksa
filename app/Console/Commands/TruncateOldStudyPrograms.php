<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\StudyPrograms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateOldStudyPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'study_programs:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Old Study Programs Record from DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        StudyPrograms::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->subDays(30))->each(function ($item){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $item->forceDelete();
        });
    }
}
