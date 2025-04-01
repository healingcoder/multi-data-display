<?php
// 直接アクセスを防止
if (!defined('ABSPATH')) exit;

// CSVインポート関連の処理

/**
 * CSVインポート処理のフックを登録
 */
function mdd_register_csv_import() {
    add_action('admin_post_mdd_csv_import', 'mdd_process_csv_import');
    add_action('admin_post_mdd_download_sample_csv', 'mdd_download_sample_csv');
    
    // セッション開始（メッセージの受け渡しに使用）
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'mdd_register_csv_import');

/**
 * CSVインポート処理を実行
 */
function mdd_process_csv_import() {
    // 管理者権限チェック
    if (!current_user_can('administrator')) {
        wp_die('この操作を実行する権限がありません。');
    }
    
    // nonceチェック
    if (!isset($_POST['csv_import_nonce']) || !wp_verify_nonce($_POST['csv_import_nonce'], 'csv_import_action')) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // ファイルのアップロードチェック
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'ファイルのアップロードに失敗しました。';
        if (isset($_FILES['csv_file']['error'])) {
            $error_message .= ' エラーコード: ' . $_FILES['csv_file']['error'];
        }
        $_SESSION['csv_import_message'] = $error_message;
        $_SESSION['csv_import_status'] = 'error';
        wp_redirect(site_url('area/list/'));
        exit;
    }
    
    // CSVファイルであることを確認
    $file_info = pathinfo($_FILES['csv_file']['name']);
    if (!isset($file_info['extension']) || strtolower($file_info['extension']) !== 'csv') {
        $_SESSION['csv_import_message'] = 'CSVファイルのみアップロード可能です。';
        $_SESSION['csv_import_status'] = 'error';
        wp_redirect(site_url('area/list/'));
        exit;
    }
    
    // アップロードされたファイルのエンコーディングを検出
    $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
    
    // BOMを確認して削除（UTF-8 BOM対応）
    if (substr($csv_content, 0, 3) === "\xEF\xBB\xBF") {
        $csv_content = substr($csv_content, 3);
    }
    
    // エンコーディングを検出して変換
    if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
        $encoding = mb_detect_encoding($csv_content, ['ASCII', 'UTF-8', 'SJIS-win', 'EUC-JP', 'JIS'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $csv_content = mb_convert_encoding($csv_content, 'UTF-8', $encoding);
        }
    }
    
    // 変換後のコンテンツを一時ファイルに書き込み
    $temp_file = tempnam(sys_get_temp_dir(), 'csv_import_');
    file_put_contents($temp_file, $csv_content);
    
    // インポートモードを取得
    $import_mode = isset($_POST['import_mode']) ? sanitize_text_field($_POST['import_mode']) : 'append';
    
    // ヘッダー行の有無
    $has_header = isset($_POST['has_header']) && $_POST['has_header'] == '1';
    
    // CSVファイルを処理
    $result = process_csv_file($temp_file, $import_mode, $has_header);
    
    // 一時ファイルを削除
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    // 結果メッセージを設定
    $_SESSION['csv_import_message'] = $result['message'];
    $_SESSION['csv_import_status'] = $result['status'];
    
    // リストページにリダイレクト
    wp_redirect(site_url('area/list/'));
    exit;
}

/**
 * CSVファイルを処理してデータベースにインポート
 *
 * @param string $file_path CSVファイルのパス
 * @param string $mode インポートモード（append/update/replace）
 * @param bool $has_header 1行目をヘッダーとして扱うか
 * @return array 処理結果の配列
 */
