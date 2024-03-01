<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\ReceiverIsShopkeeperException;
use App\Exceptions\ReceiverNotIsAPayerException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Services\MockyService;
use App\Services\ValidationTransactionService;
use Illuminate\Http\Request as HttpRequest;

class TransactionController extends Controller
{
    protected $transactionRepository;
    protected $validationService;
    protected $mockyService;
    protected $walletRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        ValidationTransactionService $validationService,
        MockyService $mockyService,
        WalletRepository $walletRepository,
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->validationService = $validationService;
        $this->mockyService = $mockyService;
        $this->walletRepository = $walletRepository;
    }

    public function listTransactions()
    {
        $transactions = $this->transactionRepository->getAllTransactions();
        return response()->json($transactions);
    }

    public function transactionValidator(HttpRequest $request)
    {
        $response = $this->getAuthorizationToTransaction($request);
        if ($response['message'] !== 'Autorizado') {
            return response()->json([$response], 422);
        }

        try {
            $fields = $request->only(['payer_id', 'receive_id', 'value']);
            $this->transactionRepository->validator($fields);
            return $this->handleWallet($request);
        } catch (ReceiverIsShopkeeperException | InsufficientBalanceException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], $exception->getCode());
        } catch (UserNotFoundException | ReceiverNotIsAPayerException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], $exception->getCode());
        } catch (ReceiverNotIsAPayerException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], $exception->getCode());
        }
    }

    private function getAuthorizationToTransaction($request)
    {
        $errors = $this->validationService->validateRequest($request);
        if ($errors) {
            return ['message' => 'Service is not responding. Try again later.'];
        }

        return $this->mockyService->authorizeTransaction();
    }

    protected function handleWallet($request)
    {
        $debit = $this->walletRepository->walletTransaction($request['payer_id'], $request['receive_id'], $request['value']);
        if (isset($debit['ErrorMessage'])) {
            return response()->json(['message' => 'Error, transfer was not possible!'], 400);;
        }

        return $this->transaction($request);
    }

    protected function transaction($request)
    {
        $transaction = $this->transactionRepository->makeTransaction($request, 'Success');
        if ($transaction) {
            $response = $this->sendNoticicationToReceiverPayment();
            if (is_bool($response['message'])) {
                return response()->json(['message' => 'Success!'], 200);
            }
        }
    }

    public function sendNoticicationToReceiverPayment()
    {
        return $this->mockyService->notifyUser();
    }
}
