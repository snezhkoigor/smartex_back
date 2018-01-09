<?php

namespace App\Services\Fractal;

use Illuminate\Http\JsonResponse;
use Spatie\Fractal\Fractal;

/**
 * Class CustomFractal
 *
 * @method static CustomFractal create($data = null, $transformer = null, $serializer = null)
 *
 * @package App\Services\Fractal
 */
class CustomFractal extends Fractal
{
	const ID_KEY = 'id';  // id всегда возвращаем
	const RELATIONS_KEY = 'relations';  // удобнее группировать связанные сущности в отдельном поле, чтобы избежать проблем с фильтрацией при использовании fieldsets

	protected $recursionLimit = 2;

//	protected $resourceName = 'data';   // переопределяем, чтобы фильтровались поля в массиве data


	// переопределяем метод, чтобы обернуть ответ в массив с ключом data и отфильтровать поля (подумать, где фильтрация полей будет уместнее)
	public function respond($statusCode = 200, $headers = [])
	{
		$this->setDefaultFieldsets();

		return parent::respond($statusCode, $headers);
	}


	// по дефолту выводим _id и связи
	private function setDefaultFieldsets()
	{
		foreach ($this->fieldsets as $key => $fieldset) {
			if (empty($fieldset)) {
				$this->fieldsets[$key] = self::ID_KEY . ',' . self::RELATIONS_KEY;
			} else {
				$this->fieldsets[$key] .= ',' . self::ID_KEY . ',' . self::RELATIONS_KEY;
			}
		}
	}
}