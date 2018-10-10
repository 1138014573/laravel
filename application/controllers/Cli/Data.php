<?php
/**
 * 数据统计
 */
class Cli_DataController extends Ctrl_Cli {

    # 当前时间
    private $_time;
    # 对账开始时间
    private $_start;
    # 对账结束时间
    private $_end;
    # 对象
    private static $_coins_obj = null;

    /**
     * 对账
     *
     * 0 9 18 这三个时间段发送
     * php Cli.php request_uri=/cli_data/duizhang/hour/0
     */
    public function duizhangAction($hour=0){
        # 当前对账时间
        $this->_time = time();
        switch ($hour) {
            case 0:
                # 18 - 0点
                $this->_end     = strtotime(date('Ymd 0:0:0', $this->_time));
                $this->_start   = $this->_end - 6*3600;
                break;
            case 9:
                # 0 - 9点
                $this->_end     = strtotime(date('Ymd 9:0:0', $this->_time));
                $this->_start   = $this->_end - 9*3600;
                break;
            case 18:
                # 9 - 18点
                $this->_end     = strtotime(date('Ymd 18:0:0', $this->_time));
                $this->_start   = $this->_end - 9*3600;
                break;
            default:
                exit('error');
        }
        
        $where_time = ' created<'.$this->_end.' and created>='.$this->_start;
        # 获取当前币种
        $coin_mo    = new User_CoinModel;
        $user_mo    = new UserModel;
        $coins      = $coin_mo->field('id,name,display')->where('status=0')->fList();
        $asset_field_str  = 'sum(cny_over) as cny_over, sum(cny_lock) as cny_lock, sum(cny_over+cny_lock) as cny_total';
        $field_str  = 'cny_over, cny_lock';
        foreach ($coins as $v) {
            $asset_field_str .= ', sum('.$v['name'].'_over) as '.$v['name'].'_over, sum('.$v['name'].'_lock) as '.$v['name'].'_lock, sum('.$v['name'].'_'.'over+'.$v['name'].'_'.'lock) as '.$v['name'].'_total';
            $field_str .= ', '.$v['name'].'_over, '.$v['name'].'_lock';
            $coins_mo   = "Exchange_".ucfirst($v['name'])."Model";
            self::$_coins_obj[$v['name']] = new $coins_mo();
        }
        $assets             = array();
        # 总资产情况，包含官方
        $assets['sum']  = $user_mo->field($asset_field_str)->where('1=1')->fRow();

        # 官方资产情况
        $adminids       = '';
        foreach (User_AdminModel::$email as $k2=>$v2) {
            $adminids  .= $k2.',';
        }
        $admin_assets   = array();
        if($adminids) {
            $where_str      = 'uid in('.rtrim($adminids, ',').')';
            $admin_assets   = $user_mo->field('uid,email,name,'.$field_str)->where($where_str)->fList();
        }
        $assets['admin']      = $admin_assets;
       
        # CNY充值/提现金额
        $cny_mo             = new Exchange_CnyModel;
        ## 有效充值单金额
        $cny_in_success_order   = $cny_mo->field('sum(money) as total')->where('opt_type="in" and status="成功" and '.$where_time)->fRow();
        ## 等待充值的金额
        $cny_in_waiting_order   = $cny_mo->field('sum(money) as total')->where('opt_type="in" and status="等待" and '.$where_time)->fRow();
        ## 已经提现成功金额
        $cny_out_success_order  = $cny_mo->field('sum(money) as total, sum(money_u) as total_u')->where('opt_type="out" and status="成功" and '.$where_time)->fRow();
        ## 等待提现金额
        $cny_out_waiting_order  = $cny_mo->field('sum(money) as total, sum(money_u) as total_u')->where('opt_type="out" and status="等待" and '.$where_time)->fRow();

        $cny_order              = array();
        if(empty($cny_in_success_order)) {
            $cny_in_success_order['total'] = 0;
        }
        $cny_order['in_success']= (float)$cny_in_success_order['total'];
        if(empty($cny_in_waiting_order)) {
            $cny_in_waiting_order['total'] = 0;
        }
        $cny_order['in_waiting']= (float)$cny_in_waiting_order['total'];
        if(empty($cny_out_success_order)) {
            $cny_out_success_order['total'] = 0;
            $cny_out_success_order['total_u'] = 0;
        }
        $cny_order['out_success_total']     = (float)$cny_out_success_order['total'];
        $cny_order['out_success_total_u']   = (float)$cny_out_success_order['total_u'];
        if(empty($cny_out_waiting_order)) {
            $cny_out_waiting_order['total'] = 0;
            $cny_out_waiting_order['total_u'] = 0;
        }
        $cny_order['out_waiting']           = (float)$cny_out_waiting_order['total'];
        $cny_order['out_waiting_total_u']   = (float)$cny_out_waiting_order['total_u'];

        # 充币数量/提币数量
        $coins_data            = array();
        foreach (self::$_coins_obj as $coin => $coin_mo) {
            $tmp_arr    = $coin_mo->field('sum(number) as total')->where('opt_type="in" and status="成功" and '.$where_time)->fRow();
            if(empty($tmp_arr)) {
                $tmp_arr['total'] = 0;
            }
            $coins_data[$coin]['in_success'] = (float)$tmp_arr['total'];

            $tmp_arr    = $coin_mo->field('sum(number) as total')->where('opt_type="in" and status="等待" and '.$where_time)->fRow();
            if(empty($tmp_arr)) {
                $tmp_arr['total'] = 0;
            }
            $coins_data[$coin]['in_waiting'] = (float)$tmp_arr['total'];

            $tmp_arr    = $coin_mo->field('sum(number) as total')->where('opt_type="out" and status="成功" and '.$where_time)->fRow();
            if(empty($tmp_arr)) {
                $tmp_arr['total'] = 0;
            }
            $coins_data[$coin]['out_success'] = (float)$tmp_arr['total'];

            $tmp_arr    = $coin_mo->field('sum(number) as total')->where('opt_type="out" and status="等待" and '.$where_time)->fRow();
            if(empty($tmp_arr)) {
                $tmp_arr['total'] = 0;
            }
            $coins_data[$coin]['out_waiting'] = (float)$tmp_arr['total'];
        }
        
        # 发送邮件
        $send_email  = 'duizhang@bijiaosuo.com';
        $title       = '系统资产监控数据'.'['.date('Y-m-d H:i:s', $this->_start)."至".date('Y-m-d H:i:s', $this->_end).']';
        $send_data   = $this->duizhangTpl($title, $assets, $cny_order, $coins_data);
        $result = Tool_Fnc::mailto($send_email, $title, $send_data);
        
        exit('success');
    }


