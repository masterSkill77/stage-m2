<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuctionNotOpenException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'AUCTION_NOT_OPEN'], Response::HTTP_BAD_REQUEST);
    }
}
