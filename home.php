<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>何切る掲示板</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php 
        require 'header.php';

        // ログイン成功メッセージがあれば表示
        if (isset($_SESSION['login_success'])) {
            echo '<p class="message">ログインしました。</p>';
            // フラグをクリア
            unset($_SESSION['login_success']);
        }

        // ログアウト成功メッセージがあれば表示
        if (isset($_SESSION['logout_success'])) {
            echo '<p class="message">ログアウトしました。</p>';
            // フラグをクリア
            unset($_SESSION['logout_success']);
        }

        // ユーザー登録成功メッセージがあれば表示
        if (isset($_SESSION['registration_success'])) {
            echo '<p class="message">ユーザーを登録しました。</p>';
            // フラグをクリア
            unset($_SESSION['registration_success']);
        }

        // 投稿成功メッセージがあれば表示
        if (isset($_SESSION['post_success'])) {
            echo '<p class="message">投稿しました。</p>';
            // フラグをクリア
            unset($_SESSION['post_success']);
        }
    ?>

    <section>
        <h2>投稿一覧</h2>

        <?php
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

        // 投稿一覧取得
        $sql = "SELECT posts.*, users.user_name FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY created_at DESC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo '<div>';
            echo '<p>' . $row['user_name'] . ' さんの投稿：</p>';
            echo '<p>' . $row['content'] . '</p>';

            // 画像があれば表示
            if ($row['image_pass']) {
                echo '<img src="' . $row['image_pass'] . '" alt="投稿画像">';
            }

            echo '<p>' . $row['created_at'] . '</p>';

            if (isset($_SESSION['user'])) {
                echo '<a href="reply.php?post_id=' . $row['post_id'] . '">返信</a>'; // 返信ボタン
                echo ' <a href="vote.php?post_id=' . $row['post_id'] . '">投票</a>'; // 投票ボタン
            }

            // 投票結果表示
            $voteCounts = getVoteCounts($row['post_id']);
            displayVoteResults($voteCounts);

            echo '<hr>';
            echo '</div>';
        }

        $conn->close();

        function getVoteCounts($postId) {
            $voteCounts = array();
            $imageFolder = 'pai_image/';
            $images = glob($imageFolder . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

            foreach ($images as $image) {
                $voteCounts[$image] = 0;
            }

            $conn = new mysqli("localhost", "root", "", "nanikiru");
            $sqlVotes = "SELECT vote_image_pass FROM votes WHERE post_id = $postId";
            $resultVotes = $conn->query($sqlVotes);

            if ($resultVotes->num_rows > 0) {
                while ($rowVotes = $resultVotes->fetch_assoc()) {
                    $voteCounts[$rowVotes['vote_image_pass']]++;
                }
            }

            $conn->close();
            return $voteCounts;
        }

        function displayVoteResults($voteCounts) {
            echo '<h3>投票結果</h3>';

            $nonZeroVotes = array_filter($voteCounts, function ($count) {
                return $count > 0;
            });

            if (count($nonZeroVotes) > 0) {
                arsort($nonZeroVotes);
                echo '<ol>';
                foreach ($nonZeroVotes as $image => $count) {
                    echo '<li>';
                    echo '<img src="' . $image . '" alt="投稿画像" style="max-width: 100px; max-height: 100px;">';
                    echo ' - 投票数: ' . $count;
                    echo '</li>';
                }
                echo '</ol>';
            } else {
                echo '<p>まだ投票がありません。</p>';
            }
        }
        ?>

    </section>

    <footer>
        <p>&copy; 2023 何切る掲示板
