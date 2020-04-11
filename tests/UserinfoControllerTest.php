<?php

namespace IdaasPassportTests;

use DateTime;
use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface as IdaasAccessTokenRepositoryInterface;
use Idaas\OpenID\Repositories\ClaimRepositoryInterface;
use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\Passport\Bridge\ClaimRepository;
use Idaas\Passport\Bridge\UserRepository;
use Idaas\Passport\KeyRepository;
use Idaas\Passport\Passport;
use Idaas\Passport\PassportServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Auth\User;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\HasApiTokens;
use Mockery as m;
use Laravel\Passport\Tests\Feature\PassportTestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface as LeagueAccessTokenRepositoryInterface;

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

        $token = (string) (new Builder())
            ->permittedFor('client')
            ->identifiedBy('1234')
            ->issuedAt(\time())
            ->canOnlyBeUsedAfter(\time())
            ->expiresAt((new DateTime("+7 day"))->getTimestamp())
            ->relatedTo('user-id-1234')
            ->withClaim('scopes', [
                new Scope('openid')
            ])
            ->withClaim('claims', ['claim1'])
            ->getToken(new Sha256(), new Key($keyRepository->getPrivateKey()->getKeyPath(), null));

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
