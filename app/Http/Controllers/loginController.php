<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class loginController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {

            return view('login');
        } else {
            $request->validate([
                'account' => 'required',
                'password' => 'required',
            ]);

            $user = DB::table('t_user')->select('id', 'password')->where('account', $request->get('account'))->first();

            if (empty($user)) {
                return back()->with(['error' => '账号错误']);
            }

            if ($user->password != $request->get('password')) {
                return back()->with(['error' => '密码错误']);
            }

            session()->put('account', $user->id);

            return redirect('/');
        }
    }

    public function quit()
    {
        session()->forget('account');
        return redirect('/login');
    }
}
