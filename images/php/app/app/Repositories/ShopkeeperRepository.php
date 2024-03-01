<?php

namespace App\Repositories;

use App\Interfaces\ShopkeeperRepositoryInterface;
use App\Models\Shopkeeper;

class ShopkeeperRepository implements ShopkeeperRepositoryInterface
{
  public function getAllShopkeepers()
  {
    return Shopkeeper::all();
  }

  public function getShopkeeperById(string $id)
  {
    return Shopkeeper::where('id', $id)->first();
  }
}