function process_csv_file($file_path, $mode = 'append', $has_header = true) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    
    // フィールドの最大長を取得
    $field_max_lengths = get_table_field_lengths($table_name);
    
    // モードに応じて前処理
    if ($mode === 'replace') {
        // 全データを削除
        $wpdb->query("TRUNCATE TABLE $table_name");
    }
    
    // CSVファイルを開く
    $handle = fopen($file_path, 'r');
    if (!$handle) {
        return array(
            'status' => 'error',
            'message' => 'CSVファイルが開けませんでした。'
        );
    }
    
    // カラム名のマッピング
    $columns = array(
        'url', 'sname', 'price', 'surl', 'area', 'service', 'copy', 
        'comment', 'time', 'tel', 'sogo', 'point', 'premium', 'type', 'recommended'
    );
    
    // 必須カラムのインデックス
    $required_columns = array(
        'surl' => 3,   // 配列内での位置（0始まり）
        'sname' => 1   // 配列内での位置（0始まり）
    );
    
    // 統計情報
    $stats = array(
        'total' => 0,      // 処理した行数
        'inserted' => 0,   // 追加した行数
        'updated' => 0,    // 更新した行数
        'skipped' => 0,    // スキップした行数
        'errors' => 0      // エラー発生行数
    );
    
    // ヘッダー行をスキップする場合
    if ($has_header) {
        fgetcsv($handle, 0, ',');
    }
    
    // 行ごとに処理
    $line_number = $has_header ? 2 : 1; // 行番号（エラーメッセージ用）
    $error_details = array();
    
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        $stats['total']++;
        
        // カラム数チェック
        if (count($data) < count($required_columns)) {
            $error_details[] = "{$line_number}行目: カラム数が足りません。";
            $stats['errors']++;
            $line_number++;
            continue;
        }
        
        // 必須カラムのチェック
        $missing_required = false;
        foreach ($required_columns as $column => $index) {
            if (empty($data[$index])) {
                $error_details[] = "{$line_number}行目: {$column}は必須です。";
                $missing_required = true;
                break;
            }
        }
        
        if ($missing_required) {
            $stats['errors']++;
            $line_number++;
            continue;
        }
        
        // データを整形
        $record = array();
        $length_errors = array();
        
        foreach ($columns as $i => $column) {
            if (isset($data[$i])) {
                // データ値をトリミングして制御文字を削除
                $value = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $data[$i]));
                
                // フィールド長のチェックと切り詰め
                if (isset($field_max_lengths[$column]) && mb_strlen($value, 'UTF-8') > $field_max_lengths[$column]) {
                    // 長すぎる値は切り詰める
                    $value = mb_substr($value, 0, $field_max_lengths[$column], 'UTF-8');
                    $length_errors[] = $column;
                }
                
                $record[$column] = $value;
            } else {
                // データがない場合はデフォルト値を設定
                if (in_array($column, array('sogo', 'point', 'premium', 'type'))) {
                    $record[$column] = 0; // 数値型カラムのデフォルト
                } else {
                    $record[$column] = ''; // 文字列型カラムのデフォルト
                }
            }
        }
        
        // 数値型カラムを整形
        $record['sogo'] = isset($record['sogo']) && is_numeric($record['sogo']) ? intval($record['sogo']) : 0;
        $record['point'] = isset($record['point']) && is_numeric($record['point']) ? intval($record['point']) : 0;
        $record['premium'] = isset($record['premium']) && is_numeric($record['premium']) ? intval($record['premium']) : 0;
        $record['type'] = isset($record['type']) && is_numeric($record['type']) ? intval($record['type']) : 0;
        
        // 長さエラーの警告
        if (!empty($length_errors)) {
            $error_details[] = "{$line_number}行目: 以下のフィールドが長すぎるため切り詰められました: " . implode(', ', $length_errors);
        }
        
        // フォーマット文字列を生成
        $column_format = array();
        foreach ($columns as $column) {
            if (in_array($column, array('sogo', 'point', 'premium', 'type'))) {
                $column_format[] = '%d';
            } else {
                $column_format[] = '%s';
            }
        }
        
        // モードに応じてデータを挿入/更新
        $surl = $record['surl'];
        
        // 既存レコードの確認
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE surl = %s", $surl));
        
        if ($exists && ($mode === 'update' || $mode === 'append')) {
            if ($mode === 'update') {
                // 更新
                $result = $wpdb->update(
                    $table_name,
                    $record,
                    array('surl' => $surl),
                    $column_format,
                    array('%s')
                );
                
                if ($result !== false) {
                    $stats['updated']++;
                } else {
                    $error_details[] = "{$line_number}行目: 更新中にエラーが発生しました: " . $wpdb->last_error;
                    $stats['errors']++;
                }
            } else {
                // append モードで既存データの場合はスキップ
                $stats['skipped']++;
            }
        } else {
            // 追加
            $result = $wpdb->insert(
                $table_name,
                $record,
                $column_format
            );
            
            if ($result !== false) {
                $stats['inserted']++;
            } else {
                $error_details[] = "{$line_number}行目: 追加中にエラーが発生しました: " . $wpdb->last_error;
                $stats['errors']++;
            }
        }
        
        $line_number++;
    }
    
    fclose($handle);
    
    // 結果メッセージの生成
    $message = "CSVインポートが完了しました。処理した行数: {$stats['total']}, 追加: {$stats['inserted']}, 更新: {$stats['updated']}, スキップ: {$stats['skipped']}, エラー: {$stats['errors']}";
    
    // エラーがある場合は詳細を追加
    if ($stats['errors'] > 0 || !empty($error_details)) {
        $message .= "\n\nエラー詳細:\n" . implode("\n", $error_details);
    }
    
    return array(
        'status' => ($stats['errors'] > 0) ? 'error' : 'success',
        'message' => $message,
        'stats' => $stats
    );
}

