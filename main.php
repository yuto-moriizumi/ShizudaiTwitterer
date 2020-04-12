<?php
session_start();

require_once './secret.php';
require_once './autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//セッションに入れておいたさっきの配列
$access_token = $_SESSION['access_token'];

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

$myAccount=$connection->get('account/settings',[])->screen_name;

$follows=$connection->get(
    'friends/ids',
    [
        'screen_name'=>$myAccount,
    ]
)->ids;

$json = file_get_contents('./search.json');
$json=mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$json = json_decode($json,true);

$afterThisDate=strtotime($_SESSION['dateLeft']);
$beforeThisDate=strtotime($_SESSION['dateRight']);

$after=array_filter($json['users'],function ($user){
    return $afterThisDate<=$user['tweetDate'] && $user['tweetDate']<$beforeThisDate;
});
$candidates =array_diff(array_keys($after),$follows);

//ブロックしているユーザをフォロー候補から除外する
$blocks=$connection->get('blocks/ids',[])->ids;
$candidates2 =array_diff($candidates,$blocks);

//除外リストに指定されたユーザを除外する
$exclusives=explode(',',$_SESSION['exclusive']);
$candidates2 =array_diff($candidates2,$exclusives);

?>
<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>静大新入生Twitterer補足装置</title>
</head>
<body>
    <h1>自動フォローページ</h1>
    <p>このシステムに登録されている静大生ツイッタラーは<?=count($json['users'])?>人です</p>
    <p>そのうち、指定された日付以降であなたがまだフォローしていない人は<?=count($candidates)?>人でした</p>
    <p>ブロックしているユーザや除外リストのユーザを除外した結果、フォロー候補は<?=count($candidates2)?>人でした</p>

<?php
$ans=0;
$failedUserIds=[];
foreach ($candidates2 as $user){
    $result=$connection->post('friendships/create',[
        'user_id'=>$user,
    ]);
    if(isset($result->errors)){
        if($result->errors[0]->code!==161) { //フォロー制限でないエラーならば
            echo '<p>エラーが発生しました：<br>';
            var_dump($result->errors);
            echo '</p>';
            array_push($failedUserIds,$user);
            continue;
        }
        echo '<p>処理中にフォロー上限に達しました。時間を空けてから再度アクセスしてください。</p>';        
        break;
    }
    $ans++;
    usleep(100);
}
echo '<p>処理が終了しました。</p><p>フォローした数：'.$ans.'</p>';
echo '<p>フォローに失敗したユーザ：'.implode($failedUserIds,',').'</p>';
?>
</body>
</html>