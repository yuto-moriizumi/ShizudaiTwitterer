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

$candidates =array_diff(array_keys($json['users']),$follows);

//ブロックしているユーザをフォロー候補から除外する
$blocks=$connection->get('blocks/ids',[])->ids;
$candidates =array_diff($candidates,$blocks);

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
    <p>そのうち、あなたがまだフォローしていない人は<?=count($candidates)?>人でした</p>


<?php
$ans=0;
foreach ($candidates as $user){
    $result=$connection->post('friendships/create',[
        'user_id'=>$user,
    ]);
    if(isset($result->errors)){
        if($result->errors[0]->code!==161) { //フォロー制限でないエラーならば
            echo '<p>エラーが発生しました：<br>';
            var_dump($result->errors);
            echo '</p>';
            continue;
        }
        echo '<p>処理中にフォロー上限に達しました。時間を空けてから再度アクセスしてください。</p>';        
        break;
    }
    $ans++;
    usleep(10);
}
echo '<p>処理が終了しました。</p><p>フォローした数：'.$ans.'</p>';
?>
</body>
</html>