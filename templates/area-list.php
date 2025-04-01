<?php
/**
 * テンプレート名: エリアリスト
 *
 * 管理者向けの店舗データリスト表示ページ
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) exit;

// 管理者権限の確認
if (!current_user_can('administrator')) {
    auth_redirect();
    exit;
}

// 削除リクエストの処理
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['surl']) && isset($_POST['_wpnonce'])) {
    // nonce チェック
    if (wp_verify_nonce($_POST['_wpnonce'], 'delete_shop_' . $_POST['surl'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shops';
        $surl = sanitize_text_field($_POST['surl']);
        
        // 削除処理
        $result = $wpdb->delete(
            $table_name,
            array('surl' => $surl),
            array('%s')
        );
        
        // 結果メッセージの設定
        if ($result === false) {
            $delete_message = '削除中にエラーが発生しました: ' . $wpdb->last_error;
            $delete_status = 'error';
        } else {
            $delete_message = '「' . sanitize_text_field($_POST['sname']) . '」を削除しました';
            $delete_status = 'success';
        }
    } else {
        $delete_message = 'セキュリティチェックに失敗しました';
        $delete_status = 'error';
    }
}

get_header(); 
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="mdd-container">
            <header class="entry-header">
                <h1 class="entry-title">店舗データ一覧</h1>
            </header>

            <div class="entry-content">
                <?php
                // 削除メッセージの表示
                if (isset($delete_message)) {
                    $notice_class = ($delete_status == 'success') ? 'updated' : 'error';
                    echo '<div class="notice notice-' . $notice_class . ' is-dismissible"><p>' . esc_html($delete_message) . '</p></div>';
                }
                ?>
                
                <div class="mdd-actions">
                    <a href="<?php echo site_url('area/edit/') . '?option=add'; ?>" class="button button-primary">新規追加</a>
                </div>
                
                <?php
                // リスト表示関数を呼び出し
                display_shop_list_page();
                ?>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();

// リスト専用の表示関数
function display_shop_list_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    
    // 検索・フィルタリング用のパラメータ
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $area_filter = isset($_GET['area_filter']) ? sanitize_text_field($_GET['area_filter']) : '';
    $type_filter = isset($_GET['type_filter']) !== false ? intval($_GET['type_filter']) : '';
    
    // ページネーション設定
    $per_page = 20; // 1ページあたりの表示数
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    // クエリの構築
    $query = "SELECT * FROM $table_name";
    $count_query = "SELECT COUNT(*) FROM $table_name";
    $where = array();
    $where_params = array();
    
    // 検索条件
    if (!empty($search)) {
        $search_term = '%' . $wpdb->esc_like($search) . '%';
        $where[] = "(sname LIKE %s OR area LIKE %s OR service LIKE %s)";
        $where_params[] = $search_term;
        $where_params[] = $search_term;
        $where_params[] = $search_term;
    }
    
    if (!empty($area_filter)) {
        $where[] = "area LIKE %s";
        $where_params[] = '%' . $wpdb->esc_like($area_filter) . '%';
    }
    
    if ($type_filter !== '') {
        $where[] = "type = %d";
        $where_params[] = $type_filter;
    }
    
    // WHERE句の追加
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
        $count_query .= " WHERE " . implode(' AND ', $where);
    }
    
    // 並び順
    $query .= " ORDER BY sname ASC";
    
    // データ取得用のクエリを準備
    $limit_query = $query . " LIMIT %d OFFSET %d";
    $limit_params = array_merge($where_params, array($per_page, $offset));
    
    // データの取得
    if (!empty($limit_params)) {
        $shops = $wpdb->get_results($wpdb->prepare($limit_query, $limit_params));
        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_params));
    } else {
        // パラメータがない場合は直接クエリ実行
        $shops = $wpdb->get_results($query . " LIMIT $per_page OFFSET $offset");
        $total_items = $wpdb->get_var($count_query);
    }
    
    // エリア一覧を取得（フィルター用）
    $areas = $wpdb->get_col("SELECT DISTINCT area FROM $table_name ORDER BY area");
    
    // フィルターフォームの表示
    ?>
    <div class="mdd-filter-form">
        <form method="get" action="<?php echo site_url('area/list/'); ?>">
            <div class="mdd-search-box">
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="店舗名、エリア、サービスで検索">
                
                <select name="area_filter">
                    <option value="">全エリア</option>
                    <?php foreach($areas as $area): ?>
                        <option value="<?php echo esc_attr($area); ?>" <?php selected($area_filter, $area); ?>><?php echo esc_html($area); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="type_filter">
                    <option value="">全タイプ</option>
                    <option value="0" <?php selected($type_filter, '0'); ?>>タイプ 0</option>
                    <option value="1" <?php selected($type_filter, '1'); ?>>タイプ 1</option>
                    <option value="2" <?php selected($type_filter, '2'); ?>>タイプ 2</option>
                </select>
                
                <input type="submit" class="button" value="フィルター">
                <?php if (!empty($search) || !empty($area_filter) || $type_filter !== ''): ?>
                    <a href="<?php echo site_url('area/list/'); ?>" class="button">リセット</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <?php
    // 検索結果のステータス表示
    if (!empty($search) || !empty($area_filter) || $type_filter !== '') {
        echo '<div class="mdd-search-status">';
        echo '検索結果: ' . $total_items . '件';
        echo '</div>';
    }
    
    // テーブルの表示
    if ($shops) {
        ?>
        <div class="mdd-shop-list">
            <p>全 <?php echo $total_items; ?> 件中 <?php echo ($offset + 1); ?>〜<?php echo min($offset + $per_page, $total_items); ?> 件を表示</p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>サイト名</th>
                        <th>エリア</th>
                        <th>サービス</th>
                        <th>タイプ</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shops as $shop): ?>
                    <tr>
                        <td><?php echo esc_html($shop->sname); ?></td>
                        <td><?php echo esc_html($shop->area); ?></td>
                        <td><?php echo esc_html($shop->service); ?></td>
                        <td><?php echo esc_html($shop->type); ?></td>
                        <td>
                            <a href="<?php echo site_url('area/edit/' . urlencode($shop->surl)); ?>" class="button button-secondary edit-shop">編集</a>
                            <form method="post" style="display:inline-block;margin-left:5px;" onsubmit="return confirm('「<?php echo esc_attr($shop->sname); ?>」を削除してもよろしいですか？この操作は元に戻せません。');">
                                <?php wp_nonce_field('delete_shop_' . $shop->surl); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="surl" value="<?php echo esc_attr($shop->surl); ?>">
                                <input type="hidden" name="sname" value="<?php echo esc_attr($shop->sname); ?>">
                                <input type="submit" class="button button-secondary delete-shop" value="削除">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php
            // ページネーションの表示
            $total_pages = ceil($total_items / $per_page);
            if ($total_pages > 1) {
                echo '<div class="mdd-pagination">';
                
                // 現在のGETパラメータを保持
                $pagination_args = $_GET;
                
                // 前のページへのリンク
                if ($current_page > 1) {
                    $pagination_args['paged'] = $current_page - 1;
                    echo '<a href="' . add_query_arg($pagination_args, site_url('area/list/')) . '" class="button">&laquo; 前へ</a>';
                }
                
                // ページ番号
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="page-numbers current">' . $i . '</span>';
                    } else {
                        $pagination_args['paged'] = $i;
                        echo '<a href="' . add_query_arg($pagination_args, site_url('area/list/')) . '" class="page-numbers">' . $i . '</a>';
                    }
                }
                
                // 次のページへのリンク
                if ($current_page < $total_pages) {
                    $pagination_args['paged'] = $current_page + 1;
                    echo '<a href="' . add_query_arg($pagination_args, site_url('area/list/')) . '" class="button">次へ &raquo;</a>';
                }
                
                echo '</div>';
            }
            ?>
        </div>
        <?php
    } else {
        echo '<p>データが見つかりませんでした。</p>';
    }
}
?>