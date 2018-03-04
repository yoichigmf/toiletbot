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



   if ($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage) {  // Location event
   
    
        $log->addWarning("location event event!\n");

        $address = $event->getAddress();
        
        $lat = $event->getLatitude();
        $lon = $event->getLongitude();
        
        $retar = GetToiletIndex( $lat, $lon );
        
        $log->addWarning($retar);
        
        $bot->replyText($event->getReplyToken(), "location event ${address}");
     //  firstmessage( $bot, $event,0);
       continue;
   
   }
   
   

   if ($event instanceof \LINE\LINEBot\Event\JoinEvent) {  // Join event add
   
    
    $log->addWarning("join event!\n");
    $bot->replyText($event->getReplyToken(), "ありがとう");
     //  firstmessage( $bot, $event,0);
       continue;
   
   }
   
   
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent) ||
      !($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
      
      if (!($event instanceof \LINE\LINEBot\Event\PostbackEvent) ) {
         $bot->replyText($event->getReplyToken(), " event");
         
             continue;
      }
     else  {
     
       $bot->replyText($event->getReplyToken(), "post back event");
        }
     
      }
    
 
   
        $bot->replyText($event->getReplyToken(), "位置情報を送ると近くのトイレを探します  line://nv/location ");
        
   }


//  緯度経度情報から近隣トイレのインデックス情報を返す

function  GetToiletIndex( $lat, $lon ) {

// cx 入力  経度
//  cy 入力  緯度

//  bxo ボックス　緯度　最小値
//  byo ボックス　経度　最小値
//  bxc ボックス　緯度　最大値 
//  byc ボックス　経度　最大値
//  iwidth   イメージ幅
//  iheight  イメージ高さ

global $log;

$bxo = 139.2630463;
$byo = 35.58720779;
$byc =  35.8024559;
$bxc = 139.9567566;
$iheight = 330;
$iwidth = 1063;



$cx = $lon;
$cy = $lat;
 
$dx = $cx - $bxo;
$dy = $cy - $byo;

  $log->addWarning("x ${cx}  y ${cy} \n");

 $log->addWarning("dx ${dx}  dy ${dy} \n");
$px = ($dx / ($bxc - $bxo) )* $iwidth;

$py = $iheight -( ( $dy/ ( $byc - $byo )) * $iheight );

$log->addWarning("px ${px}  py ${py} \n");

$px = round( $px );
$py = round( $py );


$turl = "http://tk2-207-13336.vs.sakura.ne.jp/geoserver/toilet/wms?service=WMS&version=1.1.0&request=GetFeatureInfo&layers=toilet:boronoi&query_layers=toilet:boronoi&styles=&bbox=139.263046264648,35.5872077941895,139.956756591797,35.8024559020996&width=1063&height=330&srs=EPSG:4326&info_format=application/json&x=${px}&y=${py}";


  $timeout = "200";
  $log->addWarning("url  ${turl}\n");

   $retar = getApiDataCurl($turl, $timeout );
   
   return $retar;
   
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
