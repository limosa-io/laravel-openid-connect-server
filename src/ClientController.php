<?php

namespace Idaas\Passport;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Idaas\Passport\Bridge\ClientRepository;

use Laravel\Passport\Http\Controllers\ClientController as LaravelClientController;

class ClientController extends LaravelClientController
{
    use ValidatesRequests;

    /**
     * The client repository instance.
     *
     * @var \ArieTimmerman\Passport\Brdige\ClientRepository
     */
    protected $clients;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    protected $validations = [

        'application_type' => 'in:web,native',
        'public' => 'in:public,confidential',
        'redirect_uris' => 'nullable|array',
        'redirect_uris.*' => ['required','url'],

        'post_logout_redirect_uris' => 'nullable|array',
        'post_logout_redirect_uris.*' => ['required','url'],

        'response_types' => 'nullable|array',
        'response_types.*' => 'in:code,id_token,token|distinct',
        'grant_types'   => 'nullable|array',
        'grant_types.*' => 'in:authorization_code,implicit,refresh_token,client_credentials',
        
        'code_challenge_methods_supported' => 'nullable|array|in:plain,S256',

        'contacts' => 'nullable|array',
        'contacts.*' => 'email|distinct',
        
        // strictly not required according to OIDC specs
        'client_name' => 'required|unique:oidc_clients,name|max:255',
        
        'logo_uri' => 'nullable',
        'client_uri' => 'nullable|url',
        'policy_uri' => 'nullable|url',
        'tos_uri' => 'nullable|url',

        'token_endpoint_auth_method' => 'nullable|in:client_secret_post,none',

        'default_max_age' => 'nullable|integer|min:0',

        'default_prompt' => 'nullable|in:login,none,consent',
        'default_prompt_allow_override' => 'nullable|boolean',
        'default_acr_values_allow_override' => 'nullable|boolean',

        'require_auth_time' => 'nullable|integer|min:0',

        // 'default_acr_values' => 'nullable|array',
        // 'default_acr_values.*' => 'nullable|array',
        'initiate_login_uri' => 'nullable|url',
        
        'trusted' => 'nullable|boolean',

        'user_interface' => 'nullable|exists:u_i_servers,id',

    ];

    protected $messages = [
        'redirect_uris.*' => 'One or more values does not represent a valid url',
        'post_logout_redirect_uris.*' => 'One or more values does not represent a valid url'
    ];

    /**
     * Create a client controller instance.
     *
     * @param  \Idaas\Passport\ClientRepository  $clients
     * @param  \Illuminate\Contracts\Validation\Factory  $validation
     * @return void
     */
    public function __construct(
        ClientRepository $clients,
        ValidationFactory $validation
    ) {
        $this->clients = $clients;
        $this->validation = $validation;
    }

    /**
     * Get all of the clients for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forUser(Request $request)
    {
        return $this->clients->all();
    }

    /**
     * Store a new client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $data = $this->validate($request, $this->validations, $this->messages);
        $client = $this->clients->getRepository()->create(
            $request->user()->getKey(),
            $request->client_name,
            $request->redirect_uris
        )->makeVisible('secret');
            
        $client->forceFill($data);

        $client->save();

        return $client;
    }


    public function get($clientId)
    {
        //TODO: Add some form of authorization
        return $this->clients->findForManagement($clientId);
    }

    /**
     * Update the given client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $clientId
     * @return \Illuminate\Http\Response|\ArieTimmerman\Passport\Client
     */
    public function update(Request $request, $clientId)
    {
        $client =  $this->clients->findForManagement($clientId);

        if (! $client) {
            return new Response('', 404);
        }

        $validations = $this->validations;
        
        if ($request->input('client_name') == $client->client_name) {
            unset($validations['client_name']);
        }

        $data = $this->validate($request, $validations, $this->messages);

        $client->forceFill($data)->save();

        return $client;
    }

    /**
     * Delete the given client.
     *
     * @param  Request  $request
     * @param  string  $clientId
     * @return Response
     */
    public function destroy(Request $request, $clientId)
    {
        $client =  $this->clients->findForManagement($clientId);

        if (! $client) {
            return new Response('', 404);
        }

        $this->clients->getRepository()->delete(
            $client
        );
    }
}
