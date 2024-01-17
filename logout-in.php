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
        <?php require 'header.php'; ?>
        
        <section>
            <p>ログアウトしますか？</p>
            <a href="logout-out.php">はい</a>
            <a href="home.php">いいえ</a>
        </section>

        <footer>
            <p>&copy; 2023 何切る掲示板</p>
        </footer>
    </body>
</html>
