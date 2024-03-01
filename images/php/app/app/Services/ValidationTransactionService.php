<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ValidationTransactionService
{
  public function validateRequest(Request $request)
  {
    $rules = [
      'payer_id' => 'required|string',
      'receive_id' => 'required|string',
      'value'    => 'required|numeric'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return $validator->errors();
    }

    return null;
  }
}