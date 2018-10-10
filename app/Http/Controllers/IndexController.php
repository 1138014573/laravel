<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Index;

class IndexController extends Controller
{
    //
    public function index()
    {
       // $this->seo('币交所-区块链权益资产交易平台');

        // 系统公告
        $data = DB::select("select id,title,created from news where category=1 and receive=2 and sort>0 order by sort desc, id desc limit 5");
        $data=get_object_vars($data[0]);


        //// 行业新闻
        $newsdata = DB::select("select id,title,created from news where category=2 and receive=2 and sort>0 order by sort desc, id desc limit 5");
        $newsdata=get_object_vars($newsdata[0]);
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
        //$this->assign('total_24', $total_24);
        //
        //$pairs = Coin_PairModel::getInstance()->getList();

        //return view('index.index')->with('pairs',$pairs);
        return view('index.index',compact('pairs','data','newsdata'));
    }
}
