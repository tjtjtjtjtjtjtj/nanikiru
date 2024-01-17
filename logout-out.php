<?php
    session_start();
    if (isset($_SESSION['user'])) {
        unset($_SESSION['user']);

        // ログアウト成功のフラグを設定
        $_SESSION['logout_success'] = true;

        header("location: home.php");
    } else {
        echo 'すでにログアウトしています。';
    }

?>