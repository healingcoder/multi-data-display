<?php
if ( ! defined( 'ABSPATH' ) ) exit; // 直接アクセスを防止

function create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops'; // プレフィックスを考慮したテーブル名
    $charset_collate = $wpdb->get_charset_collate();

    // テーブルが存在しない場合に作成
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            url VARCHAR(100) NOT NULL,
            sname VARCHAR(100) NOT NULL,
            price TEXT NOT NULL,
            surl VARCHAR(50) NOT NULL,
            area TEXT NOT NULL,
            service TEXT NOT NULL,
            copy LONGTEXT NOT NULL,
            comment LONGTEXT NOT NULL,
            time TEXT NOT NULL,
            tel TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            update_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            sogo INT(11) NOT NULL DEFAULT 0,
            point INT(11) NOT NULL DEFAULT 0,
            premium INT(11) NOT NULL DEFAULT 0,
            type INT(11) NOT NULL DEFAULT 0,
            recommended LONGTEXT NOT NULL,
            PRIMARY KEY (surl)
        ) ENGINE=InnoDB $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
?>
