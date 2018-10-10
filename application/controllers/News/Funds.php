<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_FundsController extends Ctrl_Admin {

  # 资金汇总
  public function summaryAction() {
    $tRmbMO = new Exchange_CnyModel();
    
    if ( isset($_GET['kw']) && Tool_Validate::safe($_GET['kw'])) $today = $_GET['kw'];
    else $today = date("Y-m-d");
    
    $this->assign("today", $today);
    $time_start = strtotime($today);
    $time_end = $time_start + 24*60*60;
    // 人民币入资
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign('rmb_in', $result['total']);
    // 三种方式分别的金额
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='支付宝'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign('rmb_in_alipay', $result['total']);
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='财付通'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign('rmb_in_tenpay', $result['total']);
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='银行卡'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign('rmb_in_bank', $result['total']);
    
    
    // 人民币提现
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("rmb_out", $result['total']);
    // 三种方式分别的金额
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='支付宝'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("rmb_out_alipay", $result['total']);
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='财付通'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("rmb_out_tenpay", $result['total']);
    $sql = "select sum(money) as total from exchange_rmb where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='银行卡'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("rmb_out_bank", $result['total']);
    
    
    // 比特币转入
    $sql = "select sum(number) as total from exchange_btc where created>=".$time_start." and created<".$time_end." and opt_type='in'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("btc_in", $result['total']);
    // 比特币转出
    $sql = "select sum(number) as total from exchange_btc where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功'";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("btc_out", $result['total']);
    // 比特币当天成交量
    $sql = "select sum(number) as total from `order` where created>=".$time_start." and created<".$time_end." and opt=1";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("btc_vol", $result['total']);
    // 比特币24小时成交量
    $time_start_24 = time() - 24*60*60;
    $sql = "select sum(number) as total from `order` where created>=".$time_start_24." and opt=1";
    $result = $tRmbMO->fRow($sql);
    if ( !$result['total'] ) $result['total'] = 0;
    $this->assign("btc_vol_24", $result['total']);
    
    // 总资金
    $sql = "select sum(rmb_over) as rmb_over,sum(rmb_lock) as rmb_lock,sum(btc_over) as btc_over, sum(btc_lock) as btc_lock from user";
    $result = $tRmbMO->fRow($sql);
    $this->assign("rmb_over", $result['rmb_over']);
    $this->assign("rmb_lock", $result['rmb_lock']);
    $this->assign("btc_over", $result['btc_over']);
    $this->assign("btc_lock", $result['btc_lock']);
    $this->assign("ltc_over", $result['ltc_over']);
    $this->assign("ltc_lock", $result['ltc_lock']);
    
    // 手续费
    $sql = "select sum(price) as fee_money,sum(number) as fee_btc from `order` where created>=".$time_start." and created<".$time_end." and opt=3";
    $result = $tRmbMO->fRow($sql);
    $this->assign("fee_money", $result['fee_money']);
    $this->assign("fee_btc", $result['fee_btc']);
    
 }

}