<?php

namespace Idaas\Passport\Http\Controllers;

use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Idaas\Passport\ClientRepository;
use Idaas\Passport\PassportConfig;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository as LaravelClientRepository;
use Laravel\Passport\Http\Controllers\AuthorizationController as LaravelAuthorizationController;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationController extends LaravelAuthorizationController
{

    public function isApproved(
        AuthorizationRequest $authRequest,
        ?Authenticatable $user,
        Client $client,
        TokenRepository $tokens
    ) {
        if ($user == null) {
            return false;
        }

        $scopes = $this->parseScopes($authRequest);

        $token = $tokens->findValidToken(
            $user,
            $client
        );

        return ($token && $token->scopes === collect($scopes)->pluck('id')->all());
    }

    public function returnError(AuthorizationRequest $authorizationRequest, Request $request)
    {
        $clientUris = Arr::wrap($authorizationRequest->getClient()->getRedirectUri());

        if (!in_array($uri = $authorizationRequest->getRedirectUri(), $clientUris)) {
            $uri = Arr::first($clientUris);
        }

        if ($authorizationRequest instanceof AuthenticationRequest && $authorizationRequest->getResponseMode() == 'web_message') {
            return (new WebMessageResponse())->setData([
                'redirect_uri' => $uri,
                'error'  => 'access_denied',
                'state' => $authorizationRequest->getState(),
            ])->generateHttpResponse(new Psr7Response());
        } else {
            $separator = $authorizationRequest->getGrantTypeId() === 'implicit' ? '#' : '?';
            return $this->response->toResponse($request)->isRedirect(
                $uri . $separator . 'error=access_denied&state=' . $authorizationRequest->getState()
            );
        }
    }

    /**
     * In contrast with Laravel Passport, this authorize method can be invoked when the user has not been authenticated
     * This is because the OpenID Connect determines how to user should be authenticated
     */
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        LaravelClientRepository $clients,
        TokenRepository $tokens
    ) {
        return $this->withErrorHandling(function () use ($psrRequest, $request, $clients, $tokens) {

            $authorizationRequest = $this->server->validateAuthorizationRequest($psrRequest);
            $authenticateResponse = $this->doAuthenticate($psrRequest, $authorizationRequest);

            if ($authenticateResponse == null) {
                $authenticateResponse = $this->continueAuthorize($authorizationRequest, $request, $clients, $tokens);
            }
            return $authenticateResponse;
        });
    }

    public function continueAuthorize(
        AuthorizationRequest $authRequest = null,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens
    ) {
        // If $authRequest is not provided as a parameter, load it from a session
        if ($authRequest == null) {
            $authRequest = $request->session()->get('authRequest');
        }

        if ($authRequest == null) {
            throw OAuthServerException::invalidRequest('unknown', 'No authorization request found. Seems like a cookie problem.');
        }

        $user = $request->user();
        $client = $clients->find($authRequest->getClient()->getIdentifier());

        if ($this->isApproved($authRequest, $user, $client, $tokens)) {
            return $this->approveRequest($authRequest, $user);
        } else {
            return $this->returnError($authRequest, $request);
        }
    }

    public function doAuthenticate(ServerRequestInterface $psrRequest, $authorizationRequest)
    {
        return resolve(PassportConfig::class)
            ->doAuthenticationResponse(
                AuthenticationRequest::fromAuthorizationRequest($authorizationRequest)
            );
    }
}
