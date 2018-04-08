<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class loginAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!session()->get('account')) {
            return redirect('/login');
        }
        //获取用户信息
        $user = DB::table('t_user')
            ->select('t_user.account', 't_group.name')
            ->leftjoin('t_user_group', 't_user_group.userId', 't_user.id')
            ->leftjoin('t_group', 't_group.id', 't_user_group.groupId')
            ->where('t_user.id', session()->get('account'))
            ->first();

        session()->put([
            'name' => $user->account,
            'title' => $user->name
        ]);

        return $next($request);
    }
}
