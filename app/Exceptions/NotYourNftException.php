<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotYourNftException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'Not your Nft'], Response::HTTP_UNAUTHORIZED);
    }
}
