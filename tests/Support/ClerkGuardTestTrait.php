<?php

namespace RonasIT\Clerk\Tests\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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

    protected function persistSignerKeyToFile(): string
    {
        $relativePath = 'storage/framework/testing/clerk_key.pem';
        $absolutePath = base_path($relativePath);

        File::ensureDirectoryExists(dirname($absolutePath));

        File::put($absolutePath, base64_decode(Config::get('clerk.signer_key'), true));

        $this->beforeApplicationDestroyed(fn () => File::delete($absolutePath));

        return $relativePath;
    }
}