    public function duizhangTpl($title, $assets, $cny_order, $coins_order){
        $str = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'></head><body>
                    <h3 style='font-size:16px;height:45px;font-weight:bold;'>".$title."</h3>   
                    <table style='width:100%;'>";
        $str .= "<tr>
                    <td colspan='2' style='font-size:15px;height:50px;line-height:50px;background:#e3e3e3;color:red;'>平台总资产</td>
                </tr>";
        foreach ($assets['sum'] as $key => $value) {
            $str .= "<tr>
                    <td>".$key.":</td>
                    <td>".$value."</td>
                </tr>";
        }

        $str .= "<tr>
                    <td colspan='2' style='font-size:15px;height:50px;line-height:50px;background:#e3e3e3;color:red;'>官方账户资产</td>
                </tr>";
        foreach ($assets['admin'] as $value2) {
            $str .= "<tr>
                    <td colspan='2' style='font-size:14px;height:40px;line-height:40px;background:#f3f3f3;'>ID:".$value2['uid']."&nbsp;邮箱:".$value2['email']."&nbsp;姓名:".$value2['name']."</td>";
                    foreach ($value2 as $key3 => $value3) {
                        if('email'!=$key3 && 'name'!=$key3 && 'uid'!=$key3) {
                            $str .= "<tr><td>";
                            $str .= $key3;
                            $str .= "</td><td>".$value3.'</td></tr>';
                        }
                    }
            $str .="</tr>";
        }

        $str .= "<tr>
                    <td colspan='2' style='font-size:15px;height:50px;line-height:50px;background:#e3e3e3;color:red;'>Cny充值/提现情况</td>
                </tr>";
        foreach ($cny_order as $key4 => $value4) {
            $str .= "<tr>
                    <td>".$key4.":</td>
                    <td>".$value4."</td>
                </tr>";
        }      

        # 虚拟货币充币/提币情况
        
        foreach ($coins_order as $key5 => $value5) {
            $str .= "<tr>
                        <td colspan='2' style='font-size:15px;height:50px;line-height:50px;background:#e3e3e3;color:red;'>".ucfirst($key5)."充币/提币</td>
                    </tr>";
            foreach ($value5 as $key6 => $value6) {
                $str .= "<tr>
                        <td>".$key6.":</td>
                        <td>".$value6."</td>
                    </tr>";
            } 
        }

        $str .= "</table></body></html>"; 

        return $str;
    }



    public function userAction($hour=8) {
        # 当前对账时间
        $this->_time = time();
        switch ($hour) {
            case 8:
                # 21 - 9点
                $this->_end     = strtotime(date('Ymd 9:0:0', $this->_time));
                $this->_start   = $this->_end - 13*3600;
                break;
            case 21:
                # 9 - 21点
                $this->_end     = strtotime(date('Ymd 22:0:0', $this->_time));
                $this->_start   = $this->_end - 11*3600;
                break;
            default:
                exit('error');
        }

        $where_time = ' created<'.$this->_end.' and created>='.$this->_start;
        $user_mo    = new UserModel;
        $user       = array();
        # 当前平台用户总数：
        $user['total']      = $user_mo->where('1=1')->count();
        # 已实名用户总数
        $user['auth_total'] = $user_mo->where("mo<>'' and idcard<>'' and name<>''")->count();
        # 新增用户总数
        $user['new_total']  = $user_mo->where($where_time)->count();
        # 新增已实名用户总数
        $user['new_auth_total']= $user_mo->where($where_time." and mo<>'' and idcard<>'' and name<>''")->count();

        $send_email  = 'duizhang@bijiaosuo.com';
        $title       = '当前用户数据监控'.'['.date('Y-m-d H:i:s', $this->_start)."至".date('Y-m-d H:i:s', $this->_end).']';
        $send_data   = $this->userTpl($title, $user);
        $result = Tool_Fnc::mailto($send_email, $title, $send_data);
        
        exit('success');
    }

    private function userTpl($title, $user) {
       $str     = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'></head><body>
                    <h3 style='font-size:16px;height:45px;font-weight:bold;'>".$title."</h3>   
                    <table style='width:100%;'>";
        $str    .= "<tr>
                        <td style='font-size:14px;height:40px;line-height:40px;'>当前平台用户总数：</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>".$user['total']."</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>已实名用户总数：</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>".$user['auth_total']."</td>
                    </tr>";
         $str    .= "<tr>
                        <td style='font-size:14px;height:40px;line-height:40px;'>新增用户总数：</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>".$user['new_total']."</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>新增已实名用户总数：</td>
                        <td style='font-size:14px;height:40px;line-height:40px;'>".$user['new_auth_total']."</td>
                    </tr>";
        $str    .= "</table></body></html>"; 

        return $str;
    }
    
}
