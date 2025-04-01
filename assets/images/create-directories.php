<?php
// プラグインのイメージディレクトリを作成するための一時ファイル
// このファイルはサーバーにアップロードした後、手動で削除してください

// 直接アクセスを禁止
if (!defined('ABSPATH')) {
    echo "直接アクセスは禁止されています";
    exit;
}

// imagesディレクトリが存在しない場合は作成
$image_dir = dirname(__FILE__);
if (!file_exists($image_dir)) {
    mkdir($image_dir, 0755, true);
}

echo "イメージディレクトリを作成しました: " . $image_dir;
?>