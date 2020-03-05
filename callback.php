<?php
session_start();
require_once './secret.php';
require_once './autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//login.phpでセットしたセッション
$request_token = [];

$request_token['oauth_token'] = $_SESSION['oauth_token']; $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];
//Twitterから返されたOAuthトークンと、あらかじめlogin.phpで入れておいたセッション上のものと一致するかをチェック
if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) { die( 'Error!' ); }
//OAuth トークンも用いて TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

//配列access_tokenにOauthトークンとTokenSecretを入れる
$_SESSION['access_token'] = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

//セッションIDをリジェネレート
session_regenerate_id();

//リダイレクト
header( 'location: ./main.php' );

?>