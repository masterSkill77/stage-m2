<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class BidTooLowException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'BID_TOO_LOW'], Response::HTTP_BAD_REQUEST);
    }
}
