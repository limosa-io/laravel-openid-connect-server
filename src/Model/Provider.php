<?php

namespace Idaas\Passport\Model;

class Provider implements ProviderInterface
{
    protected $wellKnown;

    public function __construct()
    {
        $this->wellKnown = array(
            'response_types_supported' =>
            [
                'code',
                'token',
                'id_token',
                'code token',
                'id_token token',
            ],
            'acr_values_supported' =>
            [
                'urn:mace:incommon:iap:gold',
                'urn:mace:incommon:iap:silver',
                'urn:mace:incommon:iap:bronze',
            ],
            'scopes_supported' =>
            [
                'openid',
                'online_access',
                'profile',
                'email',
                'address',
                'phone',
                'profile',
                'email',
                'address',
                'phone',
                'roles'
            ],
            'authorization_endpoint' => route('oauth.authorize', []),
            'token_endpoint' => route('oauth.token', []),
            'userinfo_endpoint' => route('oidc.userinfo', []),
            'jwks_uri' => route('oidc.jwks', []),
            'issuer' => url('/'),
            'claims_supported' =>
            [
                'sub',
                'iss',
                'roles',
                'acr',
                'picture',
                'profile',
            ],
            'end_session_endpoint' => route('oidc.logout', []),
            'code_challenge_methods_supported' =>
            [
                'S256',
            ],
            'introspection_endpoint' => route('oauth.introspect'),
            'introspection_endpoint_auth_methods_supported' =>
            [
                'client_secret_jwt',
            ],
            'token_endpoint_auth_methods_supported' =>
            [
                'none',
                'client_secret_post',
                'client_secret_basic',
            ],
            'revocation_endpoint' => route('oauth.revoke'),
            'service_documentation' => url('/'),
            'ui_locales_supported' =>
            [
                'en-GB',
                'nl-NL',
            ],
        );
    }

    public function toJson($options = 0)
    {
        return json_encode([
            $this->wellKnown
        ], $options);
    }
}
