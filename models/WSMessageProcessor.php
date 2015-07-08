<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 28.6.15
 * Time: 0.58
 */
namespace app\models;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Predis\Async\Client;
use yii\helpers\Json;

class WSMessageProcessor implements WampServerInterface {

    public $subscribedTopics = array();


    protected $redis;

    private $prevArguments;

    public function init($client) {
        $this->redis = $client;
        $this->redis->monitor(function ($event) {
            $arguments = str_replace('"','',$event->arguments);
            $args = explode(' ',$arguments);

            if($args[0]=='global:classroom:updateTS'){
                $message = $this->createMessage($this->prevArguments);
                foreach($this->subscribedTopics as $topic){
                    $topic->broadcast($message);
                }
            }

            $this->prevArguments = $arguments;

        });
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
            $this->subscribedTopics[$topic->getId()] = $topic;
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {

    }

    public function onOpen(ConnectionInterface $conn) {

    }

    public function onClose(ConnectionInterface $conn) {

    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $conn->close();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {

    }

    private function createMessage($arguments){

        $message = [];
        $message['type'] = 'class_config_changed';

        $args = explode(' ',$arguments);
        $numOfArgs = count($args);

        if($numOfArgs==3){
            //HandMoving
            $message['type'] = 'student_state_changed';
            $message['student'] = [];
            $message['student']['id'] = explode(':',$args[0])[3];
            $user = User::findOne($message['student']['id']);
            $message['student']['name'] = $user->getAttribute('username');
            $message['student']['handState'] = $user->getAttribute('handState');
        }else{
            $message['members'] = [];
            $users = User::findAll(null);
            foreach($users as $user){
                $member = [];
                $member['id'] = $user->getAttribute('id');
                $member['name'] = $user->getAttribute('username');
                $member['handState'] = $user->getAttribute('handState');
                $message['members'] []= $member;
            }

        }

        return Json::encode($message);
    }
}