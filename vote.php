<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>何切る掲示板 - コメントページ</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <?php require 'header.php'; ?>
        <section>
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

                // 投票対象の投稿を取得
                if (isset($_GET['post_id'])) {
                    $post_id = $_GET['post_id'];
                    $sql = "SELECT posts.*, users.user_name FROM posts JOIN users ON posts.user_id = users.user_id WHERE post_id = $post_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        echo '<h2>投稿内容</h2>';
                        echo '<p>' . $row['user_name'] . ' さんの投稿：</p>';
                        echo '<p>' . nl2br(htmlspecialchars($row['content'])) . '</p>';

                        // 画像があれば表示
                        if ($row['image_pass']) {
                            echo '<img src="' . $row['image_pass'] . '" alt="投稿画像">';
                        }

                        echo '<p>' . $row['created_at'] . '</p>';

                        // 返信ボタン
                        echo '<a href="reply.php?post_id=' . $row['post_id'] . '">返信</a>';
                        echo '<hr>';

                        // 投票結果
                        echo '<h2>投票結果</h2>';

                        // 投票情報を取得
                        $sqlVotes = "SELECT vote_image_pass FROM votes WHERE post_id = $post_id";
                        $resultVotes = $conn->query($sqlVotes);

                        // 投票数を初期化
                        $voteCounts = array();

                        // 画像ごとに初期化
                        $imageFolder = 'pai_image/';  // 画像フォルダのパス
                        $images = glob($imageFolder . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                        foreach ($images as $image) {
                            $voteCounts[$image] = 0;
                        }

                        // 画像が存在する場合
                        if ($images) {
                            if ($resultVotes->num_rows > 0) {

                                while ($rowVotes = $resultVotes->fetch_assoc()) {

                                    if (isset($voteCounts[$rowVotes['vote_image_pass']])) {
                                        $voteCounts[$rowVotes['vote_image_pass']]++;
                                    }
                                }

                                // 投票数が0でない画像のみ抽出
                                $nonZeroVotes = array_filter($voteCounts, function ($count) {
                                    return $count > 0;
                                });

                                // 投票数の多い順にソート
                                arsort($nonZeroVotes);

                                // ランキング表示
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
                        } else {
                            echo '<p>画像がありません。</p>';
                        }
                        echo '<hr>';

                        // 投票
                        echo '<h2>投票</h2>';

                        // ユーザーがこの投稿に既に投票しているか確認
                        $user_id = $_SESSION['user']['id'];
                        $sqlCheckVote = "SELECT COUNT(*) as count, vote_image_pass FROM votes WHERE post_id = $post_id AND user_id = $user_id GROUP BY vote_image_pass";
                        $resultCheckVote = $conn->query($sqlCheckVote);

                        // 既に投票している場合
                        if ($resultCheckVote->num_rows > 0) {
                            echo '<p>投票済みです。</p>';
                            
                            while ($rowCheckVote = $resultCheckVote->fetch_assoc()) {
                                // 投票した画像を表示
                                echo '<div style="display: inline-block; margin: 10px;">';
                                echo '<img src="' . $rowCheckVote['vote_image_pass'] . '" alt="投稿画像" style="max-width: 200px; max-height: 200px;">';
                                echo '</div>';
                            }
                        }else{

                            // 画像フォルダから画像一覧を取得
                            $imageFolder = 'pai_image/';  // 画像フォルダのパス（適切に変更してください）
                            $images = glob($imageFolder . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                            // 画像が存在する場合
                            if ($images) {
                                foreach ($images as $image) {

                                    // 画像を表示し、クリック時に投票処理を行うリンクを生成
                                    echo '<div style="display: inline-block; margin: 10px;">';
                                    echo '<img src="' . $image . '" alt="投稿画像" style="max-width: 200px; max-height: 200px;">';
                                    echo '<br>';
                                    echo '<a href="#" onclick="confirmVote(\'' . $post_id . '\', \'' . urlencode($image) . '\')">投票</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>画像がありません。</p>';
                            }
                        }
                    }
                }
            ?>
            <script>
                function confirmVote(post_id, image_pass) {
                    var confirmVote = confirm("本当に投票しますか？");
                    if (confirmVote) {
                        window.location.href = "vote-process.php?post_id=" + post_id + "&image_pass=" + image_pass;
                    }
                }
            </script>
        </section>
        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
    </body>
</html>
