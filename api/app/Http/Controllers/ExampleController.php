<?php

namespace App\Http\Controllers;

use App\Models\GMAccount;
use App\Models\Test;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:gm', ['except' => ['test']]);
    }

    public function test(Request $request)
    {
        $user = DB::connection('mongodb')->collection('users')->get();

        $user = DB::collection('users')->get();

        $t = (new Test);
        $t->name = 'adasdfa' . rand(0, 999999);
        $t->username = 'adasdfasdf';
        $t->password = Crypt::encrypt('a123456');
        $t->a = [1, 2, 3, 4];
        $t->save();
        $d = Test::orderBy("_id", 'desc')->first();
        // dump($d);

        $g = (new GMAccount);
        $g->username = 'hofa';
        $g->password = app('hash')->make('a123456');
        $g->save();
        return response()->json($d);
    }

    public function getUser(Request $request)
    {
        dd(JWTAuth::parseToken()->touser());
        return \response()->json(JWTAuth::parseToken()->touser());
    }
}
