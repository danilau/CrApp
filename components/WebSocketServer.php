<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 23.46
 */
namespace app\components;

use app\models\WSMessageProcessor;
use Yii;
use yii\base\Component;
use React;
use Predis;
use Ratchet\Http\HttpServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class WebSocketServer extends Component{

    public $host = '0.0.0.0';

    public $port = 8080;

    public $redisIpHost = '127.0.0.1';

    public function run(){

        $loop = React\EventLoop\Factory::create();

        $messageProcessor = new WSMessageProcessor();

        $client = new Predis\Async\Client('tcp://'.$this->redisIpHost.':'.Yii::$app->redis->port.'', $loop);

        $client->connect([$messageProcessor,'init']);

        $webSock = new React\Socket\Server($loop);
        $webSock->listen($this->port, $this->host);
        $webServer = new IoServer(new HttpServer(
                            new WsServer(
                                new WampServer(
                                    $messageProcessor
                                )
                            )
        ),
                        $webSock
                    );

        $loop->run();
    }

}