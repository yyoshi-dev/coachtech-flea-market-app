# 基本設計書

---

## Route及びController

| 画面名称                              | パス                          | メソッド | ルート先コントローラー | アクション | 認証必須 | 説明           |
| ------------------------------------- | ----------------------------- | -------- | ---------------------- | ---------- | -------- | -------------- |
| 商品一覧画面（トップ画面）            | `/`                           | GET      | ItemController         | index      |          | 商品一覧ページ |
| 商品一覧画面（トップ画面）_マイリスト | `/?tab=mylist`                |          |                        |            |          |                |
| 会員登録画面                          | `/register`                   |          |                        |            |          |                |
| ログイン画面                          | `/login`                      |          |                        |            |          |                |
| 商品詳細画面                          | `/item/{item_id}`             |          |                        |            |          |                |
| 商品購入画面                          | `/purchase/{item_id}`         |          |                        |            |          |                |
| 住所変更ページ                        | `/purchase/address/{item_id}` |          |                        |            |          |                |
| 商品出品画面                          | `/sell`                       |          |                        |            |          |                |
| プロフィール画面                      | `/mypage`                     |          |                        |            |          |                |
| プロフィール編集画面                  | `/mypage/profile`             |          |                        |            |          |                |
| プロフィール画面_購入した商品一覧     | `/mypage?page=buy`            |          |                        |            |          |                |
| プロフィール画面_出品した商品一覧     | `/mypage?page=sell`           |          |                        |            |          |                |

---

## Model

| モデルファイル名 | 説明                        |
| ---------------- | --------------------------- |
| User             | Laravel標準構成に基づき作成 |

---

## View

| 画面名称                         | bladeファイル名 |
| -------------------------------- | --------------- |
| 商品一覧画面（トップ画面）       |                 |
| 会員登録画面                     |                 |
| ログイン画面                     |                 |
| 商品詳細画面                     |                 |
| 商品購入画面                     |                 |
| 送付先住所変更画面               |                 |
| 商品出品画面                     |                 |
| プロフィール画面                 |                 |
| プロフィール編集画面（設定画面） |                 |

---

## バリデーション

| バリデーションファイル名 | フォーム         | ルール                                            |
| ------------------------ | ---------------- | ------------------------------------------------- |
| RegisterRequest.php      | ユーザー名       | 入力必須、20文字以内                              |
|                          | メールアドレス   | 入力必須、メール形式                              |
|                          | パスワード       | 入力必須、8文字以上                               |
|                          | 確認用パスワード | 入力必須、8文字以上、「パスワード」との重複のみ可 |
| LoginRequest.php         | メールアドレス   | 入力必須、メール形式                              |
|                          | パスワード       | 入力必須                                          |
| CommentRequest.php       | 商品コメント     | 入力必須、最大文字数255                           |
| PurchaseRequest.php      | 支払い方法       | 選択必須                                          |
|                          | 配送先           | 選択必須                                          |
| AddressRequest.php       | 郵便番号         | 入力必須、ハイフンありの8文字                     |
|                          | 住所             | 入力必須                                          |
| ProfileRequest.php       | プロフィール画像 | 拡張子が.jpegもしくは.png                         |
|                          | ユーザー名       | 入力必須、20文字以内                              |
|                          | 郵便番号         | 入力必須、ハイフンありの8文字                     |
|                          | 住所             | 入力必須                                          |
| ExhibitionRequest.php    | 商品名           | 入力必須                                          |
|                          | 商品説明         | 入力必須、最大文字数255                           |
|                          | 商品画像         | アップロード必須、拡張子が.jpegもしくは.png       |
|                          | 商品のカテゴリー | 選択必須                                          |
|                          | 商品の状態       | 選択必須                                          |
|                          | 商品価格         | 入力必須、数値型、0円以上                         |

---

## 商品データ一覧
商品データのダミー作成時に記載する情報は以下の通りであり、コーディングの際は以下の要件を満たし、ダミーデータを必ず作成する必要がある。

| 商品名           | 価格   | ブランド名 | 商品説明                               | img_url                                                                                                                                                                         | コンディション       |
| ---------------- | ------ | ---------- | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------- |
| 腕時計           | 15,000 | Rolax      | スタイリッシュなデザインのメンズ腕時計 | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg                                                                                            | 良好                 |
| HDD              | 5,000  | 西芝       | 高速で信頼性の高いハードディスク       | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg                                                                                                | 目立った傷や汚れなし |
| 玉ねぎ3束        | 300    | なし       | 新鮮な玉ねぎ3束のセット                | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg                                                                                                   | やや傷や汚れあり     |
| 革靴             | 4,000  |            | クラシックなデザインの革靴             | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg                                                                                  | 状態が悪い           |
| ノートPC         | 45,000 |            | 高性能なノートパソコン                 | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg                                                                                           | 良好                 |
| マイク           | 8,000  | なし       | 高音質のレコーディング用マイク         | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg                                                                                            | 目立った傷や汚れなし |
| ショルダーバッグ | 3,500  |            | おしゃれなショルダーバッグ             | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg                                                                                         | やや傷や汚れあり     |
| タンブラー       | 500    | なし       | 使いやすいタンブラー                   | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg                                                                                             | 状態が悪い           |
| コーヒーミル     | 4,000  | Starbacks  | 手動のコーヒーミル                     | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg                                                                                 | 良好                 |
| メイクセット     | 2,500  |            | 便利なメイクアップセット               | https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg | 目立った傷や汚れなし |
