<?php

namespace RonasIT\Clerk\Traits;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;

trait TokenMockTrait
{
    protected const string SECRET_KEY_PASS = 'secret_key_pass';

    protected function createJWTToken(string $relatedTo, string $issuer = 'issuer', array $claims = []): Token
    {
        list($signerCert, $privateCert) = $this->generateCertificates();

        Config::set('clerk.signer_key', $signerCert);

        $configJwt = Configuration::forAsymmetricSigner(
            signer: new Sha256(),
            signingKey: InMemory::plainText($privateCert, self::SECRET_KEY_PASS),
            verificationKey: InMemory::plainText($signerCert),
        );

        $now = CarbonImmutable::now()->toDateTimeImmutable();

        $builder = $configJwt->builder();

        foreach ($claims as $name => $value) {
            $builder = $builder->withClaim($name, $value);
        }

        return $builder
            ->issuedBy($issuer)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo($relatedTo)
            ->getToken($configJwt->signer(), $configJwt->signingKey());
    }

    protected function generateCertificates(): array
    {
        $privateKeyResource = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($privateKeyResource, $privateKey, self::SECRET_KEY_PASS, [
            'encrypt_key_cipher' => OPENSSL_CIPHER_AES_256_CBC,
        ]);

        $publicKey = openssl_pkey_get_details($privateKeyResource);

        return [
            $publicKey['key'],
            $privateKey,
        ];
    }
}
