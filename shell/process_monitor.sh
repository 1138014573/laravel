#!/bin/bash
#cli_*重启&监控脚本
#日志文件
file_name="./php_coinin.log"
proc_date=`date "+%Y%m%d"`
# check|restart
if [ "$2" = "trade" ];
then
    proc_name=("/cli_trade/docoin/pair/goc_cny" "/cli_trade/docoin/pair/btc_cny" "/cli_trade/docoin/pair/ltc_cny" "/cli_trade/docoin/pair/lc_cny" "/cli_trade/docoin/pair/mtc_cny" "/cli_trade/docoin/pair/uc_cny" "/cli_trade/docoin/pair/dsc_cny" "/cli_trade/docoin/pair/lbc_cny" "/cli_trade/docoin/pair/mac_cny" "/cli_trade/docoin/pair/lcc_cny" "/cli_trade/docoin/pair/tur_cny"  "/cli_trade/docoin/pair/ecf_cny"  "/cli_trade/docoin/pair/osc_cny" "/cli_trade/docoin/pair/gec_cny")
elif [ "$2" = "shell" ];
then
    proc_name=("sentemail.php")
else
    exit 3
fi

#启动脚本命令
function start()
{
    case "$1" in
        trade)
            nohup php Cli.php request_uri=$2 &
            ;;
        shell)
            nohup php $2 &
            ;;
        *)
            return 0
            ;;
    esac
    return 1
}

case "$1" in
    check)
        #单纯检查脚本是否存在
        for proc in ${proc_name[@]}
        do
            proc_space=${proc//,/ }
            number=`ps -ef | grep "$proc_space" | grep -v grep | wc -l`
            # 判断进程是否存在
            if [ $number -eq 0 ]
            then
                start $2 $proc
                pid=`ps -ef | grep "$proc_space" | grep -v grep | awk '{print $2}'`
                # 将新进程号和重启时间记录
                echo ${pid}, `date` >>  $file_name
            fi
        done
		;;
    stop)
        for proc in ${proc_name[@]}
        do
            proc_space=${proc//,/ }
            #获取进程号
            pid=`ps -ef | grep "$proc_space" | grep -v grep | awk '{print $2}'`
            if [ $pid -ne 0 ]
            then
                kill -9 $pid
            fi
        done
        ;;
    restart)
        for proc in ${proc_name[@]}
        do
            proc_space=${proc//,/ }
            #获取进程号
            pid=`ps -ef | grep "$proc_space" | grep -v grep | awk '{print $2}'`
            if [ $pid -ne 0 ]
            then
                kill -9 $pid
            fi
            number=`ps -ef | grep "$proc_space" | grep -v grep | wc -l`
            #判断进程是否存在
            if [ $number -eq 0 ]
            then
                sleep 2
                start $2 $proc
                pid=`ps -ef | grep "$proc_space" | grep -v grep | awk '{print $2}'`
                # 将新进程号和重启时间记录
                echo ${pid}, `date` >>  $file_name
            fi
        done
		;;

    *)
        echo "it's need enter the param: check|restart"
        exit 1
		;;
esac
exit 0
