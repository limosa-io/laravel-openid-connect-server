<?php

namespace Idaas\Passport;

use Illuminate\Http\Request;

class UserInfoController
{
    public function userinfo(Request $request, UserRepository $userRepositoryOIDC)
    {
        return (new UserInfo())->respondToUserInfoRequest($request, $userRepositoryOIDC);
    }
}
