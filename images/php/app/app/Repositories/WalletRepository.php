<?php

namespace App\Repositories;

use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletRepository
{
  public function getBalance(string $id)
  {
    return Wallet::select('balance')->where('id', $id)->first()->balance;
  }

  public function getUserByWalledId(string $id)
  {
    return Wallet::find($id);
  }

  public function walletTransaction($payerId, $receiverId, $value)
  {
    try {
      DB::table('wallets')->where('id', $payerId)->decrement('balance', $value);
      DB::table('wallets')->where('id', $receiverId)->increment('balance', $value);
      DB::commit();
    } catch (\Exception $e) {
      DB::rollback();
      error_log($e->getMessage());
      return ['ErrorMessage' => 'Transaction fail'];
    }
  }
}
