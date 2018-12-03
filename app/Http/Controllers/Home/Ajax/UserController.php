<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function index()
    {
        // $this->seo('币交所-区块链权益资产交易平台');

        // 系统公告
        $data = DB::select("select id,title,created from news where category=1 and receive=2 and sort>0 order by sort desc, id desc limit 5");



        //// 行业新闻
        $newsdata = DB::select("select id,title,created from news where category=2 and receive=2 and sort>0 order by sort desc, id desc limit 5");

        $pairs = DB::select("select * from coin_pair where status=1");

        //// 累计交易额
        ////chen
        //$pairs           = Coin_PairModel::getInstance()->getList();
        //$arr[0]['total'] = 0;
        //foreach ($pairs as $v)
        //{
        //    $table = 'order_' . $v['coin_from'] . 'coin';
        //    $arrs  = $tMO->query("select sum(number*price) total from $table where opt=1");
        //    $arr[0]['total'] += $arrs[0]['total'];
        //}
        ////$arr = $tMO->query("select sum(number*price) total from order_coin where opt=1");
        ////chen
        //if ($arr[0]['total'])
        //{
        //    $tmpNum = (int) $arr[0]['total'];
        //}
        //else
        //{
        //    $tmpNum = 0;
        //}
        //$total_24 = number_format($tmpNum, 0, '.', ' ');
      //  $this->assign('total_24', 66);
        $total_24=66;
        //
        //$pairs = Coin_PairModel::getInstance()->getList();

        //return view('index.index')->with('pairs',$pairs);
        return view('home.index.index',compact('pairs','data','newsdata',"total_24"));
    }

    public function captcha(Request $request)
    {
        //生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 100, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();
        //把内容存入session
        $request->session()->put('milkcaptcha', $phrase);


        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/jpeg');
        exit($builder->output());

    }
}