/**
 * サンプルCSVをダウンロードする処理
 */
function mdd_download_sample_csv() {
    // 管理者権限チェック
    if (!current_user_can('administrator')) {
        wp_die('この操作を実行する権限がありません。');
    }
    
    // nonceチェック
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'download_sample_csv')) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // テーブルのフィールド長を取得して、適切なサンプルを作成する
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    $field_max_lengths = get_table_field_lengths($table_name);
    
    // サンプルデータの作成
    $sample_data = array(
        array('url', 'sname', 'price', 'surl', 'area', 'service', 'copy', 'comment', 'time', 'tel', 'sogo', 'point', 'premium', 'type', 'recommended'),
        array(
            'https://example.com/shop1', 
            mb_substr('サンプル店舗1', 0, $field_max_lengths['sname'] ?? 100, 'UTF-8'), 
            mb_substr('1,000円～5,000円', 0, $field_max_lengths['price'] ?? 100, 'UTF-8'), 
            'sample-shop1', 
            mb_substr('東京', 0, $field_max_lengths['area'] ?? 100, 'UTF-8'), 
            mb_substr('サービスA, サービスB', 0, $field_max_lengths['service'] ?? 100, 'UTF-8'), 
            mb_substr('東京駅から徒歩5分', 0, $field_max_lengths['copy'] ?? 100, 'UTF-8'), 
            mb_substr('おすすめの店舗です。特徴的なサービスが魅力。', 0, $field_max_lengths['comment'] ?? 500, 'UTF-8'), 
            mb_substr('10:00～18:00', 0, $field_max_lengths['time'] ?? 100, 'UTF-8'), 
            '03-1234-5678', 
            '1', 
            '80', 
            '0', 
            '0', 
            mb_substr('ポイント1：アクセスが良い\nポイント2：スタッフの対応が丁寧', 0, $field_max_lengths['recommended'] ?? 500, 'UTF-8')
        ),
        array(
            'https://example.com/shop2', 
            mb_substr('サンプル店舗2', 0, $field_max_lengths['sname'] ?? 100, 'UTF-8'), 
            mb_substr('2,000円～8,000円', 0, $field_max_lengths['price'] ?? 100, 'UTF-8'), 
            'sample-shop2', 
            mb_substr('大阪', 0, $field_max_lengths['area'] ?? 100, 'UTF-8'), 
            mb_substr('サービスC, サービスD', 0, $field_max_lengths['service'] ?? 100, 'UTF-8'), 
            mb_substr('大阪駅から徒歩10分', 0, $field_max_lengths['copy'] ?? 100, 'UTF-8'), 
            mb_substr('大阪エリアで人気の店舗。リピーターが多い。', 0, $field_max_lengths['comment'] ?? 500, 'UTF-8'), 
            mb_substr('9:00～20:00', 0, $field_max_lengths['time'] ?? 100, 'UTF-8'), 
            '06-1234-5678', 
            '0', 
            '75', 
            '1', 
            '1', 
            mb_substr('ポイント1：価格が手頃\nポイント2：施設が清潔', 0, $field_max_lengths['recommended'] ?? 500, 'UTF-8')
        )
    );
    
    // CSVデータをメモリ上に作成
    $output = fopen('php://temp', 'r+');
    foreach ($sample_data as $row) {
        fputcsv($output, $row);
    }
    rewind($output);
    $csv_data = stream_get_contents($output);
    fclose($output);
    
    // UTF-8にBOMを追加（Excelでの文字化け対策）
    $csv_data = "\xEF\xBB\xBF" . $csv_data;
    
    // ヘッダーを送信してCSVをダウンロード
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="shop_sample.csv"');
    header('Content-Length: ' . strlen($csv_data));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $csv_data;
    exit;
}

