<?php
// 直接アクセスを防止
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1>Multi Data Display - 使い方ガイド</h1>
    
    <div class="card">
        <h2>プラグインの概要</h2>
        <p>
            Multi Data Displayは、カスタムデータテーブルから情報を取得し、管理・表示するためのプラグインです。
            店舗情報などを管理し、一覧表示や詳細表示を行うことができます。
        </p>
    </div>

    <div class="card">
        <h2>基本的な使い方</h2>
        
        <h3>1. データの一覧表示とアクセス方法</h3>
        <p>
            データ一覧にアクセスするには以下の方法があります：
        </p>
        <ul>
            <li>管理画面の「<strong>データテーブル</strong>」メニューから「<strong>使い方</strong>」をクリック</li>
            <li>または直接 <code><?php echo site_url('area/list/'); ?></code> にアクセス</li>
        </ul>
        
        <div class="notice notice-info inline">
            <p><strong>注意：</strong> データ管理ページは管理者権限を持つユーザーのみアクセスできます。</p>
        </div>
        
        <h3>2. データの検索とフィルタリング</h3>
        <p>
            データ一覧画面では、以下の方法でデータをフィルタリングできます：
        </p>
        <ul>
            <li><strong>検索ボックス</strong>: 店舗名、エリア、サービスなどでキーワード検索</li>
            <li><strong>エリアフィルター</strong>: 特定のエリアでフィルタリング</li>
            <li><strong>タイプフィルター</strong>: データのタイプ（0、1、2）でフィルタリング</li>
        </ul>
        
        <h3>3. データの編集</h3>
        <p>
            一覧画面の各レコードの「編集」ボタンをクリックすると編集画面が開きます。
            また、<code><?php echo site_url('area/edit/'); ?>?option=add</code> にアクセスすると、新規データを追加できます。
        </p>
        
        <div class="notice notice-warning inline">
            <p><strong>注意：</strong> データを削除する場合は慎重に行ってください。削除後は元に戻せません。</p>
        </div>
    </div>

    <div class="card">
        <h2>データ構造について</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>フィールド名</th>
                    <th>説明</th>
                    <th>データ型</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>url</code></td>
                    <td>店舗等のWEBサイトURL</td>
                    <td>文字列</td>
                </tr>
                <tr>
                    <td><code>sname</code></td>
                    <td>店舗名など</td>
                    <td>文字列</td>
                </tr>
                <tr>
                    <td><code>price</code></td>
                    <td>料金情報</td>
                    <td>テキスト</td>
                </tr>
                <tr>
                    <td><code>surl</code></td>
                    <td>一意のIDとして使用 (主キー)</td>
                    <td>文字列</td>
                </tr>
                <tr>
                    <td><code>area</code></td>
                    <td>エリア情報</td>
                    <td>テキスト</td>
                </tr>
                <tr>
                    <td><code>service</code></td>
                    <td>提供サービス</td>
                    <td>テキスト</td>
                </tr>
                <tr>
                    <td><code>copy</code></td>
                    <td>コピー文</td>
                    <td>長文テキスト</td>
                </tr>
                <tr>
                    <td><code>comment</code></td>
                    <td>コメント</td>
                    <td>長文テキスト</td>
                </tr>
                <tr>
                    <td><code>time</code></td>
                    <td>営業時間など</td>
                    <td>テキスト</td>
                </tr>
                <tr>
                    <td><code>tel</code></td>
                    <td>電話番号</td>
                    <td>テキスト</td>
                </tr>
                <tr>
                    <td><code>sogo</code></td>
                    <td>相互フォロー</td>
                    <td>数値</td>
                </tr>
                <tr>
                    <td><code>point</code></td>
                    <td>ポイント</td>
                    <td>数値</td>
                </tr>
                <tr>
                    <td><code>premium</code></td>
                    <td>プレミアム</td>
                    <td>数値</td>
                </tr>
                <tr>
                    <td><code>type</code></td>
                    <td>タイプ (0, 1, 2)</td>
                    <td>数値</td>
                </tr>
                <tr>
                    <td><code>recommended</code></td>
                    <td>おすすめポイント</td>
                    <td>長文テキスト</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>URL構造</h2>
        <ul>
            <li><code><?php echo site_url('area/list/'); ?></code> - データ一覧ページ</li>
            <li><code><?php echo site_url('area/edit/'); ?>?option=add</code> - 新規データ追加ページ</li>
            <li><code><?php echo site_url('area/edit/[surl]'); ?></code> - 特定データの編集ページ ([surl]は実際のsurlの値に置き換え)</li>
        </ul>
    </div>

    <div class="card">
        <h2>ページネーション</h2>
        <p>
            データが多い場合は自動的にページ分割されます (1ページあたり20件)。
            ページの下部にあるページネーションリンクで別のページに移動できます。
        </p>
    </div>

    <div class="card">
        <h2>トラブルシューティング</h2>
        <ul>
            <li><strong>ページが見つからない場合：</strong> パーマリンク設定を保存し直してみてください</li>
            <li><strong>データが表示されない場合：</strong> データベース接続とテーブル構造を確認してください</li>
            <li><strong>編集ページでエラーが発生する場合：</strong> surlパラメータが正しく設定されているか確認してください</li>
        </ul>
    </div>

    <div class="card">
        <h2>リストページのスクリーンショット</h2>
        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/list-page-sample.png'; ?>" 
             alt="リストページサンプル" style="max-width: 100%; border: 1px solid #ddd;">
    </div>

    <div class="card">
        <h2>編集ページのスクリーンショット</h2>
        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/edit-page-sample.png'; ?>" 
             alt="編集ページサンプル" style="max-width: 100%; border: 1px solid #ddd;">
    </div>

    <hr>
    <p class="description">
        このプラグインについてのサポートが必要な場合は、開発者にお問い合わせください。<br>
        バージョン: 1.0.0
    </p>
</div>

<style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        margin-top: 20px;
        padding: 15px 20px;
    }
    .card h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .card h3 {
        margin-top: 20px;
        margin-bottom: 10px;
    }
</style>