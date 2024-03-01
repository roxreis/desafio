<?php

namespace App\Http\Controllers;

use App\Models\Shopkeeper;
use App\Repositories\ShopkeeperRepository;
use App\Repositories\UserRepository;

class MainController extends Controller
{
    protected $userRepository;
    protected $shopkeeperRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, ShopkeeperRepository $shopkeeperRepository)
    {
        $this->userRepository = $userRepository;
        $this->shopkeeperRepository = $shopkeeperRepository;
    }

    public function listUsers()
    {
        $users = $this->userRepository->getAllUsers();
        return response()->json($users);
    }

    public function listShopkeepers()
    {
        $users = $this->shopkeeperRepository->getAllShopkeepers();
        return response()->json($users);
    }        
}
