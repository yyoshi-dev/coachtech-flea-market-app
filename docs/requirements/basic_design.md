# 基本設計書

---

## Route及びController

| 画面名称                              | パス                          | メソッド  | ルート先コントローラー | アクション                         | 認証必須 | 説明                                               |
| ------------------------------------- | ----------------------------- | --------- | ---------------------- | ---------------------------------- | -------- | -------------------------------------------------- |
| 商品一覧画面（トップ画面）            | `/`                           | GET       | ItemController         | index                              |          | 商品一覧画面表示機能 (商品検索機能含む)            |
| 商品一覧画面（トップ画面）_マイリスト | `/?tab=mylist`                | GET       | ItemController         | index                              |          | 商品一覧のマイリスト表示機能                       |
| 会員登録画面                          | `/register`                   | GET, POST | Fortify (内部)         | register                           |          | 会員登録機能                                       |
| ログイン画面                          | `/login`                      | GET, POST | Fortify (内部)         | login                              |          | ログイン機能                                       |
| 商品詳細画面                          | `/item/{item_id}`             | GET       | ItemController         | show                               |          | 商品詳細画面表示機能                               |
| 商品購入画面                          | `/purchase/{item_id}`         | GET, POST | PurchaseController     | showPurchasePage, purchase         | 〇       | 商品購入画面表示機能、商品購入機能                 |
| 住所変更ページ                        | `/purchase/address/{item_id}` | GET, POST | PurchaseController     | showAddressEditPage, updateAddress | 〇       | 住所変更画面表示機能、住所変更機能                 |
| 商品出品画面                          | `/sell`                       | GET, POST | ExhibitionController   | create, store                      | 〇       | 商品出品画面表示機能、商品出品機能                 |
| プロフィール画面                      | `/mypage`                     | GET       | ProfileController      | showProfilePage                    | 〇       | プロフィール画面表示機能                           |
| プロフィール編集画面                  | `/mypage/profile`             | GET, POST | ProfileController      | showProfileEditPage, editProfile   | 〇       | プロフィール編集画面表示機能、プロフィール編集機能 |
| プロフィール画面_購入した商品一覧     | `/mypage?page=buy`            | GET       | ProfileController      | showProfilePage                    | 〇       | プロフィール画面での購入した商品一覧表示機能       |
| プロフィール画面_出品した商品一覧     | `/mypage?page=sell`           | GET       | ProfileController      | showProfilePage                    | 〇       | プロフィール画面での出品した商品一覧表示機能       |
| いいね機能                            | `/item/{item_id}/like`        | POST      | ItemController         | like                               | 〇       | 商品詳細画面のいいね機能                           |
| コメント機能                          | `/item/{item_id}/comment`     | POST      | ItemController         | comment                            | 〇       | 商品詳細画面のコメント機能                         |
| 支払い方法選択機能                    | `/purchase/payment/{item_id}` | POST      | PurchaseController     | storePaymentMethodSelection        | 〇       | 商品購入画面の支払い方法選択機能                   |
| stripe決済成功時の処理機能            | `/purchase/success/{item_id}` | GET       | PurchaseController     | handleStripeSuccess                | 〇       | stripe決済成功時の処理機能                         |
| メール認証誘導画面                    | `/email/verify`               | GET       | 匿名ルート             |                                    | 〇       | メール認証誘導画面の表示機能                       |
| mailhog画面                           | `/email/verify/mailhog`       | GET       | 匿名ルート             |                                    | 〇       | mailhog画面表示機能 (テスト用にルートを設定)       |

---

## Model

| モデルファイル名 | 説明                         |
| ---------------- | ---------------------------- |
| Order            | ordersテーブル用             |
| PaymentMethod    | payment_methodsテーブル用    |
| Product          | productsテーブル用           |
| ProductCategory  | product_categoriesテーブル用 |
| ProductComment   | product_commentsテーブル用   |
| ProductCondition | product_conditionsテーブル用 |
| ProductLike      | product_likesテーブル用      |
| User             | usersテーブル用              |

