<?php
class Tool_Message {

    public static $apikey = "a5c1428d05e0701a128871b6a70ba374"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取

    static function run($action, $mobile = '', $text = '', $sign = '云片网')
    {
        $ch = curl_init();

        # 设置验证方式
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        # 设置返回结果为流
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # 设置超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        # 设置通信方式
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $tmMo = new Tool_Message;

        switch ($action) {
            case 'get':

                $json_data = $tmMo->getUser($ch, self::$apikey);
                break;

            case 'send':

                $data = array(
                    'text'      => $text,
                    'apikey'    => self::$apikey,
                    'mobile'    => $mobile
                );

                $json_data = $tmMo->send($ch, $data);
                break;

            case 'tpl_send':

                $data = array(
                    'tpl_id'    => '1',
                    'tpl_value' => urlencode('#code#').'='.urlencode('1234').'&'.urlencode('#company#').'='.urlencode($sign),
                    'apikey'    => self::$apikey,
                    'mobile'    => $mobile
                );

                $json_data = $tmMo->tplSend($ch, $data);
                break;

            case 'voice_send':

                $data = array(
                    'code'      => $text,
                    'apikey'    => self::$apikey,
                    'mobile'    => $mobile
                );

                $json_data = $tmMo->voiceSend($ch, $data);
                break;

            default:
                # code...
                break;
        }
        curl_close($ch);

        return json_decode($json_data, true);
    }


    //获得账户
    protected function getUser($ch, $apikey)
    {
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/user/get.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
        return curl_exec($ch);
    }

    protected function send($ch, $data)
    {
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    protected function tplSend($ch, $data)
    {
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/tpl_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    protected function voiceSend($ch, $data)
    {
        curl_setopt ($ch, CURLOPT_URL, 'http://voice.yunpian.com/v1/voice/send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }


}
