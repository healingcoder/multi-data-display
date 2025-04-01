<?php
/*
Plugin Name: Multi Data Display
Plugin URI:
Description: オリジナルテーブルからデータを抽出して表示するプラグイン
Version: 1.0.0
Author: HealingCode
Author URI: https://healingcoder.com/
License: GNU General Public License (GPL) 
*/
//エラー表示の設定
ini_set('display_errors', 1);
//phpファイルのURLに直接アクセスされても中身見られないようにするやつ
if ( ! defined( 'ABSPATH' ) ) exit;

// プラグインを有効化した時の処理を登録
register_activation_hook(__FILE__, 'mdd_activation');

// 有効化時に実行する関数
function mdd_activation() {
    require_once plugin_dir_path(__FILE__) . 'control/create_table_shop.php';
    create_custom_table();
}

// 管理メニュー関連の処理を読み込み
require_once plugin_dir_path(__FILE__) . 'control/menu.php';

// 店舗データ操作関連の処理を読み込み
require_once plugin_dir_path(__FILE__) . 'control/shop_functions.php';

// ショートコード関連の処理を読み込み
require_once plugin_dir_path(__FILE__) . 'control/shortcode_functions.php';

// 管理メニューが読み込まれる前に実行される admin_menuを指定します
add_action('admin_menu', 'add_menu');

// スタイルシートの登録
function mdd_enqueue_scripts() {
    wp_enqueue_style('mdd-styles', plugin_dir_url(__FILE__) . 'assets/css/mdd-styles.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'mdd_enqueue_scripts');
add_action('admin_enqueue_scripts', 'mdd_enqueue_scripts');

// カスタムリライトルールの追加
function mdd_add_rewrite_rules() {
    // リスト表示用のルール
    add_rewrite_rule(
        'area/list/?$',
        'index.php?pagename=area-list',
        'top'
    );
    
    // 編集画面用のルール
    add_rewrite_rule(
        'area/edit/?$',
        'index.php?pagename=area-edit',
        'top'
    );
    
    // 編集画面用のルール (surlパラメータあり)
    add_rewrite_rule(
        'area/edit/([^/]+)/?$',
        'index.php?pagename=area-edit&surl=$matches[1]',
        'top'
    );
}
add_action('init', 'mdd_add_rewrite_rules');

// フラッシュリライトルール（プラグイン有効化時に実行）
function mdd_flush_rewrite_rules() {
    mdd_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mdd_flush_rewrite_rules');

// カスタムクエリ変数の登録
function mdd_query_vars($vars) {
    $vars[] = 'surl';
    $vars[] = 'option';
    return $vars;
}
add_filter('query_vars', 'mdd_query_vars');

// テンプレートの選択
function mdd_template_include($template) {
    // リスト一覧画面
    if (get_query_var('pagename') === 'area-list') {
        // 管理者権限チェック
        if (!current_user_can('administrator')) {
            auth_redirect(); // 管理者でなければログイン画面にリダイレクト
            exit;
        }
        
        // カスタムテンプレートを使用
        $new_template = plugin_dir_path(__FILE__) . 'templates/area-list.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    
    // 編集画面
    if (get_query_var('pagename') === 'area-edit') {
        // 管理者権限チェック
        if (!current_user_can('administrator')) {
            auth_redirect(); // 管理者でなければログイン画面にリダイレクト
            exit;
        }
        
        // WordPressのクエリ変数からパラメータを取得し、スーパーグローバル変数に設定
        $surl = get_query_var('surl');
        if (!empty($surl) && empty($_GET['surl'])) {
            $_GET['surl'] = $surl;
        }
        
        $option = get_query_var('option');
        if (!empty($option) && empty($_GET['option'])) {
            $_GET['option'] = $option;
        }
        
        // カスタムテンプレートを使用
        $new_template = plugin_dir_path(__FILE__) . 'templates/area-edit.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'mdd_template_include');