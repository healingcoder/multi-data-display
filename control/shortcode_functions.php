<?php
// Multi Data Display ショートコード関連の機能

// 直接アクセスを防止
if (!defined('ABSPATH')) exit;

/**
 * ショートコードの登録
 */
function mdd_register_shortcodes() {
    // 店舗一覧を表示するショートコード
    add_shortcode('prefecture', 'mdd_prefecture_shortcode');
}
add_action('init', 'mdd_register_shortcodes');

/**
 * 'prefecture' ショートコード処理関数
 * 使用例: [prefecture area="大阪"]
 *
 * @param array $atts ショートコード属性
 * @return string 生成されたHTML
 */
function mdd_prefecture_shortcode($atts) {
    // デフォルト値を設定
    $atts = shortcode_atts(array(
        'area' => '',         // エリア（部分一致）
        'service' => '',      // サービス（部分一致）
        'type' => '',         // タイプ（完全一致）
        'premium' => '',      // プレミアム値（完全一致）
        'limit' => 10,        // 取得件数
        'orderby' => 'sname', // 並び順の項目
        'order' => 'ASC',     // 並び順（ASC/DESC）
    ), $atts, 'prefecture');

    // WordPressのグローバル変数を使用
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    
    // クエリの構築
    $query = "SELECT * FROM $table_name WHERE 1=1";
    $where_params = array();
    
    // 検索条件の追加
    if (!empty($atts['area'])) {
        $query .= " AND area LIKE %s";
        $where_params[] = '%' . $wpdb->esc_like($atts['area']) . '%';
    }
    
    if (!empty($atts['service'])) {
        $query .= " AND service LIKE %s";
        $where_params[] = '%' . $wpdb->esc_like($atts['service']) . '%';
    }
    
    if ($atts['type'] !== '') {
        $query .= " AND type = %d";
        $where_params[] = intval($atts['type']);
    }
    
    if ($atts['premium'] !== '') {
        $query .= " AND premium = %d";
        $where_params[] = intval($atts['premium']);
    }
    
    // 並び順の設定
    $allowed_orderby = array('sname', 'area', 'service', 'sogo', 'point', 'premium', 'type');
    $orderby = in_array($atts['orderby'], $allowed_orderby) ? $atts['orderby'] : 'sname';
    $order = strtoupper($atts['order']) === 'DESC' ? 'DESC' : 'ASC';
    
    $query .= " ORDER BY $orderby $order";
    
    // 取得件数の制限
    $limit = intval($atts['limit']) > 0 ? intval($atts['limit']) : 10;
    $query .= " LIMIT %d";
    $where_params[] = $limit;
    
    // クエリを実行
    $shops = $wpdb->get_results($wpdb->prepare($query, $where_params));
    
    // 結果がない場合
    if (empty($shops)) {
        return '<div class="mdd-shop-error">指定された条件に一致する店舗はありませんでした。</div>';
    }
    
    // 出力バッファを開始
    ob_start();
    
    // 店舗リストの表示
    echo '<div class="mdd-shop-list-container">';
    
    foreach ($shops as $shop) {
        // 店舗カードを表示
        echo '<div class="mdd-shop-card">';
        
        // 店舗名とリンク
        echo '<div class="mdd-shop-header">';
        
        // 無料タグ（premium が 0 の場合）
        if (isset($shop->premium) && $shop->premium == 0) {
            echo '<span class="mdd-shop-free-tag">無料</span> ';
        }
        
        if (!empty($shop->url)) {
            echo '<h3 class="mdd-shop-name"><a href="' . esc_url($shop->url) . '" target="_blank">' . esc_html($shop->sname) . '</a></h3>';
        } else {
            echo '<h3 class="mdd-shop-name">' . esc_html($shop->sname) . '</h3>';
        }
        echo '</div>';
        
        echo '<div class="mdd-shop-content">';
        
        // 店舗情報
        echo '<div class="mdd-shop-details">';
        
        // 評価（ポイントを星表示）
        if (!empty($shop->point) && $shop->point > 0) {
            echo '<div class="mdd-shop-rating">';
            $rating = min(5, $shop->point / 20); // 仮に100満点中のポイントを5段階評価に変換
            $full_stars = floor($rating);
            $half_star = ($rating - $full_stars) >= 0.5;
            
            // 星の表示
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $full_stars) {
                    echo '<span class="mdd-star mdd-star-full">★</span>';
                } elseif ($i == $full_stars + 1 && $half_star) {
                    echo '<span class="mdd-star mdd-star-half">☆</span>';
                } else {
                    echo '<span class="mdd-star mdd-star-empty">☆</span>';
                }
            }
            
            // 点数表示
            echo '<span class="mdd-rating-value">' . number_format($rating, 2) . '</span>';
            echo '</div>';
        }
        
        // 横線を追加
        echo '<hr class="mdd-divider">';
        
        // エリア
        if (!empty($shop->area)) {
            echo '<div class="mdd-shop-area"><span class="mdd-label">エリア:</span> <span class="mdd-value">' . esc_html($shop->area) . '</span></div>';
        }
        
        // サービス
        if (!empty($shop->service)) {
            echo '<div class="mdd-shop-service"><span class="mdd-label">サービス:</span> <span class="mdd-value">' . esc_html($shop->service) . '</span></div>';
        }
        
        // アクセス
        if (!empty($shop->copy)) {
            echo '<div class="mdd-shop-access"><span class="mdd-label">アクセス:</span> <span class="mdd-value">' . esc_html($shop->copy) . '</span></div>';
        }
        
        // 営業時間
        if (!empty($shop->time)) {
            echo '<div class="mdd-shop-time"><span class="mdd-label">営業時間:</span> <span class="mdd-value">' . esc_html($shop->time) . '</span></div>';
        }
        
        // 料金
        if (!empty($shop->price)) {
            echo '<div class="mdd-shop-price"><span class="mdd-label">料金:</span> <span class="mdd-value">' . nl2br(esc_html($shop->price)) . '</span></div>';
        }
        
        // 電話番号
        if (!empty($shop->tel)) {
            echo '<div class="mdd-shop-tel"><span class="mdd-label">電話:</span> <span class="mdd-value">' . esc_html($shop->tel) . '</span></div>';
        }
        
        echo '</div>'; // .mdd-shop-details
        
        // おすすめポイント（あれば表示）
        if (!empty($shop->recommended)) {
            echo '<div class="mdd-shop-recommended">';
            echo '<h4><span class="mdd-heart-icon">♥</span> おすすめポイント</h4>';
            echo '<p>' . nl2br(esc_html($shop->recommended)) . '</p>';
            echo '</div>';
        }
        
        echo '</div>'; // .mdd-shop-content
        echo '</div>'; // .mdd-shop-card
    }
    
    echo '</div>'; // .mdd-shop-list-container
    
    // 出力バッファの内容を取得して返す
    return ob_get_clean();
}

/**
 * ショートコード用のスタイルを追加
 */
function mdd_shortcode_styles() {
    // 既存のスタイルシートを読み込み、それに追加する形で実装
    wp_enqueue_style('mdd-shortcode-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/mdd-shortcode.css', array(), '1.0.0');
}


    

add_action('wp_enqueue_scripts', 'mdd_shortcode_styles');