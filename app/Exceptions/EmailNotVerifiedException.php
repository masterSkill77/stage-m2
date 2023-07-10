<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class EmailNotVerifiedException extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'VERIFY_YOUR_EMAIL'], Response::HTTP_FORBIDDEN);
    }
}
