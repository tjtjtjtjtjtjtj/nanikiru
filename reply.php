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

                // コメントが投稿された場合
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_reply'])) {
                    $user_id = $_SESSION['user']['id'];
                    $post_id = $_POST['post_id'];
                    $content = $_POST['content'];

                    $sql = "INSERT INTO replies (user_id, post_id, content) VALUES ('$user_id', '$post_id', '$content')";

                    if ($conn->query($sql) === TRUE) {

                        // コメント後にコメント画面を再表示
                        echo '<script>';
                        echo 'const postId = ' . $post_id . ';';
                        echo 'const currentUrl = new URL(window.location.href);';
                        echo 'if (currentUrl.searchParams.get("post_id") !== postId) {';
                        echo '    currentUrl.searchParams.set("post_id", postId);';
                        echo '    window.location.href = currentUrl.href;'; // ページを再読み込みしてコメント対象の投稿の内容を再表示
                        echo '}';
                        echo '</script>';
                        exit;
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                }

                // 返信対象の投稿を取得
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
                        
                        //投票ボタン
                        echo ' <a href="vote.php?post_id=' . $row['post_id'] . '">投票</a>';
                        echo '<hr>';
                    }
                }

                // コメントフォーム
                if (isset($_GET['post_id'])) {
                    $post_id = $_GET['post_id'];
                    echo '<h2>コメントフォーム</h2>';
                    echo '<form action="" method="post">';
                    echo '<textarea name="content" rows="4" cols="50" required></textarea>';
                    echo '<br>';
                    echo '<input type="hidden" name="post_id" value="' . $post_id . '">';
                    echo '<input type="submit" name="post_reply" value="送信">';
                    echo '</form>';
                    echo '<hr>';
                }
                
                // コメント一覧取得
                if (isset($_GET['post_id'])) {
                    $post_id = $_GET['post_id'];

                    $sql = "SELECT replies.*, users.user_name FROM replies JOIN users ON replies.user_id = users.user_id WHERE post_id = $post_id ORDER BY created_at DESC";
                    $result = $conn->query($sql);

                    // コメントが存在する場合のみコメント一覧を表示
                    if ($result->num_rows > 0) {
                        echo '<h2>コメント一覧</h2>';
                        
                        while ($row = $result->fetch_assoc()) {
                            echo '<div>';
                            echo '<p>' . $row['user_name'] . ' さんのコメント：</p>';
                            echo '<p>' . nl2br(htmlspecialchars($row['content'])) . '</p>';
                            echo '<p>' . $row['created_at'] . '</p>';

                            // コメントフォームを表示ボタン
                            echo '<button class="reply-button" data-reply-id="' . $row['reply_id'] . '" data-user-name="' . $row['user_name'] . '">コメントフォームを表示</button>';

                            // コメントに対するコメントフォーム（初期状態は非表示）
                            echo '<form class="reply-form" action="" method="post" style="display: none;">';
                            echo '<textarea name="content" rows="4" cols="50" required></textarea>';
                            echo '<br>';
                            echo '<input type="hidden" name="post_id" value="' . $post_id . '">';
                            echo '<input type="hidden" name="reply_id" value="' . $row['reply_id'] . '">';
                            echo '<input type="hidden" name="user_name" value="' . $row['user_name'] . '">';
                            echo '<input type="submit" name="post_reply" value="送信">';
                            echo '</form>';
                            echo '<hr>';
                            echo '</div>';
                        }
                    }
                }
            
            ?>
        </section>
        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
        <script>
            // JavaScriptで送信ボタンとフォームの表示・非表示を切り替える
            const replyButtons = document.querySelectorAll('.reply-button');
            replyButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const replyForm = button.nextElementSibling;
                    const userName = button.getAttribute('data-user-name');
                    const textArea = replyForm.querySelector('textarea');
                    textArea.value = `>>${userName} `; // ユーザー名をデフォルト値として設定
                    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                });
            });
        </script>
    </body>
</html>