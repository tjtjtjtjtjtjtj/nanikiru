<?php
session_start();

// データベース接続設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nanikiru";

$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ログインしているか確認
if (!isset($_SESSION['user'])) {

    // ログインしていない場合はログインページにリダイレクト
    header("Location: login.php");
    exit();
}

// ユーザーID取得
$user_id = $_SESSION['user']['id'];

// 投票された画像URLと投稿IDを取得
if (isset($_GET['post_id']) && isset($_GET['image_pass'])) {
    $post_id = $_GET['post_id'];
    $image_pass = urldecode($_GET['image_pass']);

    // 同じユーザーが同じ投稿に複数回投票できないようにする
    $checkVoteSql = "SELECT * FROM votes WHERE user_id = $user_id AND post_id = $post_id";
    $checkVoteResult = $conn->query($checkVoteSql);

    if ($checkVoteResult->num_rows == 0) {

        // 投票情報を挿入
        $insertVoteSql = "INSERT INTO votes (vote_image_pass, user_id, post_id) VALUES ('$image_pass', $user_id, $post_id)";
        if ($conn->query($insertVoteSql) === TRUE) {

            // 投票成功時の処理
            $_SESSION['vote_success'] = true;
        } else {

            // 投票失敗時の処理
            $_SESSION['vote_error'] = "投票に失敗しました。";
        }
    } else {

        // 既に投票している場合の処理
        $_SESSION['vote_error'] = "既に投票済みです。";
    }

    // 元の投稿ページにリダイレクト
    header("Location: vote.php?post_id=$post_id");
    exit();
} else {

    // パラメータが足りない場合のエラー処理
    $_SESSION['vote_error'] = "不正なリクエストです。";
    header("Location: home.php"); // トップページにリダイレクト
    exit();
}

$conn->close();
?>
