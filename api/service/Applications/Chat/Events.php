<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose
 */
use \GatewayWorker\Lib\Gateway;

class Events
{

    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message)
    {
        // debug
        // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:" . json_encode($_SESSION) . " onMessage:" . $message . "\n";

        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if (!$message_data) {
            return;
        }

        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            case 'customerLogin':
                $customer_id = $message_data['customer_id'];
                $merchant_id = $message_data['merchant_id'];
                $client_name = $message_data['client_name'];
                $_SESSION['type'] = 'customer';
                $_SESSION['merchant_id'] = $merchant_id;
                $_SESSION['customer_id'] = $customer_id;
                $_SESSION['client_name'] = $client_name;
                Gateway::joinGroup($client_id, $merchant_id . '_customer');

                // 通知客服当前在线人数
                $count = Gateway::getClientCountByGroup($merchant_id);
                $new_message = array('type' => 'notify', 'client_id' => 0, 'client_name' => '系统', 'content' => '当前在线人数:' . $count, 'time' => date('Y-m-d H:i:s'));
                Gateway::sendToGroup($merchant_id . '_customer', json_encode($new_message, JSON_UNESCAPED_UNICODE));
                break;

            case 'userLogin':
                echo 'userLogin', PHP_EOL;
                // 判断是否有房间号
                if (!isset($message_data['room_id'])) {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // user_id merchant_id
                $room_id = $message_data['user_id'];
                $merchant_id = $message_data['merchant_id'];
                $client_name = $message_data['client_name'];
                $_SESSION['type'] = 'user';
                $_SESSION['room_id'] = $room_id;
                $_SESSION['merchant_id'] = $merchant_id;
                $_SESSION['client_name'] = $client_name;

                // 加入
                Gateway::joinGroup($client_id, $merchant_id . '_' . $room_id);
                $new_message = array('type' => 'notify', 'client_id' => 0, 'client_name' => '系统', 'content' => $client_name . '进入房间', 'time' => date('Y-m-d H:i:s'));
                Gateway::sendToGroup($merchant_id . '_' . $room_id, json_encode($new_message, JSON_UNESCAPED_UNICODE));

                // 取得当前列表数目
                $clients_list = Gateway::getClientSessionsByGroup($merchant_id . '_' . $room_id);

                if (count($clients_list) != 2) {
                    // print_r($clients_list);
                    // 取得客服列表
                    $customer_clients_list = Gateway::getClientSessionsByGroup($merchant_id . '_customer');
                    echo '$customer_clients_list' . print_r($customer_clients_list, true);
                    $r = rand(0, count($customer_clients_list) - 1);
                    $k = 0;
                    if (count($customer_clients_list) >= 1) {
                        foreach ($customer_clients_list as $client => $item) {
                            if ($k == $r) {
                                $item['client_id'] = $client;
                                $customer_client = $item;
                                break;
                            }
                            $k++;
                        }

                        // $customer_client = $customer_clients_list[rand(0, len($customer_clients_list) - 1)];
                        $new_message = array('type' => 'notify', 'client_id' => 0, 'client_name' => '系统', 'content' => '客服:' . $customer_client['client_name'] . '为你服务！', 'time' => date('Y-m-d H:i:s'));
                        Gateway::sendToGroup($merchant_id . '_' . $room_id, json_encode($new_message, JSON_UNESCAPED_UNICODE));
                        Gateway::joinGroup($customer_client['client_id'], $merchant_id . '_' . $room_id);
                    } else {
                        $new_message = array('type' => 'notify', 'client_id' => 0, 'client_name' => '系统', 'content' => '当前没有在线客服', 'time' => date('Y-m-d H:i:s'));
                        Gateway::sendToGroup($merchant_id . '_' . $room_id, json_encode($new_message, JSON_UNESCAPED_UNICODE));
                        Gateway::joinGroup($client_id, $merchant_id . '_wait');
                    }
                }

                // 加入商户列表统计在线人数
                Gateway::joinGroup($client_id, $merchant_id);

                // 通知客服当前在线人数
                $count = Gateway::getClientCountByGroup($merchant_id . '_' . $room_id);
                $new_message = array('type' => 'notify', 'client_id' => 0, 'client_name' => '系统', 'content' => '当前在线人数:' . $count, 'time' => date('Y-m-d H:i:s'));
                Gateway::sendToGroup($merchant_id . '_customer', json_encode($new_message, JSON_UNESCAPED_UNICODE));
                break;
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if (!isset($message_data['room_id'])) {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;

                // 获取房间内所有用户列表
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach ($clients_list as $tmp_client_id => $item) {
                    $clients_list[$tmp_client_id] = $item['client_name'];
                }
                $clients_list[$client_id] = $client_name;

                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                $new_message = array('type' => $message_data['type'], 'client_id' => $client_id, 'client_name' => htmlspecialchars($client_name), 'time' => date('Y-m-d H:i:s'));
                Gateway::sendToGroup($room_id, json_encode($new_message));
                Gateway::joinGroup($client_id, $room_id);

                // 给当前用户发送用户列表
                $new_message['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;

            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                if (!isset($_SESSION['room_id'])) {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];

                // 私聊
                if ($message_data['to_client_id'] != 'all') {
                    $new_message = array(
                        'type' => 'say',
                        'from_client_id' => $client_id,
                        'from_client_name' => $client_name,
                        'to_client_id' => $message_data['to_client_id'],
                        'content' => "<b>对你说: </b>" . nl2br(htmlspecialchars($message_data['content'])),
                        'time' => date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    $new_message['content'] = "<b>你对" . htmlspecialchars($message_data['to_client_name']) . "说: </b>" . nl2br(htmlspecialchars($message_data['content']));
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }

                $new_message = array(
                    'type' => 'say',
                    'from_client_id' => $client_id,
                    'from_client_name' => $client_name,
                    'to_client_id' => 'all',
                    'content' => nl2br(htmlspecialchars($message_data['content'])),
                    'time' => date('Y-m-d H:i:s'),
                );
                return Gateway::sendToGroup($room_id, json_encode($new_message));
        }
    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // debug
        // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";

        // 从房间的客户端列表中删除
        // if (isset($_SESSION['room_id'])) {
        //     $room_id = $_SESSION['room_id'];
        //     $new_message = array('type' => 'logout', 'from_client_id' => $client_id, 'from_client_name' => $_SESSION['client_name'], 'time' => date('Y-m-d H:i:s'));
        //     Gateway::sendToGroup($room_id, json_encode($new_message));
        // }
    }

}
