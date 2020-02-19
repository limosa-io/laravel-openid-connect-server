<?php

namespace Idaas\Passport;

use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Http\Request;
use ArieTimmerman\Passport\TokenRepository;

class RevokeController
{
    public function index(Request $request, Parser $jwt, AdvancedBearerTokenValidator $validator, TokenRepository $tokens)
    {
        $tokenString =$request->input('token');
        $tokenTypeHint = $request->input('token_type_hint');

        if (!empty($tokenTypeHint) || $tokenTypeHint != 'access_token') {
            return response([
                'error' => 'unsupported_token_type'
            ], 400);
        }

        $token = $jwt->parse($tokenString);

        try {
            $validator->ensureValidity($token);
        } catch (OAuthServerException $e) {
            return response(null, 200);
        }

        $tokenEloquent = $tokens->find($token->getClaim('jti'));

        $tokenEloquent->revoke();

        return response(null, 200);
    }
}
