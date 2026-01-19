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

4. `.env`ファイルのDB設定を以下の通り修正
    ```ini
    # DB設定
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel_db
    DB_USERNAME=laravel_user
    DB_PASSWORD=laravel_pass
    ```

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