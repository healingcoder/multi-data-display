<?php
// phpファイルのURLに直接アクセスされても中身見られないようにするやつ
if ( ! defined( 'ABSPATH' ) ) exit;

// 管理メニューページの設定
function add_menu() {
    // メインメニューを追加
	add_menu_page(
        'Multi Data Display', // <title>タグの内容を設定
        'データテーブル', // 左メニューに表示される名前を設定
        'manage_options', // 表示権限
        'multi-data-display', // スラッグ（ページを開いたときのURL)
        'display_help_page', // メインメニューではヘルプページを表示
        'dashicons-list-view', // 表示アイコン（テーブル/リスト表示用）
        200 // メニューの表示順、200と大きい数字にしたので、メニューの一番下に表示される
    );
	
    // サブメニュー「ヘルプ」を追加（メインメニューと同じ関数を呼び出し）
	add_submenu_page(
		'multi-data-display', // 親メニューのスラッグ
		'使い方ヘルプ', // ページタイトル
		'使い方', // メニュー名
		'manage_options', // 権限
		'multi-data-display', // スラッグ（親メニューと同じにすることで最初のサブメニューになる）
		'display_help_page'
	);
	
    // サブメニュー「設定」を追加
	add_submenu_page(
		'multi-data-display', // 親メニューのスラッグ
		'設定', // ページタイトル
		'設定', // メニュー名
		'manage_options', // 権限 
		'multi-data-display-settings', // 一意のスラッグ
		function() {

		} 
	);
}

// ヘルプページ表示関数
function display_help_page() {
    // ヘルプページのHTMLを表示
    include(plugin_dir_path(__FILE__) . "../templates/help-page.php");
}