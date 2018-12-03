<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    /**
     * 登录
     */
    public function login()
    {
        // $this->seo('币交所-区块链权益资产交易平台');
///echo 111;die;
        // 系统公告
        //$data = DB::select("select id,title,created from news where category=1 and receive=2 and sort>0 order by sort desc, id desc limit 5");



        return view('home.user.login');
    }

    /**
     * 注册
     */
    public function register()
    {
        // $this->seo('币交所-区块链权益资产交易平台');
///echo 111;die;
        // 系统公告
        //$data = DB::select("select id,title,created from news where category=1 and receive=2 and sort>0 order by sort desc, id desc limit 5");



        return view('home.user.register');
    }
}
