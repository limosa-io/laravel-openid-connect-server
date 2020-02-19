<?php
// TODO: not sure if this is needed
namespace Idaas\Passport\Bridge;

use Laravel\Passport\Bridge\ClientRepository as LaravelClientRepository;

class ClientRepository extends LaravelClientRepository
{
    protected function handlesGrant($record, $grantType)
    {
        $result = [];

        foreach (($record->grant_types ?? []) as $v) {
            $result[] = $v;
            $result[] = $v . '_oidc';
        }

        return $record->application_type != null && in_array($grantType, $result);
    }
}
