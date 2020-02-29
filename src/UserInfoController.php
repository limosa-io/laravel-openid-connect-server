<?php

namespace Idaas\Passport;

use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\OpenID\UserInfo;
use Illuminate\Http\Request;
use Laminas\Diactoros\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;

class UserInfoController
{
    public function userinfo(Request $request, UserRepositoryInterface $userRepository)
    {
        $psr = (new PsrHttpFactory(
            new ServerRequestFactory,
            new StreamFactory,
            new UploadedFileFactory,
            new ResponseFactory
        ))->createRequest($request);

        /* @var $userinfo \Idaas\OpenID\UserInfo */
        $userinfo = resolve(UserInfo::class);

        return $userinfo->respondToUserInfoRequest($psr, new Response());
    }
}
