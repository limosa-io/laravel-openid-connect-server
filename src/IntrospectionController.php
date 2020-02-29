<?php

namespace Idaas\Passport;

use Illuminate\Http\JsonResponse;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;

class IntrospectionController
{
    /**
     * @var \Lcobucci\JWT\Parser
     */
    private $jwt;

    /**
     * constructing IntrospectionController
     *
     * @param \Lcobucci\JWT\Parser $jwt
     * @param \League\OAuth2\Server\ResourceServer $resourceServer
     * @param \Laravel\Passport\ClientRepository
     */
    public function __construct(
        Parser $jwt
    ) {
        $this->jwt = $jwt;
    }


    /**
     * Authorize a client to access the user's account.
     *
     * @param  ServerRequestInterface $request
     *
     * @return JsonResponse|ResponseInterface
     */
    public function introspect(ServerRequestInterface $request, BearerTokenValidator $validator)
    {
        if (array_get($request->getParsedBody(), 'token_type_hint', 'access_token') !== 'access_token') {
            //  unsupported introspection
            return $this->notActiveResponse();
        }

        $accessToken = array_get($request->getParsedBody(), 'token');
        if ($accessToken === null) {
            return $this->notActiveResponse();
        }

        try {
            $token = $this->jwt->parse($accessToken);

            try {
                $validator->ensureValidity($token);
            } catch (OAuthServerException $e) {
                return $this->notActiveResponse();
            }

            return $this->jsonResponse([
                'active' => true,
                'scope' => trim(implode(' ', (array)$token->getClaim('scopes', []))),
                'client_id' => $token->getClaim('aud'),
                'token_type' => 'access_token',
                'exp' => intval($token->getClaim('exp')),
                'iat' => intval($token->getClaim('iat')),
                'nbf' => intval($token->getClaim('nbf')),
                'sub' => $token->getClaim('sub'),
                'aud' => $token->getClaim('aud'),
                'jti' => $token->getClaim('jti'),
            ]);
        } catch (OAuthServerException $oAuthServerException) {
            return $oAuthServerException->generateHttpResponse(new Psr7Response);
        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    /**
     * returns inactive token message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function notActiveResponse() : JsonResponse
    {
        return $this->jsonResponse(['active' => false]);
    }

    /**
     * @param array|mixed $data
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function jsonResponse($data, $status = 200) : JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    /**
     * returns an error
     *
     * @param \Exception $exception
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function exceptionResponse(\Exception $exception, $status = 500) : JsonResponse
    {
        return $this->notActiveResponse();
    }
}
