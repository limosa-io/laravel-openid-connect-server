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
}
