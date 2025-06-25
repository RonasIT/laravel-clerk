<?php

namespace RonasIT\Clerk\Tests\Support;

use Carbon\CarbonImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;

trait TokenMockTrait
{
    protected const SECRET_KEY_PATH = '/tests/private_key.pem';
    protected const SIGNER_KEY_PATH = '/tests/public_key.pem';
    protected const SECRET_KEY_PASS = 'secret_key_pass';

    protected function createJWTToken(string $relatedTo): Token
    {
        $this->generateSertificates();

        $configJwt = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(base_path(self::SECRET_KEY_PATH), self::SECRET_KEY_PASS),
            InMemory::file(base_path(self::SIGNER_KEY_PATH))
        );

        $now = CarbonImmutable::now()->toDateTimeImmutable();

        $tokenBuilder = $configJwt->builder()
            ->issuedBy('some_issuer')
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo($relatedTo);

        return $tokenBuilder->getToken(
            $configJwt->signer(),
            $configJwt->signingKey()
        );
    }

    protected function generateSertificates(): string
    {
        $privateKeyResource = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($privateKeyResource, $privateKey, self::SECRET_KEY_PASS, [
            'encrypt_key_cipher' => OPENSSL_CIPHER_AES_256_CBC
        ]);

        $publicKey = openssl_pkey_get_details($privateKeyResource);

        file_put_contents(base_path(self::SIGNER_KEY_PATH), $publicKey['key']);
        file_put_contents(base_path(self::SECRET_KEY_PATH), $privateKey);

        return self::SIGNER_KEY_PATH;
    }
}
