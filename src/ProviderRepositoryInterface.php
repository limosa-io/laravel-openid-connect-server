<?php

namespace Idaas\Passport;

use Illuminate\Http\Request;

interface ProviderRepositoryInterface
{

    /**
     * @return ProviderInterface
     */
    public function get();

    public function update(Request $request);
}
