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
        'orderby' => 'score', // デフォルトでスコア順（相互フォロー+ポイント+プレミアム）
        'order' => 'DESC',    // デフォルトで降順（高い順）
        'show_image' => 'yes', // 画像を表示するか
    ), $atts, 'prefecture');

    // WordPressのグローバル変数を使用
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    
    // クエリの構築
    $query = "SELECT *, (sogo + point + premium) AS score FROM $table_name WHERE 1=1";
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
    $allowed_orderby = array('sname', 'area', 'service', 'sogo', 'point', 'premium', 'type', 'score');
    $orderby = in_array($atts['orderby'], $allowed_orderby) ? $atts['orderby'] : 'score'; // デフォルトでスコア順
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
        
        // タグエリアの表示（上部の赤やオレンジのタグ）
        echo '<div class="mdd-tag-area">';
        // 新規タグ - 追加から30日以内の場合に表示
        $created_date = isset($shop->created_at) ? strtotime($shop->created_at) : 0;
        $days_since_creation = $created_date > 0 ? (time() - $created_date) / (60 * 60 * 24) : 999;
        if ($days_since_creation <= 30) {
            echo '<span class="mdd-tag mdd-tag-new">新規</span>';
        }
        
        // カテゴリタグ表示 - エステ/リラク/整体・カイロ等
        $services = explode(',', $shop->service);
        $service_tags = array(
            'エステ' => 'エステ',
            'リラク' => 'リラク',
            '整体' => '整体・カイロ',
            'カイロ' => '整体・カイロ',
            'メンズ' => 'メンズOK'
        );
        
        foreach ($service_tags as $keyword => $tag) {
            if (stripos($shop->service, $keyword) !== false) {
                echo '<span class="mdd-tag mdd-tag-service">' . esc_html($tag) . '</span>';
            }
        }
        
        echo '</div>'; // .mdd-tag-area
        
        echo '<div class="mdd-shop-header">';
        // 公式サイトへのリンクボタン
        echo '<div class="mdd-smartphone-url">';
        echo '<a href="' . esc_url($shop->url) . '" class="mdd-smartphone-button" target="_blank">公式サイトへ <span class="mdd-arrow">→</span></a>';
        
        // 管理者用編集ボタン（管理者がログインしている場合のみ表示）
        if (current_user_can('administrator')) {
            echo '<a href="' . site_url('area/edit/' . urlencode($shop->surl)) . '" class="mdd-edit-button">編集</a>';
        }
        
        echo '</div>';
        
        echo '</div>'; // .mdd-shop-header
        
        echo '<div class="mdd-shop-content">';
        
        // 店舗名とロゴ
        echo '<div class="mdd-shop-info">';
        echo '<h3 class="mdd-shop-name"><a href="' . esc_url($shop->url) . '" target="_blank">' . esc_html($shop->sname) . '</a></h3>';
        // サブタイトル（コピー文を使用）
        if (!empty($shop->copy)) {
            echo '<div class="mdd-shop-subtitle">' . esc_html($shop->copy) . '</div>';
        }
        echo '</div>'; // .mdd-shop-info
        
        // 上部の店舗情報
        echo '<div class="mdd-shop-details-top">';
        
        // 画像表示（設定がyesの場合）- クリックで公式サイトへ
        if ($atts['show_image'] === 'yes' && function_exists('mdd_get_shop_image_url')) {
            $image_url = mdd_get_shop_image_url($shop->surl);
            if ($image_url) {
                echo '<div class="mdd-shop-image-container">';
                echo '<a href="' . esc_url($shop->url) . '" target="_blank">';
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($shop->sname) . '" class="mdd-shop-featured-image" />';
                echo '</a>';
                echo '</div>';
            }
        }
        
        // 店舗基本情報
        echo '<div class="mdd-shop-basic-info">';
        
        // 住所情報
        if (!empty($shop->area)) {
            echo '<div class="mdd-shop-address">';
            echo '<span class="mdd-icon">📍</span> ';
            echo '<span class="mdd-value">' . esc_html('阪急京都本線 河原町駅 ' . $shop->area) . '</span>';
            echo '</div>';
        }
        
        // 営業時間
        if (!empty($shop->time)) {
            echo '<div class="mdd-shop-time">';
            echo '<span class="mdd-icon">🕒</span> ';
            echo '<span class="mdd-value">' . esc_html($shop->time) . '</span>';
            echo '</div>';
        }
        
        echo '</div>'; // .mdd-shop-basic-info
        echo '</div>'; // .mdd-shop-details-top
        
        // 店舗メイン情報
        echo '<div class="mdd-shop-main-info">';
        
        // 左側：店舗説明
        echo '<div class="mdd-shop-description">';
        
        // キャッチコピーをコメントとして表示
        if (!empty($shop->comment)) {
            echo '<div class="mdd-shop-catch">';
            echo nl2br(esc_html($shop->comment));
            echo '</div>';
        }
        
        // 口コミ表示
        if (!empty($shop->point) || !empty($shop->sogo)) {
            echo '<div class="mdd-shop-review">';
            
            // 口コミラベル
            echo '<span class="mdd-review-label">口コミ</span>';
            
            // 評価点数
            if (!empty($shop->point)) {
                $rating = min(5, $shop->point / 20); // 100点満点を5点満点に変換
                echo '<span class="mdd-rating-value">' . number_format($rating, 2) . '</span>';
            }
            
            // 口コミ件数（sogoを件数として利用）
            if (!empty($shop->sogo)) {
                echo '<span class="mdd-review-count">(' . $shop->sogo . '件)</span>';
            }
            
            // 星評価の表示
            if (!empty($shop->point)) {
                echo '<div class="mdd-star-rating">';
                $star_value = $shop->point / 20; // 100点満点を5点満点に変換
                $full_stars = floor($star_value);
                $half_star = ($star_value - $full_stars) >= 0.5;
                
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $full_stars) {
                        echo '<span class="mdd-star mdd-star-full">★</span>';
                    } elseif ($i == $full_stars + 1 && $half_star) {
                        echo '<span class="mdd-star mdd-star-half">★</span>';
                    } else {
                        echo '<span class="mdd-star mdd-star-empty">☆</span>';
                    }
                }
                echo '</div>'; // .mdd-star-rating
            }
            
            echo '</div>'; // .mdd-shop-review
        }
        
        // オススメポイント
        if (!empty($shop->recommended)) {
            echo '<div class="mdd-popular-menu">';
            echo '<h4 class="mdd-menu-title">オススメポイント</h4>';
            $menu_items = explode("\n", $shop->recommended);
            foreach ($menu_items as $menu_item) {
                if (!empty(trim($menu_item))) {
                    echo '<div class="mdd-menu-item">' . esc_html($menu_item) . '</div>';
                }
            }
            echo '</div>'; // .mdd-popular-menu
        }
        
        echo '</div>'; // .mdd-shop-description
        
        // 右側：価格情報
        echo '<div class="mdd-shop-price-info">';
        
        // 料金表示
        if (!empty($shop->price)) {
            $price_parts = explode("\n", $shop->price);
            if (count($price_parts) >= 2) {
                // 通常価格
                $regular_price = trim($price_parts[0]);
                // 割引価格
                $discount_price = trim($price_parts[1]);
                
                echo '<div class="mdd-price-box">';
                
                // 割引率の計算（フォーマット：通常価格|割引率|割引価格）
                $price_info = explode('|', $discount_price);
                if (count($price_info) >= 3) {
                    echo '<div class="mdd-discount-rate">' . $price_info[1] . '</div>';
                    echo '<div class="mdd-discount-price">' . number_format(intval($price_info[2])) . '円</div>';
                    echo '<div class="mdd-regular-price">' . $price_info[0] . '</div>';
                } else {
                    // 通常表示
                    echo '<div class="mdd-discount-price">' . $discount_price . '</div>';
                    echo '<div class="mdd-regular-price">' . $regular_price . '</div>';
                }
                
                echo '</div>'; // .mdd-price-box
            } else {
                // 1行のみの場合はそのまま表示
                echo '<div class="mdd-price-box">';
                echo '<div class="mdd-price">' . esc_html($shop->price) . '</div>';
                echo '</div>';
            }
        }
        
        echo '</div>'; // .mdd-shop-price-info
        
        echo '</div>'; // .mdd-shop-main-info
        
        // ブログリンクがあれば表示
        if (!empty($shop->tel) && strpos($shop->tel, 'http') === 0) {
            echo '<div class="mdd-blog-link">';
            echo '<a href="' . esc_url($shop->tel) . '" target="_blank" class="mdd-blog-button">';
            echo '<span class="mdd-blog-icon">📝</span> ブログ';
            echo '<span class="mdd-blog-count">' . intval($shop->type) . '件</span>';
            echo '<span class="mdd-blog-new">NEW</span>';
            echo '</a>';
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
    wp_enqueue_style('mdd-shortcode-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/mdd-shortcode.css', array(), '1.2.1');
}
add_action('wp_enqueue_scripts', 'mdd_shortcode_styles');