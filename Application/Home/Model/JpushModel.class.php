<?php
namespace Home\Model;
use Think\Model;

class JpushModel extends Model {

    public function __construct() {
        vendor('JPush.Client');
        vendor('JPush.Config');
        vendor('JPush.Http');
        vendor('JPush.PushPayload');
        vendor('JPush.Exceptions.JPushException');
        vendor('JPush.Exceptions.APIRequestException');
        vendor('JPush.Exceptions.APIConnectionException');
    }

    /**
     * 用户发别名消息
     * @param $id
     * @param $title
     * @param $content
     * @param $extras
     */
    public function send_alias($id, $title, $content, $extras) {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);

        $app_key = C('push_AppKey');
        $master_secret = C('push_Secret');

        $client = new \JPush\Client($app_key, $master_secret);
        $push = $client->push();

        $platform = array('ios', 'android');
        $alert = $content;
        $android_notification = array(
            'title' => $title,
            'extras' => $extras,
        );
        $ios_notification = array(
            'sound' => $title,
            'badge' => '+1',
            'content-available' => true,
            'extras' => $extras,
        );
        $message = array(
            'title' => $title,
            'extras' => $extras,
        );
        $options = array(
            'apns_production' => true,
            'time_to_live' => 0
        );
        $push->setPlatform($platform)
            ->addAlias($id)
            ->iosNotification($alert, $ios_notification)
            ->androidNotification($alert, $android_notification)
            ->message($content, $message)
            ->options($options)
            ->send();
    }

    /**
     * 司机发别名消息
     * @param $id
     * @param $title
     * @param $content
     * @param $extras
     */
    public function sj_send_alias($id, $title, $content, $extras) {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);

        $app_key = C('sj_push_AppKey');
        $master_secret = C('sj_push_Secret');

        $client = new \JPush\Client($app_key, $master_secret);
        $push = $client->push();

        $platform = array('ios', 'android');
        $alert = $content;
        $android_notification = array(
            'title' => $title,
            'extras' => $extras,
        );
        $ios_notification = array(
            'sound' => $title,
            'badge' => '+1',
            'content-available' => true,
            'extras' => $extras,
        );
        $message = array(
            'title' => $title,
            'extras' => $extras,
        );
        $options = array(
            'apns_production' => true,
            'time_to_live' => 0
        );
       $r =  $push->setPlatform($platform)
            ->addAlias($id)
            ->iosNotification($alert, $ios_notification)
            ->androidNotification($alert, $android_notification)
            ->message($content, $message)
            ->options($options)
            ->send();

       return $r;
    }

}