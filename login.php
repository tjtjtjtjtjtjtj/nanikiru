<?php
session_start();

// ログインしている場合はリダイレクト
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

// ログイン処理
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=nanikiru;charset=utf8', 'root', '');

        $name = $_POST['name'];
        $password = $_POST['password'];

        $params = [
            ':name' => $name,
        ];

        $sql = $pdo->prepare('SELECT * FROM users WHERE user_name = :name');
        $sql->execute($params);

        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if ($row && (password_verify($password, $row['password']))) {

            // 認証成功
            $_SESSION['user'] = [
                'id' => $row['user_id'],
                'name' => $row['user_name'],
                'email' => $row['email'],
            ];

            // ログイン成功のフラグを設定
            $_SESSION['login_success'] = true;

            header("Location: home.php");
            exit();

        } else {

            // ログイン失敗
            $login_error = 'ログイン名またはパスワードが違います。';
        }
    } catch (PDOException $e) {

        // エラーハンドリング
        $login_error = 'データベースエラー: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>何切る掲示板</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <?php require 'header.php'; ?>

        <section>
            <h2>ログイン</h2>

            <!-- ログインフォーム -->
            <form action="" method="post">
                <label for="name">ユーザー名:</label>
                <input type="text" name="name" required>
                <br>
                <label for="password">パスワード:</label>
                <input type="password" name="password" required>
                <br>
                <input type="submit" name="login" value="ログイン">
            </form>

            <?php
            
            // ログインエラーがあれば表示
            if (isset($login_error)) {
                echo '<p>' . $login_error . '</p>';
            }
            ?>

        </section>

        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
    </body>
</html>
