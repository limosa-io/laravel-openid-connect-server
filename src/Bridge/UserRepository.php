<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\OpenID\Repositories\UserRepositoryTrait;
use Laravel\Passport\Bridge\UserRepository as LaravelUserRepository;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserRepository extends LaravelUserRepository implements UserRepositoryInterface
{

    use UserRepositoryTrait;

    /**
     * Returns an associative array with attribute (claim) keys and values
     */
    public function getAttributes(UserEntityInterface $user, $claims, $scopes)
    {
        die('to be defined');
        return null;
    }

    public function getUserByIdentifier($identifier) : ?UserEntityInterface
    {
        $provider = config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.' . $provider . '.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model)->findForPassport($identifier);
        } else {
            $user = (new $model)->where('email', $identifier)->first();
        }

        if (!$user) {
            return null;
        }

        return new User($user->getAuthIdentifier());
    }
}
