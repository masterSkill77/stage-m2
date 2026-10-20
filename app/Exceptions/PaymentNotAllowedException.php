<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class PaymentNotAllowedException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'PAYMENT_NOT_ALLOWED'], Response::HTTP_FORBIDDEN);
    }
}
