<?php

namespace Idaas\Passport\Model;

class Provider implements ProviderInterface
{
    public function toJson($options = 0)
    {
        return json_encode([
            // attributes
        ], $options);
    }
}
