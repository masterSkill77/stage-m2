<?php

namespace App\Services;

use App\Models\Pack;

class PackService
{
    public function listsOfPack()
    {
        return Pack::where('is_active', true)->orderBy('pack_price', 'ASC')->get();
    }
    public function store(array $data)
    {
        return (new Pack($data))->save();
    }
}
