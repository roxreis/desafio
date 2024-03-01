<?php

namespace App\Repositories;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\ReceiverIsShopkeeperException;
use App\Exceptions\ReceiverNotIsAPayerException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\ShopkeeperRepository;
use App\Models\Transaction;
use Ramsey\Uuid\Uuid as Uuid;

class TransactionRepository
{
  protected $walletRepository;
  protected $shopKeeperRepository;

  public function __construct(
    WalletRepository $walletRepository,
    ShopkeeperRepository $shopKeeperRepository
  ) {
    $this->walletRepository = $walletRepository;
    $this->shopKeeperRepository = $shopKeeperRepository;
  }

  public function validator($request)
  {
    $receiver = $this->checkUserReceiverExist($request['receive_id']);
    if (!$receiver) {
      throw new UserNotFoundException('Receiver Not Found', 404);
    }

    $payer = $this->checkUserPayerExist($request['payer_id']);
    if (!$payer) {
       throw new UserNotFoundException('Payer Not Found', 404);
    }

    $errors = $this->checkPayerBalanceIsPositive($request['payer_id']);
    if ($errors) {
      return throw new InsufficientBalanceException('Insufficient balance to this transfer.', 422);
    }

    $autoPayment = $this->checkOrderIsASelfPayment($request);
    if ($autoPayment) {
      throw new ReceiverNotIsAPayerException('You can not self payment', 401);
    }

    $shopKeeper = $this->checkReceiverIsShopkeeper($request['payer_id']);
    if ($shopKeeper) {
      throw new ReceiverIsShopkeeperException('You are a Shopkeeper, you can not make a payment', 401);      
    }
  }

  public function makeTransaction($data, $status)
  {
    $transaction = Transaction::create([
      'id' => Uuid::uuid4()->toString(),
      'payer_wallet_id' => $data['payer_id'],
      'receive_wallet_id' => $data['receive_id'],
      'amount' => $data['value'],
    ]);

    return $transaction;
  }

  public function getAllTransactions()
  {
    return Transaction::all();
  }

  public function checkPayerBalanceIsPositive(string $payerId): bool
  {
    $payerBalance = $this->walletRepository->getBalance($payerId);

    return (intval($payerBalance) <= 0);
  }

  public function checkUserReceiverExist(string $receiverId)
  {
    return $this->walletRepository->getUserByWalledId($receiverId);
  }

  public function checkUserPayerExist(string $payerId)
  {
    return $this->walletRepository->getUserByWalledId($payerId);
  }

  public function checkOrderIsASelfPayment($request): bool
  {
    return ($request['payer_id'] === $request['receive_id']);
  }

  public function checkReceiverIsShopkeeper(string $walletPayerId)
  {
    $userId = $this->walletRepository->getUserByWalledId($walletPayerId);
    return $this->shopKeeperRepository->getShopkeeperById($userId->user_id);
  }
}
