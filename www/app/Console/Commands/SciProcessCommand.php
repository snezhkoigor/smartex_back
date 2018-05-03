<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\Advcash\AdvcashService;
use App\Services\PayeerService;
use App\Services\PerfectMoneyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SciProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sci:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all sci answers from payment systems';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle()
    {
        $items = \DB::table('payment_answers_queue')
	        ->where('active', '=', true)
	        ->get();
        
        if (\count($items) > 0)
        {
        	foreach ($items as $item)
	        {
	        	switch ($item['ps_code'])
		        {
			        case 'pm':
			        	PerfectMoneyService::processIncomeTransaction(json_decode($item['post'], true));
			        	break;

		            case 'payeer':
			        	PayeerService::processIncomeTransaction(json_decode($item['post'], true));
			        	break;

	                case 'adv':
			        	AdvcashService::processIncomeTransaction(json_decode($item['post'], true));
			        	break;
		        }
	        }
        }
    }
}
