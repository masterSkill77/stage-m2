<?php

namespace App\Services;

use App\Dto\TransactionDto;
use App\Models\Transaction;

class TransactionService
{
    public function store(TransactionDto $data)
    {
        $transaction = new Transaction([
            'transaction_type' => $data->transaction_type,
            'transaction_uuid' => $data->transaction_uuid,
            'transaction_group' => $data->transaction_group,
            'type_id' => $data->type_id,
            'user_id' => $data->user_id,
        ]);
        $transaction->save();
        return $transaction;
    }
}
