<?php
// 直接アクセス禁止
if (!defined('ABSPATH')) exit;

// データベース操作関数

// レコードの更新（Update)
function save_record($wpdb, $table_name, $key_name, $key_format, $colInfo, $colFormat) {
    // レコードをセットするための配列
    $set_arr = array();
    // 配列にループ代入
    foreach ($colInfo as $col) {
        $set_arr[$col] = isset($_POST[$col]) ? sanitize_text_field($_POST[$col]) : null;
    }
    
    // テキストエリアの内容はsanitize_textareaを使用
    $set_arr['price'] = isset($_POST['price']) ? sanitize_textarea_field($_POST['price']) : null;
    $set_arr['comment'] = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : null;
    $set_arr['recommended'] = isset($_POST['recommended']) ? sanitize_textarea_field($_POST['recommended']) : null;
    
    // キーの値を取得
    $key_data = isset($_POST[$key_name]) ? sanitize_text_field($_POST[$key_name]) : null;
    
    // レコードの更新
    $result = $wpdb->update(
        $table_name, 
        $set_arr, 
        array($key_name => $key_data),
        $colFormat,
        $key_format
    );
    
    if ($result === false) {
        return "更新中にエラーが発生しました: " . $wpdb->last_error;
    } else {
        return "更新しました";
    }
}

// 削除処理
function delete_record($wpdb, $table_name, $key_name) {
    $key_data = isset($_POST[$key_name]) ? sanitize_text_field($_POST[$key_name]) : null;
    $result = $wpdb->delete(
        $table_name, 
        array($key_name => $key_data), 
        array('%s')
    );
    
    if ($result === false) {
        return "削除中にエラーが発生しました: " . $wpdb->last_error;
    } else {
        return "削除しました";
    }
}

// 追加処理
function add_record($wpdb, $table_name, $colInfo, $colFormat) {
    // レコードをセットするための配列
    $set_arr = array();
    // 配列にループ代入
    foreach ($colInfo as $col) {
        $set_arr[$col] = isset($_POST[$col]) ? sanitize_text_field($_POST[$col]) : null;
    }
    
    // テキストエリアの内容はsanitize_textareaを使用
    $set_arr['price'] = isset($_POST['price']) ? sanitize_textarea_field($_POST['price']) : null;
    $set_arr['comment'] = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : null;
    $set_arr['recommended'] = isset($_POST['recommended']) ? sanitize_textarea_field($_POST['recommended']) : null;
    
    // レコードの追加
    $result = $wpdb->insert($table_name, $set_arr, $colFormat);
    
    if ($result === false) {
        return "追加中にエラーが発生しました: " . $wpdb->last_error;
    } else {
        return "追加しました";
    }
}

// 変更フォーム表示
function display_edit_form($data) {
    $html_url     = $data->url;
    $html_name    = $data->sname;
    $html_price   = $data->price;
    $html_surl    = $data->surl;
    $html_area    = $data->area;
    $html_service = $data->service;
    $html_copy    = $data->copy;
    $html_comment = $data->comment;
    $html_time    = $data->time;
    $html_tel     = $data->tel;
    $html_sogo    = $data->sogo;
    $html_point   = $data->point;
    $html_premium = $data->premium;
    $html_type    = $data->type;
    $html_recommended = $data->recommended;
    ?>
    <form action='' method='post' novalidate="novalidate">
        <h2 class='is-style-vk-heading-solid_black'>
          <?php echo esc_html($html_name); ?>
          (<?php echo esc_html($html_surl); ?>)
        </h2>

        <div class="mdd-form-container">
            <input type='hidden' id='surl' name='surl' value='<?php echo esc_attr($html_surl); ?>'>
            
            <table class="form-table">
                <tr>
                    <th>アドレス</th>
                    <td><input type='text' id="url" name='url' class="regular-text" value='<?php echo esc_attr($html_url); ?>'></td>
                </tr>
                
                <tr>
                    <th>サイト名</th>
                    <td><input type='text' id="sname" name='sname' class="regular-text" value='<?php echo esc_attr($html_name); ?>'></td>
                </tr>
                
                <tr>
                    <th>エリア</th>
                    <td><input type='text' id="area" name='area' class="regular-text" value='<?php echo esc_attr($html_area); ?>'></td>
                </tr>
                
                <tr>
                    <th>サービス</th>
                    <td><input type='text' id="service" name='service' class="regular-text" value='<?php echo esc_attr($html_service); ?>'></td>
                </tr>
                
                <tr>
                    <th>営業時間</th>
                    <td><input type='text' id="time" name='time' class="regular-text" value='<?php echo esc_attr($html_time); ?>'></td>
                </tr>
                
                <tr>
                    <th>料金</th>
                    <td><textarea name='price' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_price); ?></textarea></td>
                </tr>
                
                <tr>
                    <th>電話番号</th>
                    <td><input type='text' id="tel" name='tel' class="regular-text" value='<?php echo esc_attr($html_tel); ?>'></td>
                </tr>
                
                <tr>
                    <th>相互フォロー</th>
                    <td><input type='number' id="sogo" name='sogo' class="small-text" value='<?php echo esc_attr($html_sogo); ?>'></td>
                </tr>
                
                <tr>
                    <th>ポイント</th>
                    <td><input type='number' id="point" name='point' class="small-text" value='<?php echo esc_attr($html_point); ?>'></td>
                </tr>
                
                <tr>
                    <th>プレミアム</th>
                    <td><input type='number' id="premium" name='premium' class="small-text" value='<?php echo esc_attr($html_premium); ?>'></td>
                </tr>
                
                <tr>
                    <th>タイプ(0,1,2)</th>
                    <td>
                        <select name="type" id="type">
                            <option value="0" <?php selected($html_type, 0); ?>>0</option>
                            <option value="1" <?php selected($html_type, 1); ?>>1</option>
                            <option value="2" <?php selected($html_type, 2); ?>>2</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th>コピー</th>
                    <td><input type='text' id="copy" name='copy' class="regular-text" value='<?php echo esc_attr($html_copy); ?>'></td>
                </tr>
                
                <tr>
                    <th>コメント</th>
                    <td><textarea name='comment' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_comment); ?></textarea></td>
                </tr>
                
                <tr>
                    <th>おすすめポイント</th>
                    <td><textarea name='recommended' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_recommended); ?></textarea></td>
                </tr>
            </table>
            
            <div class="mdd-form-actions">
                <input type="submit" name="chan" class="button button-primary" value="変更" />
                <input type="submit" name="del" class="button button-secondary" value="削除" onclick="return confirm('本当に削除しますか？');" />
                <a href="<?php echo site_url('area/list/'); ?>" class="button">リストに戻る</a>
            </div>
        </div>
    </form>
    <?php
}

