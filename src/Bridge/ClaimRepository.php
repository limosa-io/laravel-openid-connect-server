<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\ClaimRepositoryInterface;

class ClaimRepository implements ClaimRepositoryInterface
{

    public static $scopeClaims =  [
        'profile' => ['name','family_name','given_name','middle_name', 'nickname', 'preferred_username', 'profile', 'picture', 'website', 'gender', 'birthdate', 'zoneinfo', 'locale', 'updated_at'],
        'email' => ['email','email_verified'],
        'address' => ['address'],
        'phone' => ['phone_number','phone_number_verified'],
    ];
    
    public function getClaimEntityByIdentifier($identifier, $type, $essential)
    {
        return null;
    }

    public function claimsRequestToEntities(string $json = null)
    {
        json_decode($json);

        return [];
    }
}
