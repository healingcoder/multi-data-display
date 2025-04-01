<?php
// 直接アクセスを防止
if (!defined('ABSPATH')) exit;

/**
 * 画像アップロード関連の機能
 */

/**
 * 画像アップロード処理のフックを登録
 */
function mdd_register_image_upload() {
    add_action('admin_post_mdd_process_image_upload', 'mdd_process_image_upload');
}
add_action('init', 'mdd_register_image_upload');

/**
 * 画像アップロード用ディレクトリを作成する
 */
function mdd_create_upload_directory() {
    // プラグインのアップロードディレクトリを作成
    $upload_dir = wp_upload_dir();
    $mdd_upload_path = $upload_dir['basedir'] . '/mdd-images/shops';
    
    // ディレクトリが存在しない場合は作成
    if (!file_exists($mdd_upload_path)) {
        wp_mkdir_p($mdd_upload_path);
        
        // index.phpファイルを作成してディレクトリリスティングを防止
        $index_file = $mdd_upload_path . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden.');
        }
        
        // .htaccessファイルを作成して直接アクセスを制御
        $htaccess_file = $mdd_upload_path . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# BEGIN Multi Data Display\n";
            $htaccess_content .= "<IfModule mod_rewrite.c>\n";
            $htaccess_content .= "RewriteEngine On\n";
            $htaccess_content .= "RewriteCond %{HTTP_REFERER} !^" . home_url() . " [NC]\n";
            $htaccess_content .= "RewriteRule \\.(jpg|jpeg|png|gif)$ - [NC,F,L]\n";
            $htaccess_content .= "</IfModule>\n";
            $htaccess_content .= "# END Multi Data Display\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
    
    return $mdd_upload_path;
}

/**
 * 画像アップロード用のフォームを表示
 * 
 * @param string $surl 店舗のSURL
 * @return string アップロードフォームのHTML
 */
function mdd_image_upload_form($surl) {
    // nonceフィールドを作成
    $nonce = wp_create_nonce('mdd_image_upload_' . $surl);
    
    // 現在の画像パスを取得
    $image_url = mdd_get_shop_image_url($surl);
    $current_image_html = '';
    
    if ($image_url) {
        $current_image_html = '
            <div class="mdd-current-image">
                <h4>現在の画像</h4>
                <img src="' . esc_url($image_url) . '?v=' . time() . '" alt="店舗画像" style="max-width: 300px; height: auto;">
                <p>
                    <a href="' . admin_url('admin-post.php?action=mdd_process_image_upload&surl=' . urlencode($surl) . '&delete=1&_wpnonce=' . $nonce) . '" 
                       class="button button-secondary" 
                       onclick="return confirm(\'本当に画像を削除しますか？\');">
                        画像を削除
                    </a>
                </p>
            </div>';
    }
    
    // フォームHTML生成
    $form = '
    <div class="mdd-image-upload-form">
        <h3>店舗画像のアップロード</h3>
        
        ' . $current_image_html . '
        
        <form method="post" enctype="multipart/form-data" action="' . admin_url('admin-post.php') . '">
            <input type="hidden" name="action" value="mdd_process_image_upload">
            <input type="hidden" name="surl" value="' . esc_attr($surl) . '">
            <input type="hidden" name="_wpnonce" value="' . $nonce . '">
            
            <div class="mdd-form-row">
                <label for="shop_image">画像ファイル:</label>
                <input type="file" name="shop_image" id="shop_image" accept="image/jpeg,image/png,image/gif" required>
                <p class="description">
                    推奨サイズ: 1280x720ピクセル (16:9)。<br>
                    許可されるファイル形式: JPEG, PNG, GIF<br>
                    最大ファイルサイズ: ' . size_format(wp_max_upload_size()) . '
                </p>
            </div>
            
            <div class="mdd-form-row">
                <label>
                    <input type="checkbox" name="resize_image" value="1" checked>
                    16:9のアスペクト比に自動調整する
                </label>
            </div>
            
            <div class="mdd-form-row">
                <input type="submit" class="button button-primary" value="アップロード">
            </div>
        </form>
    </div>';
    
    return $form;
}

