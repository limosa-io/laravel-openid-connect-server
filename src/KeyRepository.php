<?php

namespace Idaas\Passport;

use Idaas\OpenID\CryptKey;
use Idaas\Passport\Model\Client;
use Laravel\Passport\Passport;

class KeyRepository
{
    public function getPrivateKey(string $kid = "1"): CryptKey
    {
        $privateKey = config('passport.private_key')
            ?? 'file://' . Passport::keyPath('oauth-private.key');
        $cryptKey = new CryptKey($privateKey);
        $cryptKey->setKid($kid);
        return $cryptKey;
    }

    public function getPublicKey(string $kid = "1"): CryptKey
    {
        $publicKey = config('passport.public_key')
            ?? 'file://' . Passport::keyPath('oauth-public.key');
        $cryptKey = new CryptKey($publicKey);
        $cryptKey->setKid($kid);
        return $cryptKey;
    }

    public function getPublicKeyForClient(Client $client, string $kid = "1"): CryptKey
    {
        $publicKey = config('passport.public_key')
            ?? file_get_contents('file://' . Passport::keyPath('oauth-public.key'));
        $cryptKey = new CryptKey($publicKey);
        $cryptKey->setKid($kid);
        return $cryptKey;
    }

    public function getAllPublicKeys()
    {
        return [$this->getPublicKey()];
    }

    public function getPrivateKeyByKid($kid): CryptKey
    {
        return $this->getPrivateKey();
    }

    public static function generateNew()
    {
        $dn = array(
            "countryName" => "NL",
            "stateOrProvinceName" => "Noord-Holland",
            "localityName" => "Hilversum",
            "organizationName" => "a11n",
            "organizationalUnitName" => "Developer",
            "commonName" => "a11n",
            "emailAddress" => "arietimmerman@a11n.nl"
        );

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));

        // Generate a self-signed cert, valid for 365 days
        $x509 = openssl_csr_sign($csr, null, $privkey, 365, array('digest_alg' => 'sha256'));

        openssl_x509_export($x509, $certout);
        openssl_pkey_export($privkey, $pkeyout);

        $publicKey = openssl_pkey_get_details(openssl_pkey_get_public($x509));

        return ['x509' => $certout, 'public_key' => $publicKey['key'], 'private_key' => $pkeyout];
    }
}
