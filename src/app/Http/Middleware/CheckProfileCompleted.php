<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // プロフィール未設定の場合、プロフィール設定画面にリダイレクト
        if ($user && !$user->is_profile_completed && !$request->is('mypage/profile*')) {
            return redirect('/mypage/profile');
        }

        return $next($request);
    }
}
