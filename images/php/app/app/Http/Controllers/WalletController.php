<?php

namespace App\Http\Controllers;

use App\Repositories\WalletRepository;
use App\Services\ValidationTransactionService;

class WalletController extends Controller
{
    protected $walletRepository;
    protected $validationService;

    public function __construct(
        WalletRepository $walletRepository,
        ValidationTransactionService $validationService
        )
    {
        $this->walletRepository = $walletRepository;
        $this->validationService = $validationService;
    }
    
    public function getBalanceById(string $id)
    {
        $balance = $this->walletRepository->getBalance($id);
        return response()->json($balance);
    }


}
