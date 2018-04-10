<?php

namespace App\Console\Commands;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ParseCoursesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        $url = 'https://api.gatecoin.com/Public/LiveTickers';
		$res = file_get_contents($url);
		$arr = json_decode($res);
		$a = (array)$arr;
		$courses = [];

		foreach ($a['tickers'] as $key => $value)
		{
			if (\in_array($value->currencyPair, [ 'BTCEUR', 'BTCUSD', 'ETHEUR', 'ETHUSD', 'ETHBTC', 'BTCETH' ]))
			{
				$in = substr($value->currencyPair,0,3);
				$out = substr($value->currencyPair,3,3);
				$courses[$in.$out] = [ 'in' => $in, 'out' => $out, 'course' => $value->last ];
				$courses[$out.$in] = [ 'in' => $out, 'out' => $in, 'course' => round(1/$value->last, 6) ];
			}
		}

		$must_get = [ 'CZK', 'RUB' ];
		$cb_rates = file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js');
		
		if ($cb_rates)
		{
			$rates = json_decode($cb_rates, true)['Valute'];
		
			foreach ($courses as $key => $course)
			{
				if ($course['out'] === 'EUR')
				{
					foreach ($must_get as $out_pair)
					{
						$rate = 0;
						if ($out_pair === 'RUB')
						{
							$rate = $rates[$course['out']]['Value']/$rates[$course['out']]['Nominal'];
						}
						elseif (!empty($rates[$out_pair]) && !empty($rates[$course['out']]))
						{
							$in = $rates[$course['out']]['Value']/$rates[$course['out']]['Nominal'];
							$out = $rates[$out_pair]['Value']/$rates[$out_pair]['Nominal'];
		
							$rate = round($in/$out, 4);
						}
		
						if (0 !== $rate)
						{
							$courses[$course['in'].$out_pair] = [ 'in' => $course['in'], 'out' => $out_pair, 'course' => $course['course']*$rate ];
							$courses[$out_pair.$course['in']] = [ 'in' => $out_pair, 'out' => $course['in'], 'course' => round(1/($course['course']*$rate), 6) ];
						}
					}
				}
			}
		
			$in = [ 'EUR', 'USD', 'CZK' ];
			$out = [ 'EUR', 'USD', 'RUB', 'CZK' ];
		
			foreach ($in as $in_item)
			{
				foreach ($out as $out_item)
				{
					if ($in_item !== $out_item)
					{
						if ($out_item === 'RUB')
						{
							$rate = $rates[$in_item]['Value']/$rates[$in_item]['Nominal'];
						}
						else
							{
							$rate = round(($rates[$in_item]['Value']/$rates[$in_item]['Nominal'])/($rates[$out_item]['Value']/$rates[$out_item]['Nominal']), 4);
						}
		
						if (0 !== $rate)
						{
							if (!array_key_exists($in_item.$out_item, $courses))
							{
								$courses[$in_item . $out_item] = ['in' => $in_item, 'out' => $out_item, 'course' => $rate];
							}
		
							if (!array_key_exists($out_item.$in_item, $courses))
							{
								$courses[$out_item.$in_item] = array('in' => $out_item, 'out' => $in_item, 'course' => round(1/$rate, 6));
							}
						}
					}
				}
			}
		}

		$courses = array_values($courses);

		foreach ($courses as $key => $value) {
			$item = Course::query()->where([
				['in_currency', $value['in']],
				['out_currency', $value['out']],
				['date', Carbon::today()->format('Y-m-d H:00:00')]
			])->first();

			if ($item === null) {
				$course = new Course();
				$course->date = Carbon::today()->format('Y-m-d H:00:00');
				$course->in_currency = $value['in'];
				$course->out_currency = $value['out'];
				$course->course = $value['course'];
			} else {
				$course = $item;
				$course->course = $value['course'];
			}

			$course->save();
		}
    }
}
