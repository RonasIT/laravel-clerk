<?php

namespace RonasIT\Clerk\Tests\Support;

use Illuminate\Http\Request;

trait ClerkGuardTestTrait
{
    protected function generateRequest(array $headers): Request
    {
        $request = new Request();

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }
}
