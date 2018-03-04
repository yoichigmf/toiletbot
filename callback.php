<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/vendor/autoload.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));


$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('LineMessageAPIChannelAccessToken'));

$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('LineMessageAPIChannelSecret')]);

$sign = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

$events = $bot->parseEventRequest(file_get_contents('php://input'), $sign);

$page = 1;
$action ="";

$score = -1;
//require "menus.php"; //menus.phpのプログラムを使うよ



foreach ($events as $event) {


   if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {  //  text message
   
        $query = $event->getPostbackData();
        $bot->replyText($event->getReplyToken(), $query);

         continue;
   }

   if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {  // Location event
   
    
    $log->addWarning("location event event!\n");

        $bot->replyText($event->getReplyToken(), "location event");
     //  firstmessage( $bot, $event,0);
       continue;
   
   }
   
   

   if ($event instanceof \LINE\LINEBot\Event\JoinEvent) {  // Join event add
   
    
    $log->addWarning("join event!\n");
    $bot->replyText($event->getReplyToken(), "ありがとう");
     //  firstmessage( $bot, $event,0);
       continue;
   
   }
    
   $log->addWarning("not join event \n");
   
  
        
   }



//  Google Sheet から列を取得する

function getApiDataCurl($url, $timeout )
{
   
global $log;


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );

curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
//最大何回リダイレクトをたどるか
curl_setopt($ch,CURLOPT_MAXREDIRS,10);
//リダイレクトの際にヘッダのRefererを自動的に追加させる
curl_setopt($ch,CURLOPT_AUTOREFERER,true);

$content = trim(curl_exec($ch));
    

    $info    = curl_getinfo($ch);
    $errorNo = curl_errno($ch);
    
    curl_close($ch);
    
    

    //p
    
    
    // OK以外はエラーなので空白配列を返す
    if ($errorNo !== CURLE_OK) {
$log->addWarning("error status  ${errorNo}\n");
        return [];
    }

    // 200以外のステータスコードは失敗とみなし空配列を返す
    if ($info['http_code'] !== 200) {
    $erno = $info['http_code'];
   $log->addWarning("http error status  ${erno}\n");
        return [];
    }

   // print "\nok\n";
     $log->addWarning( "success content = ${content}\n" );
    

    // 文字列から変換
    $jsonArray = json_decode($content, true);

    return $jsonArray;
}
















?>
