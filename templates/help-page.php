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

        <h3>4. フロントエンド編集機能</h3>
        <p>
            新機能として、管理者がログインしている場合は、フロントエンドのショートコード表示でも各店舗データの横に「編集」ボタンが表示されるようになりました。
            これにより、管理画面に移動することなく、直接店舗データを編集できます。
        </p>
        <div class="notice notice-info inline">
            <p><strong>メモ：</strong> この編集ボタンは管理者がログインしている場合のみ表示され、一般ユーザーには表示されません。</p>
        </div>
    </div>

    <div class="card">
        <h2>CSVインポート/エクスポート機能</h2>
        
        <h3>1. CSVエクスポート</h3>
        <p>
            データをCSVファイルとしてエクスポートするには：
        </p>
        <ol>
            <li>データ一覧ページで「<strong>データをCSV出力</strong>」ボタンをクリック</li>
            <li>CSVファイルが自動的にダウンロードされます</li>
            <li>ファイル名は「shop_export_日付_時間.csv」の形式で保存されます</li>
        </ol>
        <p>
            エクスポートされたCSVファイルは、バックアップや他システムへのデータ移行に利用できます。
        </p>
        
        <h3>2. CSVインポート</h3>
        <p>
            CSVファイルからデータをインポートするには：
        </p>
        <ol>
            <li>データ一覧ページで「<strong>CSVインポート</strong>」ボタンをクリック</li>
            <li>インポートフォームが表示されたら、以下の設定を行います：
                <ul>
                    <li><strong>CSVファイル</strong>：インポートするCSVファイルを選択</li>
                    <li><strong>インポートモード</strong>：
                        <ul>
                            <li>追加のみ：既存データを残したまま、新しいデータのみを追加</li>
                            <li>更新：同一surlの場合は既存データを上書き</li>
                            <li>置換：全データを削除してからインポート</li>
                        </ul>
                    </li>
                    <li><strong>1行目をヘッダーとして扱う</strong>：CSVの1行目がカラム名である場合はチェック</li>
                </ul>
            </li>
            <li>「<strong>インポート</strong>」ボタンをクリックしてインポートを実行</li>
        </ol>
        
        <h3>3. サンプルCSVダウンロード</h3>
        <p>
            正しいCSV形式を確認するには：
        </p>
        <ol>
            <li>インポートフォーム内の「<strong>サンプルCSVをダウンロード</strong>」ボタンをクリック</li>
            <li>ダウンロードされたCSVファイルを参考にして、自分のデータを作成できます</li>
        </ol>
        
        <div class="notice notice-info inline">
            <p><strong>CSVファイル形式：</strong> CSVファイルには url, sname, price, surl, area, service, copy, comment, time, tel, sogo, point, premium, type, recommended の列が必要です。surlとsnameは必須項目です。</p>
        </div>
    </div>

    <div class="card">
        <h2>画像アップロード機能</h2>
        
        <h3>1. 店舗画像のアップロード</h3>
        <p>
            各店舗データに画像を追加するには：
        </p>
        <ol>
            <li>店舗データの編集画面を開きます</li>
            <li>基本情報の下にある「<strong>店舗画像</strong>」セクションに移動</li>
            <li>「<strong>画像ファイル</strong>」欄でアップロードする画像を選択</li>
            <li>「<strong>16:9のアスペクト比に自動調整する</strong>」オプションを必要に応じて設定</li>
            <li>「<strong>アップロード</strong>」ボタンをクリックして画像をアップロード</li>
        </ol>
        
        <h3>2. 画像を削除する</h3>
        <p>
            すでにアップロードされている画像を削除するには：
        </p>
        <ol>
            <li>店舗データの編集画面で「<strong>店舗画像</strong>」セクションに移動</li>
            <li>現在の画像の下にある「<strong>画像を削除</strong>」ボタンをクリック</li>
            <li>確認ダイアログで「OK」をクリックして削除を実行</li>
        </ol>
        
        <h3>3. 画像のフロントエンドでの表示</h3>
        <p>
            ショートコードを使用して店舗画像を表示できます：
        </p>
        <code>[prefecture show_image="yes"]</code>
        <p>
            <code>show_image</code>パラメータは「yes」または「no」に設定可能です（デフォルトは「yes」）。
        </p>
        
        <div class="notice notice-info inline">
            <p><strong>推奨画像サイズ：</strong> 店舗画像は自動的に16:9の比率に調整されます。最適な表示結果を得るには、1280x720ピクセルの画像を使用することをお勧めします。</p>
        </div>
    </div>

    <div class="card">
        <h2>ショートコードの使い方</h2>
        <p>WordPressの投稿や固定ページ内で店舗情報を表示するには、<code>[prefecture]</code>ショートコードを使用します。</p>
        
        <h3>基本的な使い方</h3>
        <pre><code>[prefecture]</code></pre>
        <p>これにより、デフォルト設定で店舗一覧が表示されます。デフォルトでは、「相互フォロー」「ポイント」「プレミアム」の合計が大きい順に表示されます。</p>
        
        <h3>パラメータ一覧</h3>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>パラメータ</th>
                    <th>説明</th>
                    <th>例</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>area</code></td>
                    <td>エリアで絞り込み</td>
                    <td><code>[prefecture area="東京"]</code></td>
                </tr>
                <tr>
                    <td><code>service</code></td>
                    <td>サービスで絞り込み</td>
                    <td><code>[prefecture service="マッサージ"]</code></td>
                </tr>
                <tr>
                    <td><code>type</code></td>
                    <td>タイプで絞り込み (0, 1, 2)</td>
                    <td><code>[prefecture type="1"]</code></td>
                </tr>
                <tr>
                    <td><code>premium</code></td>
                    <td>プレミアム値で絞り込み</td>
                    <td><code>[prefecture premium="1"]</code></td>
                </tr>
                <tr>
                    <td><code>limit</code></td>
                    <td>表示件数（デフォルト: 10）</td>
                    <td><code>[prefecture limit="20"]</code></td>
                </tr>
                <tr>
                    <td><code>orderby</code></td>
                    <td>並び順のフィールド (sname, area, service, sogo, point, premium, type, score)</td>
                    <td><code>[prefecture orderby="point"]</code></td>
                </tr>
                <tr>
                    <td><code>order</code></td>
                    <td>並び順 (ASC, DESC)</td>
                    <td><code>[prefecture orderby="point" order="DESC"]</code></td>
                </tr>
                <tr>
                    <td><code>show_image</code></td>
                    <td>店舗画像を表示するか (yes, no)</td>
                    <td><code>[prefecture show_image="yes"]</code></td>
                </tr>
            </tbody>
        </table>
        
        <h3>複合条件の例</h3>
        <pre><code>[prefecture area="大阪" service="エステ" limit="5" orderby="score" order="DESC" show_image="yes"]</code></pre>
        <p>この例では、大阪エリアのエステサービスを提供する店舗を、総合スコア（相互フォロー+ポイント+プレミアム）の高い順に最大5件表示します。各店舗の画像も表示されます。</p>
        
        <div class="notice notice-info inline">
            <p><strong>新機能：</strong> デフォルトの並び順が変更され、「相互フォロー」「ポイント」「プレミアム」の合計値（score）が大きい順に表示されるようになりました。また、管理者がログインしている場合は各店舗の表示に編集ボタンが表示されます。</p>
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
            <li><strong>404エラーが表示される場合：</strong> 「設定 > パーマリンク設定」画面で設定を保存し直してください</li>
            <li><strong>CSVインポートでエラーが発生する場合：</strong> 
                <ul>
                    <li>CSVファイルの文字コードを確認してください（UTF-8推奨）</li>
                    <li>必須フィールド（surlとsname）が入力されていることを確認してください</li>
                    <li>テキストフィールドの長さが制限を超えていないか確認してください</li>
                </ul>
            </li>
            <li><strong>画像アップロードができない場合：</strong> 
                <ul>
                    <li>wp-content/uploads ディレクトリの書き込み権限を確認してください</li>
                    <li>アップロードする画像のファイルサイズが PHP の設定上限を超えていないか確認してください</li>
                    <li>許可されているファイル形式（JPEG/PNG/GIF）かどうか確認してください</li>
                </ul>
            </li>
            <li><strong>データが表示されない場合：</strong> データベース接続とテーブル構造を確認してください</li>
            <li><strong>編集ボタンが表示されない場合：</strong> ユーザーが管理者としてログインしているか確認してください</li>
        </ul>
    </div>

    <hr>
    <p class="description">
        このプラグインについてのサポートが必要な場合は、開発者にお問い合わせください。<br>
        バージョン: 1.2.1
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
    pre {
        background-color: #f5f5f5;
        padding: 10px;
        border: 1px solid #eee;
        border-radius: 3px;
        overflow-x: auto;
    }
    code {
        background-color: #f5f5f5;
        padding: 2px 5px;
        border-radius: 3px;
    }
</style>