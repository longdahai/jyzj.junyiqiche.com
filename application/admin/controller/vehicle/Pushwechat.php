<?php

namespace app\admin\controller\vehicle;

use app\admin\model\Order;
use app\admin\model\OrderDetails;
use app\admin\model\OrderImg;
use app\common\controller\Backend;
use fast\Date;
use think\Cache;
use think\Db;
use think\Config;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use think\Session;
use Endroid\QrCode\QrCode;
use wechat\Wx;
use think\Env;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Pushwechat extends Backend
{

    /**
     * Vehiclemanagement模型对象
     * @var \app\admin\model\vehicle\Vehiclemanagement
     */
    protected $model = null;
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order();

    }
    
    /**
     * 发送微信公众号模板消息
     * @param $data
     * @return array
     */
    public static function sendXcxTemplateMsg($data = '')
    {
        Cache::rm('access_token');
        $appid = Env::get('wx_public.appid');
        $secret = Env::get('wx_public.secret');
        $wx = new wx($appid,$secret);
        $access_token = $wx->getWxtoken()['access_token'];
        // pr($access_token);
        // die;
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        return posts($url, $data);
    }


    /**
     * 获取用户openid
     * @param $user_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOpenid($wx_public_user_id)
    {
        return Db::name('wx_public_user')->where(['id' => $wx_public_user_id])->find()['openid'];
    }

    /**
     * 发送年检公众号模板消息
     */
    public function sendannual($ids = '')
    {
        $detail = Collection($this->model->field('username,phone,wx_public_user_id,models_name')
            ->with(['orderdetails' => function ($q) use ($ids) {
                $q->withField('licensenumber,frame_number,annual_inspection_time,annual_inspection_status')->where('annual_inspection_status', 'in', ['soon','overdue'])->where('order_id', 1877);
            }])->select())->toArray();
       
        //是否存在数据
        if ($detail) {
            foreach ($detail as $key => $value) {
                
                $openid = $this->getOpenid($value['wx_public_user_id']);

                //是否有openid
                if ($openid) {

                    $type = $value['orderdetails']['annual_inspection_status'] == 'soon' ? '即将到期' : '已到期';
                    $first = $value['username'] . '您好，您车牌号为＂' . $value['orderdetails']['licensenumber'] . '＂车辆的年检' . $type;
                    $time = date('Y-m-d', $value['orderdetails']['annual_inspection_time']);
                    
                    $temp_msg = array(
                        'touser' => "{$openid}",
                        'template_id' => "aBEssxm7rNQKj_j0gDimieBcbjR1NbNrqvG8SV0wjSE",
                        'data' => array(
                            'first' => array(
                                "value" => "{$first}",
                            ),
                            'keyword1' => array(
                                "value" => "{$value['orderdetails']['licensenumber']}",
                            ),
                            'keyword2' => array(
                                "value" => "{$value['orderdetails']['frame_number']}",
                            ),
                            'keyword3' => array(
                                "value" => "{$time}",
                                "color" => '#FF5722'
                            ),
                            "remark" => array(
                                "value" => "请及时处理",
                            )
                            
                        ),
                    );
                    
                    $res = $this->sendXcxTemplateMsg(json_encode($temp_msg));
                   
                }
    
            }
            
            $this->success();
        }

    }


    /**
     * 发送保险公众号模板消息
     */
    public function sendtrafficforce($ids = '')
    {
        $detail = Collection($this->model->field('username,phone,wx_public_user_id,models_name')
            ->with(['orderdetails' => function ($q) use ($ids) {
                $q->withField('licensenumber,frame_number,traffic_force_insurance_time,traffic_force_insurance_status')->where('traffic_force_insurance_status', 'in', ['soon','overdue'])->where('order_id', 1864);
            }])->select())->toArray();
       
        //是否存在数据
        if ($detail) {
            foreach ($detail as $key => $value) {
                
                $openid = $this->getOpenid($value['wx_public_user_id']);

                //是否有openid
                if ($openid) {

                    $type = $value['orderdetails']['traffic_force_insurance_status'] == 'soon' ? '即将到期' : '已到期';
                    $first = $value['username'] . '您好，您车牌号为＂' . $value['orderdetails']['licensenumber'] . '＂车辆的保险' . $type;
                    $time = date('Y-m-d', $value['orderdetails']['traffic_force_insurance_time']);
                    
                    $temp_msg = array(
                        'touser' => "{$openid}",
                        'template_id' => "xYsJcFIfIGaS6PX1EnSpqJ68xp-ENHTT6IWzDX7HrCo",
                        'data' => array(
                            'first' => array(
                                "value" => "{$first}",
                            ),
                            'keyword1' => array(
                                "value" => "{$$value['username']}",
                            ),
                            'keyword2' => array(
                                "value" => "{$value['orderdetails']['licensenumber']}",
                            ),
                            'keyword3' => array(
                                "value" => "{$value['models_name']}",
                                "color" => '#FF5722'
                            ),
                            'keyword4' => array(
                                "value" => "{$time}",
                                "color" => '#FF5722'
                            ),
                            "remark" => array(
                                "value" => "请及时处理",
                            )
                            
                        ),
                    );
                    
                    $res = $this->sendXcxTemplateMsg(json_encode($temp_msg));
                   
                }
    
            }
            
            $this->success();
        }

    }


}
