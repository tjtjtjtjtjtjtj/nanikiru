<?php
session_start();

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

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

// フォームが送信された場合
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_content'])) {

    // フォームからのデータ取得
    $user_id = $_SESSION['user']['id'];
    $content = $_POST['content'];

    // 画像アップロード処理
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        // ファイルの一時的な保存先
        $tmp_name = $_FILES['image']['tmp_name'];

        // ファイル名の取得
        $filename = $_FILES['image']['name'];

        // 保存先のディレクトリ
        $upload_dir = 'image/';

        // ファイルの移動
        move_uploaded_file($tmp_name, $upload_dir . $filename);

        // 画像のパスをデータベースに保存
        $image_pass = $upload_dir . $filename;
    } else {

        // 画像がない場合は、$image_passを空にするか、デフォルトの画像URLを設定するなどの対応が可能
        $image_pass = '';
    }

    // 投稿データをデータベースに保存
    $sql = "INSERT INTO posts (user_id, content, image_pass) VALUES ('$user_id', '$content', '$image_pass')";

    if ($conn->query($sql) === TRUE) {

        // 投稿成功のフラグを設定
        $_SESSION['post_success'] = true;

        header("location: home.php");

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>何切る掲示板 - 投稿ページ</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <?php require 'header.php'; ?>

        <section>
            <h2>新しい投稿</h2>

            <!-- 投稿フォーム -->
            <form action="" method="post" enctype="multipart/form-data">
                <label for="content">投稿内容:</label>
                <textarea name="content" rows="4" cols="50" required></textarea>
                <br>
                <label for="image">画像を選択:</label>
                <input type="file" name="image" id="image" accept="image/*">
                <br>
                <input type="submit" name="post_content" value="投稿">
            </form>

        </section>

        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
    </body>
</html>