<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AlreadyFriendException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'ALREADY_FRIEND'], Response::HTTP_BAD_REQUEST);
    }
}
