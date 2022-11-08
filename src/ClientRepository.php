<?php

namespace Idaas\Passport;

use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository as LaravelClientRepository;

class ClientRepository extends LaravelClientRepository
{
    public function all()
    {
        $client = Passport::client();
        return $client::where(['revoked' => false])->orderBy('name', 'asc')->get();
    }

    public function findForManagement($id)
    {
        $client = Passport::client();
        return $client::find($id);
    }

    public function create($userId, $name, $redirect, $provider = null, $personalAccess = false, $password = false, $confidential = true)
    {
        $client = Passport::client()->forceFill([
            'user_id' => $userId,
            'client_name' => $name,
            'secret' => Str::random(40),
            'redirect_uris' => (is_array($redirect)) ? $redirect : [$redirect],
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'grant_types' => ($password) ? ["authorization_code", "password"] : null,
            'revoked' => false,
        ]);

        $client->save();

        return $client;
    }
}
