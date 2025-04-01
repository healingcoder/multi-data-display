<?php
/**
 * テンプレート名: エリア編集
 *
 * 管理者向けの店舗データ編集ページ
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) exit;

// 管理者権限の確認
if (!current_user_can('administrator')) {
    auth_redirect();
    exit;
}

get_header(); 
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="mdd-container">
            <header class="entry-header">
                <h1 class="entry-title">
                    <?php 
                    if (!empty($_GET['surl'])) {
                        echo '店舗データ編集';
                    } elseif (!empty($_GET['option']) && $_GET['option'] == 'add') {
                        echo '店舗データ新規追加';
                    } else {
                        echo '店舗データ管理';
                    }
                    ?>
                </h1>
            </header>

            <div class="entry-content">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'shops';
                
                // 画像アップロードメッセージの表示
                if (isset($_GET['message'])) {
                    $error_detail = isset($_GET['error']) ? urldecode($_GET['error']) : '';
                    echo mdd_display_image_upload_message($_GET['message'], $error_detail);
                }
                
                // 処理メッセージの表示領域
                if (isset($_POST['chan']) || isset($_POST['del']) || isset($_POST['add'])) {
                    echo '<div id="message" class="updated notice is-dismissible">';
                    if (isset($_POST['chan'])) {
                        $message = save_record($wpdb, $table_name, 'surl', array('%s'), array(
                            'url', 'sname', 'price', 'surl', 'area', 'service', 'copy', 'comment', 'time', 'tel', 'sogo', 'point', 'premium', 'type', 'recommended'
                        ), array(
                            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s'
                        ));
                        echo '<p>' . $message . '</p>';
                    } elseif (isset($_POST['del'])) {
                        $message = delete_record($wpdb, $table_name, 'surl');
                        echo '<p>' . $message . '</p>';
                        // 削除後はリストページにリダイレクト
                        echo '<script>window.location.href = "' . site_url('area/list/') . '";</script>';
                    } elseif (isset($_POST['add'])) {
                        $message = add_record($wpdb, $table_name, array(
                            'url', 'sname', 'price', 'surl', 'area', 'service', 'copy', 'comment', 'time', 'tel', 'sogo', 'point', 'premium', 'type', 'recommended'
                        ), array(
                            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s'
                        ));
                        echo '<p>' . $message . '</p>';
                        
                        // 追加後は追加したデータの編集画面にリダイレクト
                        if (strpos($message, 'エラー') === false && isset($_POST['surl'])) {
                            $new_surl = sanitize_text_field($_POST['surl']);
                            echo '<script>window.location.href = "' . site_url('area/edit/' . $new_surl) . '";</script>';
                        }
                    }
                    echo '</div>';
                }
                
                // 常にリストへ戻るリンクを表示
                echo '<div class="mdd-nav-links">';
                echo '<a href="' . site_url('area/list/') . '" class="button">リストに戻る</a>';
                echo '</div>';
                
                // URLの分岐を行う
                if (!empty($_GET['surl']) || !empty(get_query_var('surl'))) {
                    // $_GET['surl']または直接クエリ変数から取得
                    $shop_key = !empty($_GET['surl']) ? 
                                sanitize_text_field($_GET['surl']) : 
                                sanitize_text_field(get_query_var('surl'));
                    
                    // プレースホルダーを正しく使用
                    $prepare_query = $wpdb->prepare("SELECT * FROM $table_name WHERE surl = %s", $shop_key);
                    $shop = $wpdb->get_row($prepare_query);
                    
                    if ($shop) {
                        // 変更用HTML
                        display_edit_form($shop);
                        
                        // 画像アップロードフォームを表示（データ保存後にのみ表示）
                        echo '<div class="mdd-section">';
                        echo '<h2 class="is-style-vk-heading-solid_black">店舗画像</h2>';
                        echo mdd_image_upload_form($shop->surl);
                        echo '</div>';
                    } else {
                        echo '<div class="notice notice-error">';
                        echo '<p>指定された店舗が見つかりませんでした。(surl: ' . esc_html($shop_key) . ')</p>';
                        echo '</div>';
                        echo '<p><a href="' . site_url('area/list/') . '" class="button">リストに戻る</a></p>';
                    }
                } elseif (!empty($_GET['option']) && $_GET['option'] == 'add') {
                    // 追加用HTML
                    display_add_form();
                } else {
                    // デフォルトはリストページへリダイレクト
                    echo '<div class="notice notice-warning">';
                    echo '<p>URLパラメータが不足しています。</p>';
                    echo '<p>リストページに戻るには下のボタンをクリックしてください。</p>';
                    echo '</div>';
                    echo '<p><a href="' . site_url('area/list/') . '" class="button button-primary">リストページに戻る</a></p>';
                }
                ?>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();