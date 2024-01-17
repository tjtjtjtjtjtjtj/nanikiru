<?php
    session_start();

    // データベース接続
    $pdo = new PDO('mysql:host=localhost;dbname=nanikiru;charset=utf8', 'root', '');

    $registration_error_name = '';
    $registration_error_email = '';
    $registration_error_password = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // ユーザー名が入力されていない場合
        if (empty($name)) {
            $registration_error_name = 'ユーザー名を入力してください。';
        }

        // メールアドレスが入力されていない場合
        if (empty($email)) {
            $registration_error_email = 'メールアドレスを入力してください。';
        }

        // パスワードが入力されていない場合
        if (empty($_POST['password'])) {
            $registration_error_password = 'パスワードを入力してください。';
        }

        // エラーがない場合の処理
        if (empty($registration_error_name) && empty($registration_error_email) && empty($registration_error_password)) {

            // ユーザーが存在しない場合
            $sql = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
            $sql->execute([$name]);

            if (empty($sql->fetchAll())) {
                
                // ユーザー情報を登録
                $sql = $pdo->prepare('INSERT INTO users (user_name, email, password) VALUES (?, ?, ?)');
                $sql->execute([$name, $email, $password]);

                // ユーザー情報をセッションに保存
                $_SESSION['user'] = [
                    'id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                ];

                // ユーザー登録成功のフラグを設定
                $_SESSION['registration_success'] = true;

                header("Location: home.php");
                exit();
            } else {
                $registration_error_name = '既に存在しているユーザー名です。';
            }
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
            <h2>新規ユーザー登録</h2><hr>

            <!-- ユーザー登録フォーム -->
            <form action="" method="post">
                <label for="name">ユーザー名:</label>
                <input type="text" name="name" required>
                <?php if (!empty($registration_error_name)) { echo '<p>' . $registration_error_name . '</p>'; } ?>
                <br>
                <label for="email">メールアドレス:</label>
                <input type="text" name="email" required>
                <?php if (!empty($registration_error_email)) { echo '<p>' . $registration_error_email . '</p>'; } ?>
                <br>
                <label for="password">パスワード:</label>
                <input type="password" name="password" required>
                <?php if (!empty($registration_error_password)) { echo '<p>' . $registration_error_password . '</p>'; } ?>
                <br>
                <input type="submit" name="register" value="登録">
            </form>

        </section>

        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
    </body>
</html>

