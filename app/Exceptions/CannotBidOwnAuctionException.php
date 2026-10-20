<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class CannotBidOwnAuctionException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'CANNOT_BID_OWN_AUCTION'], Response::HTTP_BAD_REQUEST);
    }
}
