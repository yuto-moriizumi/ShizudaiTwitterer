<?php
//定期的にツイートをチェックし、特定のハッシュタグを使ったユーザをjsonに保存する
require_once './secret.php';
require_once './autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$json = file_get_contents('./search.json');
$json=mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$json = json_decode($json,true);

$result=$connection->get('search/tweets',[
    'q'=>'#春から静大',
    'since_id'=>$json['lastId'],
    'count'=>100,
    'result_type'=>'recent',
    'include_entities'=>false
    ]);

$json['lastId']=$result->search_metadata->max_id_str;

var_dump($result);

foreach ($result->statuses as $tweet) {
    $inJson=False;
    $id=$tweet->user->id_str;
    if(!is_null($json['users'][$id])) continue;
    $json["users"][$id]=['tweetDate'=>strtotime($tweet->created_at)];
}
$json=json_encode($json);
file_put_contents("./search.json",$json);
?>