/**
 * 店舗画像のURLを取得
 * 
 * @param string $surl 店舗のSURL
 * @return string|false 画像URLまたはfalse
 */
function mdd_get_shop_image_url($surl) {
    $upload_dir = wp_upload_dir();
    $image_path = $upload_dir['basedir'] . '/mdd-images/shops/' . sanitize_file_name($surl) . '.jpg';
    
    if (file_exists($image_path)) {
        return $upload_dir['baseurl'] . '/mdd-images/shops/' . sanitize_file_name($surl) . '.jpg';
    }
    
    return false;
}

/**
 * 画像アップロード処理を実行
 */
function mdd_process_image_upload() {
    // 管理者権限チェック
    if (!current_user_can('administrator')) {
        wp_die('この操作を実行する権限がありません。');
    }
    
    // nonceチェック
    $surl = isset($_REQUEST['surl']) ? sanitize_text_field($_REQUEST['surl']) : '';
    if (empty($surl) || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'mdd_image_upload_' . $surl)) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // 画像削除処理
    if (isset($_GET['delete']) && $_GET['delete'] == 1) {
        $upload_dir = wp_upload_dir();
        $image_path = $upload_dir['basedir'] . '/mdd-images/shops/' . sanitize_file_name($surl) . '.jpg';
        
        if (file_exists($image_path)) {
            unlink($image_path);
            $redirect_url = add_query_arg(
                array('message' => 'image_deleted'),
                site_url('area/edit/' . urlencode($surl))
            );
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    // ファイルのアップロードチェック
    if (!isset($_FILES['shop_image']) || $_FILES['shop_image']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'ファイルのアップロードに失敗しました。';
        if (isset($_FILES['shop_image']['error'])) {
            $error_code = $_FILES['shop_image']['error'];
            switch ($error_code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message = 'ファイルサイズが大きすぎます。';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = 'ファイルの一部のみがアップロードされました。';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message = 'ファイルがアップロードされませんでした。';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = '一時フォルダがありません。';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message = 'ディスクへの書き込みに失敗しました。';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message = 'PHPの拡張モジュールによってアップロードが停止されました。';
                    break;
            }
        }
        
        $redirect_url = add_query_arg(
            array('message' => 'upload_error', 'error' => urlencode($error_message)),
            site_url('area/edit/' . urlencode($surl))
        );
        wp_redirect($redirect_url);
        exit;
    }
    
    // ファイルタイプのチェック
    $file_type = wp_check_filetype($_FILES['shop_image']['name'], array(
        'jpg|jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ));
    
    if (!$file_type['type']) {
        $redirect_url = add_query_arg(
            array('message' => 'invalid_file_type'),
            site_url('area/edit/' . urlencode($surl))
        );
        wp_redirect($redirect_url);
        exit;
    }
    
    // アップロードディレクトリの作成
    $upload_dir = mdd_create_upload_directory();
    
    // 一時ファイルパス
    $tmp_file = $_FILES['shop_image']['tmp_name'];
    
    // 画像のリサイズ処理
    $resize_image = isset($_POST['resize_image']) && $_POST['resize_image'] == '1';
    if ($resize_image) {
        $resized_file = mdd_resize_image_to_16_9($tmp_file);
        if ($resized_file) {
            $tmp_file = $resized_file;
        }
    }
    
    // 目的のファイルパス（常にJPG形式に変換）
    $dest_file = $upload_dir . '/' . sanitize_file_name($surl) . '.jpg';
    
    // ファイルの移動と保存
    if (copy($tmp_file, $dest_file)) {
        // 一時リサイズファイルを削除
        if ($resize_image && $resized_file && file_exists($resized_file)) {
            unlink($resized_file);
        }
        
        // パーミッションの設定
        chmod($dest_file, 0644);
        
        // リダイレクト（成功）
        $redirect_url = add_query_arg(
            array('message' => 'upload_success'),
            site_url('area/edit/' . urlencode($surl))
        );
        wp_redirect($redirect_url);
        exit;
    } else {
        // リダイレクト（失敗）
        $redirect_url = add_query_arg(
            array('message' => 'save_error'),
            site_url('area/edit/' . urlencode($surl))
        );
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * 画像を16:9のアスペクト比にリサイズする
 * 
 * @param string $image_path 画像ファイルパス
 * @return string|false リサイズされた一時ファイルのパスまたはfalse
 */
function mdd_resize_image_to_16_9($image_path) {
    // GDライブラリが利用可能か確認
    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatefrompng') || !function_exists('imagecreatefromgif')) {
        return false;
    }
    
    // 元画像の情報を取得
    $image_info = getimagesize($image_path);
    if (!$image_info) {
        return false;
    }
    
    // 画像の種類に応じてリソースを作成
    $source_image = null;
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($image_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($image_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($image_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // 元画像のサイズ
    $source_width = $image_info[0];
    $source_height = $image_info[1];
    
    // 出力サイズの計算（16:9のアスペクト比）
    $target_width = 1280; // 目標幅
    $target_height = 720; // 目標高さ（16:9比）
    
    // ソース画像からのクロップ領域を計算
    $source_aspect_ratio = $source_width / $source_height;
    $target_aspect_ratio = $target_width / $target_height;
    
    if ($source_aspect_ratio > $target_aspect_ratio) {
        // ソース画像が目標よりも横長の場合
        $crop_width = floor($source_height * $target_aspect_ratio);
        $crop_height = $source_height;
        $crop_x = floor(($source_width - $crop_width) / 2);
        $crop_y = 0;
    } else {
        // ソース画像が目標よりも縦長の場合
        $crop_width = $source_width;
        $crop_height = floor($source_width / $target_aspect_ratio);
        $crop_x = 0;
        $crop_y = floor(($source_height - $crop_height) / 2);
    }
    
    // 新しい画像を作成
    $target_image = imagecreatetruecolor($target_width, $target_height);
    
    // PNGの透明度を保持
    if ($image_info[2] == IMAGETYPE_PNG) {
        imagealphablending($target_image, false);
        imagesavealpha($target_image, true);
        $transparent = imagecolorallocatealpha($target_image, 255, 255, 255, 127);
        imagefilledrectangle($target_image, 0, 0, $target_width, $target_height, $transparent);
    }
    
    // リサイズ＆クロップ
    imagecopyresampled($target_image, $source_image, 0, 0, $crop_x, $crop_y, $target_width, $target_height, $crop_width, $crop_height);
    
    // 一時ファイルを作成
    $temp_file = tempnam(sys_get_temp_dir(), 'mdd_img_');
    
    // 画像を保存
    $success = false;
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($target_image, $temp_file, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($target_image, $temp_file, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($target_image, $temp_file);
            break;
    }
    
    // メモリ解放
    imagedestroy($source_image);
    imagedestroy($target_image);
    
    if ($success) {
        return $temp_file;
    }
    
    return false;
}

/**
 * 画像アップロードメッセージを表示
 * 
 * @param string $message_key メッセージキー
 * @param string $error_detail エラー詳細（オプション）
 * @return string メッセージHTML
 */
function mdd_display_image_upload_message($message_key, $error_detail = '') {
    $message = '';
    $message_class = 'notice-info';
    
    switch ($message_key) {
        case 'upload_success':
            $message = '画像のアップロードに成功しました。';
            $message_class = 'notice-success';
            break;
        case 'upload_error':
            $message = '画像のアップロードに失敗しました。';
            if (!empty($error_detail)) {
                $message .= ' ' . esc_html($error_detail);
            }
            $message_class = 'notice-error';
            break;
        case 'invalid_file_type':
            $message = '無効なファイル形式です。JPEG、PNG、GIF形式のみ許可されています。';
            $message_class = 'notice-error';
            break;
        case 'save_error':
            $message = '画像の保存中にエラーが発生しました。アップロードディレクトリの権限を確認してください。';
            $message_class = 'notice-error';
            break;
        case 'image_deleted':
            $message = '画像が削除されました。';
            $message_class = 'notice-success';
            break;
    }
    
    if (empty($message)) {
        return '';
    }
    
    return '<div class="notice ' . $message_class . ' is-dismissible"><p>' . $message . '</p></div>';
}