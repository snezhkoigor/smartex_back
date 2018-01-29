<?php

namespace App\Services\Advcash;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AdvcashService
{
	/**
	 * @param $id_payee
	 * @param $user
	 * @param $password
	 * @param $wallet
	 * @return mixed|null
	 *
	 * @throws \Exception
	 *
	 */
	public static function getWalletBalance($id_payee, $user, $password, $wallet)
	{
		$balance = null;
		$merchantWebService = new MerchantWebService();

		$arg0 = new authDTO();
		$arg0->apiName = $id_payee;
		$arg0->accountEmail = $user;
		$arg0->authenticationToken = $merchantWebService->getAuthenticationToken($password);

		$getBalances = new getBalances();
		$getBalances->arg0 = $arg0;

		try {
			$getBalancesResponse = $merchantWebService->getBalances($getBalances);

			foreach($getBalancesResponse->return as $key => $value) {
				$arr = (array)$value;
				if ($arr['id'] === $wallet) {
					$balance = $arr['amount'];
				}
			}
		} catch (\Exception $e) {
			throw new UnprocessableEntityHttpException($e->getMessage());
		}

		if (empty($balance)) {
			throw new UnprocessableEntityHttpException('No balance found for wallet "' . $wallet . '"');
		}

		return $balance;
	}
}