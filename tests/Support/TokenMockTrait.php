<?php

namespace RonasIT\Clerk\Tests\Support;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder;

trait TokenMockTrait
{
    protected function createJWTToken(?string $relatedTo = null, array $claims = []): Token
    {
        $signingKey = InMemory::plainText(random_bytes(32));

        $currentDateTime = CarbonImmutable::now()->format('Y-m-d H:i:s');

        $now = new DateTimeImmutable($currentDateTime);

        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->expiresAt($now->modify('+1 hour'));

        foreach ($claims as $key => $value) {
            $tokenBuilder = $tokenBuilder->withClaim($key, $value);
        }

        if (!empty($relatedTo)) {
            $tokenBuilder = $tokenBuilder->relatedTo($relatedTo);
        }

        return $tokenBuilder
            ->getToken(
                signer: new Sha256(),
                key: $signingKey,
            );
    }
}