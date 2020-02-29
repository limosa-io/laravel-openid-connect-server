<?php

namespace Idaas\Passport\OIDC\Middleware;

use Closure;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Http\Request;
use Idaas\Passport\ClientRepository;
use Idaas\Passport\TokenCache;

class AllowClientCORS
{
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    public $methods = 'POST, GET, PATCH, DELETE, PUT, OPTIONS';
    public $headers = 'Content-Type, X-AuthRequest, Authorization';
    public $maxAge = '1';

    protected $clientRepository;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
     */
    public function __construct(ResourceServer $server, ClientRepository $clientRepository)
    {
        $this->server = $server;
        $this->clientRepository = $clientRepository;
    }

    public function isCORSAllowed(Request $request)
    {
        if ($request->method() == 'OPTIONS') {
            return true;
        }

        $origin = $request->headers->get('origin');

        /* @var $token \Laravel\Passport\Token */
        $token = $request->user()->token();

        return resolve(TokenCache::class)->rememberOriginAllowed($origin, function () use ($request, $origin, $token) {
            $allowed = false;

            $client = $this->clientRepository->find($token->client_id);

            foreach ($client->redirect_uris as $redirectUri) {
                $parse = parse_url($redirectUri);

                $parse['port'] = $parse['port'] ?? 80;

                if (
                    $origin == null ||
                    $origin == ($parse['scheme'] . "://" . $parse['host'] . ':' . $parse['port']) || ($parse['port'] == 80 || $parse['port'] == 443) && $origin == ($parse['scheme'] . "://" . $parse['host'])
                ) {
                    $allowed = true;
                    break;
                }
            }

            return $allowed;
        });
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        if ($request->headers->get('origin') == null) {
            return $next($request);
        } elseif ($this->isCORSAllowed($request)) {
            $response = $next($request);


            $response->headers->set('Access-Control-Allow-Methods', $this->methods);
            $response->headers->set('Access-Control-Allow-Headers', $this->headers);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Max-Age', $this->maxAge);

            return $response;
        } else {
            return response(null, 403);
        }
    }
}
