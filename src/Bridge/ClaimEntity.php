<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Entities\ClaimEntityInterface;

class ClaimEntity implements ClaimEntityInterface
{

    protected $identifier;
    protected $type;
    protected $essential;

    public function __construct($identifier, $type, $essential)
    {
        $this->identifier = $identifier;
        $this->type = $type;
        $this->essential = $essential;
    }

    public static function fromJson(array $json)
    {
        return new ClaimEntity($json['identifier'], $json['type'], $json['essential']);
    }

    public static function fromJsonArray(array $json)
    {
        return collect($json)->map(function ($value) {
            return self::fromJson($value);
        })->toArray();
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getEssential()
    {
        return $this->essential;
    }

    public function jsonSerialize()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'type' => $this->getType(),
            'essential' => $this->getEssential()
        ];
    }
}