// 追加フォーム表示
function display_add_form() {
    $html_url     = "";
    $html_name    = "";
    $html_price   = "";
    $html_surl    = "";
    $html_area    = "";
    $html_service = "";
    $html_copy    = "";
    $html_comment = "";
    $html_time    = "";
    $html_tel     = "";
    $html_sogo    = "0";
    $html_point   = "0";
    $html_premium = "0";
    $html_type    = "0";
    $html_recommended = "";
    ?>
    <form action='' method='post' novalidate="novalidate">
        <h2 class='is-style-vk-heading-solid_black'>新規登録画面</h2>

        <div class="mdd-form-container">        
            <table class="form-table">
                <tr>
                    <th>Surl(アドレスKey)</th>
                    <td><input type='text' id='surl' name='surl' class="regular-text" value='<?php echo esc_attr($html_surl); ?>' required></td>
                </tr>
                
                <tr>
                    <th>アドレス</th>
                    <td><input type='url' id="url" name='url' class="regular-text" value='<?php echo esc_attr($html_url); ?>'></td>
                </tr>
                
                <tr>
                    <th>サイト名</th>
                    <td><input type='text' id="sname" name='sname' class="regular-text" value='<?php echo esc_attr($html_name); ?>' required></td>
                </tr>
                
                <tr>
                    <th>エリア</th>
                    <td><input type='text' id="area" name='area' class="regular-text" value='<?php echo esc_attr($html_area); ?>'></td>
                </tr>
                
                <tr>
                    <th>サービス</th>
                    <td><input type='text' id="service" name='service' class="regular-text" value='<?php echo esc_attr($html_service); ?>'></td>
                </tr>
                
                <tr>
                    <th>営業時間</th>
                    <td><input type='text' id="time" name='time' class="regular-text" value='<?php echo esc_attr($html_time); ?>'></td>
                </tr>
                
                <tr>
                    <th>料金</th>
                    <td><textarea name='price' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_price); ?></textarea></td>
                </tr>
                
                <tr>
                    <th>電話番号</th>
                    <td><input type='text' id="tel" name='tel' class="regular-text" value='<?php echo esc_attr($html_tel); ?>'></td>
                </tr>
                
                <tr>
                    <th>相互フォロー</th>
                    <td><input type='number' id="sogo" name='sogo' class="small-text" value='<?php echo esc_attr($html_sogo); ?>' min="0"></td>
                </tr>
                
                <tr>
                    <th>ポイント</th>
                    <td><input type='number' id="point" name='point' class="small-text" value='<?php echo esc_attr($html_point); ?>' min="0"></td>
                </tr>
                
                <tr>
                    <th>プレミアム</th>
                    <td><input type='number' id="premium" name='premium' class="small-text" value='<?php echo esc_attr($html_premium); ?>' min="0"></td>
                </tr>
                
                <tr>
                    <th>タイプ(0,1,2)</th>
                    <td>
                        <select name="type" id="type">
                            <option value="0" selected>0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th>コピー</th>
                    <td><input type='text' id="copy" name='copy' class="regular-text" value='<?php echo esc_attr($html_copy); ?>'></td>
                </tr>
                
                <tr>
                    <th>コメント</th>
                    <td><textarea name='comment' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_comment); ?></textarea></td>
                </tr>
                
                <tr>
                    <th>おすすめポイント</th>
                    <td><textarea name='recommended' rows='5' cols='50' class="large-text"><?php echo esc_textarea($html_recommended); ?></textarea></td>
                </tr>
            </table>
            
            <div class="mdd-form-actions">
                <input type="submit" name="add" class="button button-primary" value="追加" />
                <a href="<?php echo site_url('area/list/'); ?>" class="button">リストに戻る</a>
            </div>
        </div>
    </form>
    <?php
}