---

## View

| 画面名称                         | bladeファイル名               |
| -------------------------------- | ----------------------------- |
| 商品一覧画面（トップ画面）       | items/index.blade.php         |
| 会員登録画面                     | auth/register.blade.php       |
| ログイン画面                     | auth/login.blade.php          |
| 商品詳細画面                     | items/detail.blade.php        |
| 商品購入画面                     | items/purchase.blade.php      |
| 送付先住所変更画面               | items/address.blade.php       |
| 商品出品画面                     | items/sell.blade.php          |
| プロフィール画面                 | mypage/mypage.blade.php       |
| プロフィール編集画面（設定画面） | mypage/profile-edit.blade.php |
| メール認証誘導画面               | auth/verify-email.blade.php   |

---

## バリデーション

| バリデーションファイル名 | フォーム         | ルール                                            | メッセージ                                                                                                  |
| ------------------------ | ---------------- | ------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| RegisterRequest.php      | ユーザー名       | 入力必須、20文字以内                              | お名前を入力してください、お名前は20文字以下で入力してください                                              |
|                          | メールアドレス   | 入力必須、メール形式                              | メールアドレスを入力してください、メールアドレスはメール形式で入力してください                              |
|                          | パスワード       | 入力必須、8文字以上                               | パスワードを入力してください、パスワードは8文字以上で入力してください                                       |
|                          | 確認用パスワード | 入力必須、8文字以上、「パスワード」との重複のみ可 | 確認用パスワードを入力してください、確認用パスワードは8文字以上で入力してください、パスワードと一致しません |
| LoginRequest.php         | メールアドレス   | 入力必須、メール形式                              | メールアドレスを入力してください、メールアドレスはメール形式で入力してください                              |
|                          | パスワード       | 入力必須                                          | パスワードを入力してください                                                                                |
| CommentRequest.php       | 商品コメント     | 入力必須、最大文字数255                           | コメントを入力してください、コメントは255文字以内で入力してください                                         |
| PurchaseRequest.php      | 支払い方法       | 選択必須                                          | 支払い方法を選択してください                                                                                |
|                          | 配送先           | 選択必須                                          | 配送先を選択してください                                                                                    |
| AddressRequest.php       | 郵便番号         | 入力必須、ハイフンありの8文字                     | 郵便番号を入力してください、郵便番号はハイフンありの8文字で入力してください                                 |
|                          | 住所             | 入力必須                                          | 住所を入力してください                                                                                      |
| ProfileRequest.php       | プロフィール画像 | 拡張子が.jpegもしくは.png                         | プロフィール画像の拡張子は.jpegもしくは.pngでアップロードしてください                                       |
|                          | ユーザー名       | 入力必須、20文字以内                              | お名前を入力してください、お名前は20文字以下で入力してください                                              |
|                          | 郵便番号         | 入力必須、ハイフンありの8文字                     | 郵便番号を入力してください、郵便番号はハイフンありの8文字で入力してください                                 |
|                          | 住所             | 入力必須                                          | 住所を入力してください                                                                                      |
| ExhibitionRequest.php    | 商品名           | 入力必須                                          | 商品名を入力してください                                                                                    |
|                          | 商品説明         | 入力必須、最大文字数255                           | 商品説明を入力してください、商品説明は255文字以下で入力してください                                         |
|                          | 商品画像         | アップロード必須、拡張子が.jpegもしくは.png       | 商品画像をアップロードしてください、商品画像の拡張子は.jpegもしくは.pngでアップロードしてください           |
|                          | 商品のカテゴリー | 選択必須                                          | 商品のカテゴリーを選択してください                                                                          |
|                          | 商品の状態       | 選択必須                                          | 商品の状態を選択してください                                                                                |
|                          | 商品価格         | 入力必須、数値型、0円以上                         | 商品価格を入力してください、商品価格は数値型で入力してください、商品価格は0円以上で入力してください         | 価格 | 入力必須、数値型、0円以上 |

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
