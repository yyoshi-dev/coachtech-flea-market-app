# フリマアプリ

## 環境構築

### Dockerビルド
1. リポジトリをクローン
    ```bash
    git clone git@github.com:yyoshi-dev/coachtech-flea-market-app.git
    ```

2. ディレクトリに移動
    ```bash
    cd coachtech-freemarket-app
    ```

3. コンテナを起動
    ```bash
    docker compose up -d --build
    ```

### Laravel環境構築
1. PHPコンテナに接続
    ```bash
    docker compose exec php bash
    ```

2. コンテナ内で依存関係をインストール
    ```bash
    composer install
    ```

3. `.env.example`をコピーして`.env`を作成
    ```bash
    cp .env.example .env
    ```

4. `.env`ファイルのDB設定、Stripe用API設定を修正
    ```ini
    # DB設定
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel_db
    DB_USERNAME=laravel_user
    DB_PASSWORD=laravel_pass

    # Stripe用API設定
    STRIPE_SECRET=
    STRIPE_PUBLIC=
    ```
    ※ Stripe用のAPIキーは別途共有するものを使用する事
    <br>

5. アプリケーションキーを生成
    ```bash
    php artisan key:generate
    ```

6. storageのシンボリックリンクを作成
    ```bash
    php artisan storage:link
    ```

7. storageディレクトリへ権限を付与
    ```bash
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    ```

8.  マイグレーションを実行
    ```bash
    php artisan migrate
    ```

9.  シーディングを実行
    ```bash
    php artisan db:seed
    ```

---

## 使用技術 (実行環境)
- PHP：8.4.16
- Laravel: 12.46.0
- laravel/fortify: 1.33.0
- MySQL: 8.0.40
- nginx: 1.27.2
- phpMyAdmin: 5.2.3
- mailhog: 1.0.1
- stripe/stripe-php: v19.2.0

---

## ER図
![ER図](docs/requirements/er_diagram.drawio.png)

※ 詳細要件は、[要件定義書](docs/requirements.md)に記載している

---

## URL (開発環境)
- 商品一覧画面 (トップ画面): http://localhost/
- 会員登録画面: http://localhost/register
- ログイン画面: http://localhost/login
- phpMyAdmin: http://localhost:8080/
- mailhog: http://localhost:8025/

## テスト

### stripeテストの準備

#### stripeのセットアップ
1. 開発者から共有されたAPIを`.env`ファイルに設定
    ```ini
    STRIPE_SECRET=sk_test_********
    STRIPE_PUBLIC=pk_test_********
    ```
    ※ Laravel環境構築にて既に`.env`ファイルが作成されている前提

#### Webhookのセットアップ
##### ngrokのセットアップ
1. [ngrokの公式サイト](https://ngrok.com)にアクセスしてngrokのアカウントを作成 (未実施の場合)
2. ngrokをインストール (未実施の場合)
    - インストール方法は、[ngrokログイン後のSetup & Installation](https://dashboard.ngrok.com/get-started/setup)に記載

3. ngrokのシークレットキー (Authtoken)を発行
4. ngrokにシークレットキーを設定
    ```bash
    ngrok config add-authtoken <Authtoken>
    ```

5. ngrokでローカルサーバーを公開
    ```bash
    ngrok http 80
    ```
    ※ 表示される`https://xxxxx.ngrok-free.dev`は後述のstripe設定で使用
    ※ ngrokを起動する度にURLが変わる為、stripe側の設定も毎回更新が必要

##### stripeのセットアップ
1. [stripe dashboard](https://dashboard.stripe.com/)にアクセス
2. stripe dashboardのWebhook設定ページを開く
    - UIはバージョンにより異なる為、適宜読み替える事
    - 検索欄で「Webhook」と入力・検索すると見つけ易い

3. 「送信先を追加する」からWebhookエンドポイントを登録
    - イベントのリッスン元: お客様のアカウント
    - APIバージョン: 最新版でよい
    - イベント: `payment_intent.succeeded`にチェック (今回は決済完了時のみを対象)
    - 送信先のタイプは: Webhookエンドポイント
    - 送信先の設定画面: エンドポイントURLに、`https://xxxxx.ngrok-free.dev/stripe/webhook`を設定 (`https://xxxxx.ngrok-free.dev`には、ngrok起動時に表示されたURLを指定)
    - 「送信先を作成する」を実行

4.  Webhookエンドポイント作成後、画面右側に表示される署名シークレットを`.env`ファイルに設定
    ```ini
    STRIPE_WEBHOOK_SECRET=whsec_XXXXX
    ```
**※ ngrokのURLが変わった場合は、stripe dashboardのエンドポイントURLを更新する必要有 (新規作成でない限り、署名シークレットは変更されない)**

#### stripeテストの情報
stripeのテストは以下情報を用いて実施する
- カード支払い (成功)
  - メールアドレス: 任意
  - カード番号: `4242 4242 4242 4242`
  - 有効期限: 未来の日付
  - CVC: 任意の3桁
  - 名前: 任意
- コンビニ支払い (成功)
  - メールアドレス: 任意
  - 名前: 任意
  - 電話番号: 任意
  - コンビニ選択: 任意

**※ テスト環境のコンビニ決済は自動で成功となるが、反映までに数分かかる為、stripe dashboard上で、該当取引のステータスが「成功」になってから、アプリ側の処理が正しく動作しているかを確認する事**
