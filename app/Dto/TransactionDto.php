<?php

namespace App\Dto;

class TransactionDto
{
    public function __construct(
        public string $transaction_type,
        public string $transaction_uuid,
        public string $transaction_group,
        public int $type_id,
        public int $user_id
    ) {
    }
}
