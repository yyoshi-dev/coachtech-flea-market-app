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
    php artisan db:seed --class=LocalTestSeeder
    ```
    上記は検証用データを作成する処理であり、検証用のユーザーも同時に作成される
    検証用のユーザーが不要な場合は、以下を実行すればよい
     ```bash
    php artisan db:seed
    ```

---

## テストの準備

### 検証用ユーザー
- シーディングの実行において、以下を実行した場合は、検証用ユーザーを作成している
    ```bash
    php artisan db:seed --class=LocalTestSeeder
    ```
- 検証用ユーザー
  - 取引有ユーザー
    - 概要: 商品出品や商品購入の実績があるユーザーであり、認証は完了済み
    - メールアドレス: `test1@example.com`
    - パスワード: `test1234`
  - 未取引ユーザー
    - 概要: 商品出品や商品購入の実績がないユーザーであり、認証は完了済み
    - メールアドレス: `test2@example.com`
    - パスワード: `test1234`

### stripeのテスト

#### stripeのセットアップ
1. StripeのAPI情報を`.env`ファイルに設定
    ```ini
    STRIPE_SECRET=sk_test_********
    STRIPE_PUBLIC=pk_test_********
    ```
    ※ Laravel環境構築にて既に`.env`ファイルが作成されている前提

#### stripeの処理について
- **カード支払い:** 「購入する」ボタンを押すと、Stripe決済画面に遷移し、決済を完了する事で、購入処理が完了する
- **コンビニ支払い:** 「購入する」ボタンを押した時点で、購入処理が完了する

#### stripeテストの情報
stripeのテストは以下情報を用いて実施する
- カード支払い (成功)
  - メールアドレス: 任意
  - カード番号: `4242 4242 4242 4242`
  - 有効期限: 未来の日付
  - CVC: 任意の3桁
  - 名前: 任意

**※ コンビニ支払いに関しては、「購入する」ボタンを押した時点で購入処理が完了し、商品一覧画面に遷移するようになっており、stripeの決済処理は行われない為、stripe決済処理をテストする場合は、カード支払いを選択してください**
**※ コンビニ支払いでは、stripe側に決済情報は送信されません**


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