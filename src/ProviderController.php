<?php

namespace Idaas\Passport;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ProviderController extends BaseController
{
    protected function toWebSafe($base64)
    {
        return str_replace(array('+','/','='), array('-','_',''), $base64);
        ;
    }
    protected function base64WebSafe($input)
    {
        return $this->toWebSafe(base64_encode($input));
    }

    public function index(ProviderRepository $providerRepository)
    {
        return $providerRepository->get();
    }

    public function wellknown(ProviderRepository $providerRepository)
    {
        return $providerRepository->wellknown();
    }

    public function webfinger(ProviderRepository $providerRepository, Request $request)
    {
        $result = [
            "links" => [
                [
                    "rel" => "http://openid.net/specs/connect/1.0/issuer",
                    "href" => url('/')
                ]
            ]
        ];

        if ($request->input('subject')) {
            $result['subject'] = $request->input('subject');
        }

        return $result;
    }

    public function jwks(ProviderRepository $providerRepository)
    {

        $crypt = resolve(KeyRepository::class)->getPublicKey();

        $key = $crypt->x509;
        
        $key = str_replace(array('-----BEGIN CERTIFICATE-----','-----END CERTIFICATE-----',"\r", "\n", " "), "", $key);
        $keyForParsing = "-----BEGIN CERTIFICATE-----\n".chunk_split($key, 64, "\n")."-----END CERTIFICATE-----\n";

        $result = openssl_pkey_get_details(openssl_pkey_get_public(openssl_x509_read($keyForParsing)));
        
        return [
            'keys' => [
                [
                    'alg' => 'RS256',
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'x5c' => $this->toWebSafe($key),
                    'n' => $this->base64WebSafe($result['rsa']['n']),
                    'e' => $this->base64WebSafe($result['rsa']['e']),
                    'kid' => $crypt->kid,
                    'x5t' => $this->base64WebSafe(openssl_x509_fingerprint($keyForParsing, 'sha1', true))
                ]
            ]
        ];
    }

    public function update(Request $request, ProviderRepository $providerRepository)
    {
        return $providerRepository->update($request);
    }
}
