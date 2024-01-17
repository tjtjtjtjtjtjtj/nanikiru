<?php
if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // 投票結果
        echo '<h2>投票結果</h2>';

        // 投票情報を取得
        $sqlVotes = "SELECT vote_image_pass FROM votes WHERE post_id = $row['post_id']";
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
    }
    ?>