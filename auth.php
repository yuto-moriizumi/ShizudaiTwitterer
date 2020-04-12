<?php
const callbackUrl = 'http://volga.e3.valueserver.jp/ShizudaiTwitterer/callback.php';

session_start();
require_once './secret.php';
require_once './autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
//TwitterOAuth をインスタンス化 
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
//コールバックURLセット 
$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => callbackUrl));

//callback.phpで使うのでセッションに入れる
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

//Twitter.com 上の認証画面のURLを取得
$url = $connection->url(
    'oauth/authenticate',
    ['oauth_token' => $request_token['oauth_token']]
);

//移行前アカウント名をセット
$_SESSION['exclusive']=$_REQUEST['exclusive']; 
$_SESSION['dateLeft']=$_REQUEST['dateLeft']; 
$_SESSION['dateRight']=$_REQUEST['dateRight']; 

//Twitter.com の認証画面へリダイレクト
header( 'location: '. $url );