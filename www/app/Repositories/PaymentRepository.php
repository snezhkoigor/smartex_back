<?php

namespace App\Repositories;

use App\Models\Exchange;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentRepository
 * @package App\Repositories
 */
class PaymentRepository
{
	/**
	 * @return array
	 */
	public static function getFinishedInPayments(): array
	{
		$allCount = Payment::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('type', 1)
			->first();

		$finishedCount = Payment::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('type', 1)
			->where('confirm', 1)
			->first();

		return [
			'all' => $allCount ? $allCount['count'] : 0,
			'finished' => $finishedCount ? $finishedCount['count'] : 0,
			'conversion' => $allCount ? round($finishedCount['count'] * 100 / $allCount['count'], 2) : 0
		];
	}
	
	
	/**
	 * @param $date_from
	 * @param $date_to
	 * @return array
	 */
	public static function getCurrenciesInPayments($date_from = null, $date_to = null): array
	{
		$result = [];
		$date_from = $date_from ?: Carbon::now()->subMonth()->format('Y-m-d 00:00:00');
		$date_to = $date_to ?: Carbon::now()->format('Y-m-d 23:59:59');

		$data = Payment::query()
			->select(DB::raw('currency, COUNT(id) as count'))
			->where('type', 1)
			->where('currency', '<>', '')
			->whereBetween('date', [$date_from, $date_to])
			->groupBy('currency')
			->get()
			->toArray();
		
		if ($data)
		{
			foreach ($data as $item)
			{
				$result['categories'][] = $item['currency'];
				$result['data']['data'][] = [
					'name' => $item['currency'],
		            'y' => $item['count']
				];
			}
			$result['data']['name'] = 'Currencies';
		}

		return $result;
	}


	/**
	 * @param $date_from
	 * @param $date_to
	 * @return array
	 */
	public static function getCurrenciesOutPayments($date_from = null, $date_to = null): array
	{
		$result = [];
		$date_from = $date_from ?: Carbon::now()->subMonth()->format('Y-m-d 00:00:00');
		$date_to = $date_to ?: Carbon::now()->format('Y-m-d 23:59:59');

		$data = Payment::query()
			->select(DB::raw('currency, COUNT(id) as count'))
			->where('type', 2)
			->where('currency', '<>', '')
			->whereBetween('date', [$date_from, $date_to])
			->groupBy('currency')
			->get()
			->toArray();
		
		if ($data)
		{
			foreach ($data as $item)
			{
				$result['categories'][] = $item['currency'];
				$result['data']['data'][] = [
					'name' => $item['currency'],
		            'y' => $item['count']
				];
			}
			$result['data']['name'] = 'Currencies';
		}

		return $result;
	}
}