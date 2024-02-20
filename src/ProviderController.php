<?php

namespace Idaas\Passport;

use Idaas\OpenID\CryptKey;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ProviderController extends BaseController
{
    protected function toWebSafe($base64)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), $base64);;
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

    public static function pem2der($pem_data)
    {
        $begin = "CERTIFICATE-----";
        $end   = "-----END";
        $pem_data = substr($pem_data, strpos($pem_data, $begin) + strlen($begin));
        $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
        $der = base64_decode($pem_data);
        return $der;
    }

    public function jwks(ProviderRepository $providerRepository)
    {
        /**
         * @var CryptKey $crypt
         */
        $crypt = resolve(KeyRepository::class)->getPublicKey();

        $result = [
            'alg' => 'RS256',
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => $crypt->kid ?? 1
        ];

        if (!empty($crypt->x509)) {
            $key = $crypt->x509;
            $key = str_replace(array('-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r", "\n", " "), "", $key);
            $keyForParsing = "-----BEGIN CERTIFICATE-----\n" . chunk_split($key, 64, "\n") . "-----END CERTIFICATE-----\n";

            $pkey = openssl_pkey_get_details(openssl_pkey_get_public(openssl_x509_read($keyForParsing)));

            // Do not use x5c and x5t for now
            // $result['x5c'] = [
            //     self::pem2der($this->toWebSafe($pkey))
            // ];
            // $result['x5t'] = [
            //     $this->base64WebSafe(openssl_x509_fingerprint($keyForParsing, 'sha1', true))
            // ];

        } else {
            $pkey = openssl_pkey_get_details(openssl_pkey_get_public($crypt->getKeyContents()));
        }

        $result['n'] = $this->base64WebSafe($pkey['rsa']['n']);
        $result['e'] = $this->base64WebSafe($pkey['rsa']['e']);

        return [
            'keys' => [
                $result
            ]
        ];
    }

    public function update(Request $request, ProviderRepository $providerRepository)
    {
        return $providerRepository->update($request);
    }
}
