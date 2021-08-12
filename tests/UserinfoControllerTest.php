<?php

namespace IdaasPassportTests;

use DateTime;
use DateTimeImmutable;
use Idaas\Passport\KeyRepository;
use Idaas\Passport\Passport;
use Idaas\Passport\PassportServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Auth\User;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\HasApiTokens;
use Mockery as m;
use Laravel\Passport\Tests\Feature\PassportTestCase;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
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

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($keyRepository->getPrivateKey()->getKeyContents()),
            InMemory::plainText($keyRepository->getPrivateKey()->getKeyContents())
        );

        $token = $config->builder()
            ->permittedFor('client')
            ->identifiedBy('1234')
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt(DateTimeImmutable::createFromMutable(new DateTime("+7 day")))
            ->relatedTo('user-id-1234')
            ->withClaim('scopes', [
                new Scope('openid')
            ])
            ->withClaim('claims', ['claim1'])
            ->getToken($config->signer(), $config->signingKey())
            ->toString();

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