/**
 * データベースから既存データをCSVとしてエクスポートする処理
 */
function mdd_export_data_csv() {
    // 管理者権限チェック
    if (!current_user_can('administrator')) {
        wp_die('この操作を実行する権限がありません。');
    }
    
    // nonceチェック
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'export_data_csv')) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'shops';
    
    // データを取得
    $query = "SELECT url, sname, price, surl, area, service, copy, comment, time, tel, sogo, point, premium, type, recommended FROM $table_name ORDER BY sname";
    $results = $wpdb->get_results($query, ARRAY_A);
    
    if (empty($results)) {
        wp_die('エクスポートするデータがありません。');
    }
    
    // CSVヘッダー
    $headers = array('url', 'sname', 'price', 'surl', 'area', 'service', 'copy', 'comment', 'time', 'tel', 'sogo', 'point', 'premium', 'type', 'recommended');
    
    // CSVデータをメモリ上に作成
    $output = fopen('php://temp', 'r+');
    
    // ヘッダー行を出力
    fputcsv($output, $headers);
    
    // データ行を出力
    foreach ($results as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv_data = stream_get_contents($output);
    fclose($output);
    
    // UTF-8にBOMを追加（Excelでの文字化け対策）
    $csv_data = "\xEF\xBB\xBF" . $csv_data;
    
    // ファイル名（日付を含める）
    $date = date('Ymd_His');
    $filename = "shop_export_{$date}.csv";
    
    // ヘッダーを送信してCSVをダウンロード
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($csv_data));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $csv_data;
    exit;
}
// エクスポート処理のフックを追加
add_action('admin_post_mdd_export_data_csv', 'mdd_export_data_csv');

/**
 * テーブルのフィールド長を取得する
 * 
 * @param string $table_name テーブル名
 * @return array フィールド名と最大長の連想配列
 */
function get_table_field_lengths($table_name) {
    global $wpdb;
    
    // テーブル情報の取得
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name", ARRAY_A);
    
    if (empty($columns)) {
        return array();
    }
    
    $field_lengths = array();
    
    foreach ($columns as $column) {
        // フィールドタイプから長さを抽出
        if (preg_match('/^varchar\((\d+)\)$/i', $column['Type'], $matches)) {
            $field_lengths[$column['Field']] = (int)$matches[1];
        } else if (preg_match('/^text$/i', $column['Type'])) {
            $field_lengths[$column['Field']] = 65535; // TEXT型の最大長
        } else if (preg_match('/^longtext$/i', $column['Type'])) {
            $field_lengths[$column['Field']] = 4294967295; // LONGTEXT型の最大長
        }
    }
    
    return $field_lengths;
}