<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as FortifyLogoutResponse;

class LogoutResponse implements FortifyLogoutResponse
{
    // ログアウト時のリダイレクト先を/loginに設定
    public function toResponse($request)
    {
        return redirect('/login');
    }
}
