<?php

namespace App\Traits;

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait ApiResponser
{
	/**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
	protected function sendResponse($data, string $message = null, int $code = 200, $errors=[])
	{
		return response()->json([
			
			'response_code' => $code,
			'data' => $data,
			'message' => $message,
			'errors' => $errors,
		],$code);
	}

	/**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function validationError( $message = 'validation error', $errors = [], int $code=400)
	{
		return $this->sendResponse((object)[],$message,$code, $errors);
	}

}