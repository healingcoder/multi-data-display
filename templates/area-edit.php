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

            <!-- デバッグ情報を追加 (ここから) -->
            <div class="notice notice-info">
                <h3>デバッグ情報</h3>
                <pre>
<?php
echo "========== URL情報 ==========\n";
echo "現在のURL: " . esc_html($_SERVER['REQUEST_URI']) . "\n";
echo "REQUEST_METHOD: " . esc_html($_SERVER['REQUEST_METHOD']) . "\n\n";

echo "========== GET パラメータ ==========\n";
print_r($_GET);
echo "\n";

echo "========== クエリ変数 ==========\n";
echo "pagename: " . esc_html(get_query_var('pagename')) . "\n";
echo "surl: " . esc_html(get_query_var('surl')) . "\n";
echo "option: " . esc_html(get_query_var('option')) . "\n\n";

echo "========== WP_QUERY オブジェクト ==========\n";
global $wp_query;
$qv = $wp_query->query_vars;
echo "query_vars['surl']: " . (isset($qv['surl']) ? esc_html($qv['surl']) : 'not set') . "\n";
echo "query_vars['pagename']: " . (isset($qv['pagename']) ? esc_html($qv['pagename']) : 'not set') . "\n\n";

echo "========== リライトルール確認 ==========\n";
global $wp_rewrite;
$rules = $wp_rewrite->wp_rewrite_rules();
foreach ($rules as $pattern => $query) {
    if (strpos($pattern, 'area/edit') !== false) {
        echo "Pattern: " . esc_html($pattern) . " => " . esc_html($query) . "\n";
    }
}
?>
                </pre>
            </div>
            <!-- デバッグ情報を追加 (ここまで) -->

            <div class="entry-content">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'shops';
                
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
                    
                    echo '<div class="notice notice-info">';
                    echo '<p>取得したshop_key: ' . esc_html($shop_key) . '</p>';
                    echo '</div>';
                    
                    // データベースクエリの実行前情報
                    echo '<div class="notice notice-info">';
                    echo '<p>データベース検索を実行します: "SELECT * FROM ' . esc_html($table_name) . ' WHERE surl = \'' . esc_html($shop_key) . '\'"</p>';
                    echo '</div>';
                    
                    // プレースホルダーを正しく使用
                    $prepare_query = $wpdb->prepare("SELECT * FROM $table_name WHERE surl = %s", $shop_key);
                    $shop = $wpdb->get_row($prepare_query);
                    
                    echo '<div class="notice notice-info">';
                    echo '<p>実行されたクエリ: ' . esc_html($wpdb->last_query) . '</p>';
                    echo '<p>取得結果: ' . ($shop ? 'データあり' : 'データなし') . '</p>';
                    if ($wpdb->last_error) {
                        echo '<p>エラー: ' . esc_html($wpdb->last_error) . '</p>';
                    }
                    echo '</div>';
                    
                    if ($shop) {
                        // 変更用HTML
                        echo '<div class="notice notice-success">';
                        echo '<p>データが見つかりました。編集フォームを表示します。</p>';
                        echo '</div>';
                        display_edit_form($shop);
                    } else {
                        echo '<div class="notice notice-error">';
                        echo '<p>指定された店舗が見つかりませんでした。(surl: ' . esc_html($shop_key) . ')</p>';
                        
                        // テーブル構造を確認
                        $table_check = $wpdb->get_results("SHOW TABLES LIKE '$table_name'");
                        if (empty($table_check)) {
                            echo '<p>テーブルが存在しません: ' . esc_html($table_name) . '</p>';
                        } else {
                            echo '<p>テーブルは存在します。</p>';
                            // テーブルのカラム構造を確認
                            $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
                            echo '<p>テーブルのカラム: ';
                            foreach ($columns as $column) {
                                echo esc_html($column->Field) . ', ';
                            }
                            echo '</p>';
                            
                            // テーブル内のデータ件数を確認
                            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                            echo '<p>テーブル内のデータ件数: ' . intval($count) . '</p>';
                            
                            // 存在するsurlの値を表示（最大10件）
                            $surl_samples = $wpdb->get_col("SELECT surl FROM $table_name LIMIT 10");
                            if (!empty($surl_samples)) {
                                echo '<p>存在するsurlの例: ';
                                foreach ($surl_samples as $sample) {
                                    echo esc_html($sample) . ', ';
                                }
                                echo '</p>';
                            }
                        }
                        
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
                    echo '<p>現在のURL: ' . esc_html($_SERVER['REQUEST_URI']) . '</p>';
                    echo '<p>必要なパラメータ: ?surl=[値] または /area/edit/[値]</p>';
                    echo '<p>リストページに戻るには下のボタンをクリックしてください。</p>';
                    echo '</div>';
                    echo '<p><a href="' . site_url('area/list/') . '" class="button button-primary">リストページに戻る</a></p>';
                    
                    // 自動リダイレクトはコメントアウト（デバッグ中）
                    // echo '<script>window.location.href = "' . site_url('area/list/') . '";</script>';
                }
                ?>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();