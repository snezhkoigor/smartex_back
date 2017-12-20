<?php

namespace App\Http\Controllers\Api;

use App\Models\Dictionary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class DictionaryController extends Controller
{
	public function index()
    {
	    return response()->json([
	    	'currencies' => Dictionary::getCurrencies(),
		    'const' => ''
	    ], Response::HTTP_OK);
    }
}
