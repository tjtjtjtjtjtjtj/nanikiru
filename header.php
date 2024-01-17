<header>
    <h1 onclick="location.href='home.php'">何切る掲示板</h1>
</header>

<nav>
    <?php if (isset($_SESSION['user'])) { ?>
        <a href="post.php">問題作成</a>
        <a href="logout-in.php">ログアウト</a>
    <?php } else { ?>
        <a href="login.php">ログイン</a>
        <a href="user.php">新規登録</a>
    <?php } ?>
</nav>