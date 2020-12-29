<?php

namespace IdaasPassportTests;

use DateTimeImmutable;
use Idaas\Passport\KeyRepository;
use Idaas\Passport\Passport;
use Idaas\Passport\PassportServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Auth\User;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\HasApiTokens;
use Lcobucci\JWT\Signer;
use Mockery as m;
use Laravel\Passport\Tests\Feature\PassportTestCase;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserinfoControllerTest extends PassportTestCase
{
    protected function getPackageProviders($app)
    {
        return [PassportServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $accessTokenRepository = m::mock(\Idaas\Passport\Bridge\AccessTokenRepository::class);
        $accessTokenRepository->shouldReceive('isAccessTokenRevoked')->andReturn(false);
        $accessTokenRepository->shouldReceive('getAccessToken')->andReturn(
            new AccessTokenEntity('123', [], m::mock(ClientEntityInterface::class))
        );

        $this->app->instance(
            \Idaas\Passport\Bridge\AccessTokenRepository::class,
            $accessTokenRepository
        );

        $this->withFactories(__DIR__ . '/../../database/factories');

        $this->artisan('migrate:fresh');

        Passport::routes(function ($router) {
            $router->all();
            $router->forUserinfo();
        });

        $this->artisan('passport:keys');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $config = $app->make(Repository::class);
        $config->set('database.connections.forge', [
            'driver' => 'sqlite',
            'database' => '/tmp/database.sqlite'
        ]);

        $config->set('auth.providers.users.model', TestUser::class);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testUserinfoBasic()
    {
        $keyRepository = new KeyRepository();

        $configuration = Configuration::forAsymmetricSigner(
            // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Signer\Rsa\Sha256(),
            LocalFileReference::file($keyRepository->getPrivateKey()->getKeyPath()),
            LocalFileReference::file($keyRepository->getPrivateKey()->getKeyPath())
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $token = $configuration->builder()
            ->withHeader('kid', '123')
            ->issuedBy('issuer')
            ->identifiedBy('subject')
            ->permittedFor('audience')
            ->relatedTo('subject')
            ->expiresAt(new DateTimeImmutable('+60 seconds'))
            ->issuedAt(new DateTimeImmutable)
            ->withClaim('auth_time', (new DateTimeImmutable)->getTimestamp())
            ->withClaim('nonce', 'nonce')
            ->withClaim('scopes', [
                new Scope('openid')
            ])
            ->getToken($configuration->signer(), $configuration->signingKey())->toString();

        $result = $this->get('/oauth/userinfo', [
            'Authorization' => $token
        ]);

        $result->assertStatus(200);
    }
}

class TestUser extends User
{
    use HasApiTokens;

    protected $table = 'users';

    public function findForPassport($identifier)
    {
        return new TestUser([]);
    }

    public function getAuthIdentifier()
    {
        return "test";
    }
}
