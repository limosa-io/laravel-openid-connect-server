<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\ClaimRepositoryInterface;

class ClaimRepository implements ClaimRepositoryInterface
{

    public static $scopeClaims =  [
        'profile' => ['name', 'family_name', 'given_name', 'middle_name', 'nickname', 'preferred_username', 'profile', 'picture', 'website', 'gender', 'birthdate', 'zoneinfo', 'locale', 'updated_at'],
        'email' => ['email', 'email_verified'],
        'address' => ['address'],
        'phone' => ['phone_number', 'phone_number_verified'],
    ];

    public function getScopeClaims()
    {
        return self::$scopeClaims;
    }

    public function getClaimEntityByIdentifier($identifier, $type, $essential)
    {
        return new ClaimEntity($identifier, $type, $essential);
    }

    public function getClaimsByScope(string $scope): iterable
    {
        $result = [];

        $map = $this->getScopeClaims();

        if (isset($map[$scope])) {
            foreach ($map[$scope] as $claim) {
                $result[] = new ClaimEntity($claim, null, null);
            }
        }

        return $result;
    }

    public function claimsRequestToEntities(array $json = null)
    {
        $result = [];

        foreach ([ClaimEntity::TYPE_ID_TOKEN, ClaimEntity::TYPE_USERINFO] as $type) {
            if ($json != null && isset($json[$type])) {
                foreach ($json[$type] as $claim => $properties) {
                    $result[] = new ClaimEntity(
                        $claim,
                        $type,
                        isset($properties) && isset($properties['essential']) ? $properties['essential'] : false
                    );
                }
            }
        }

        return $result;
    }
}
