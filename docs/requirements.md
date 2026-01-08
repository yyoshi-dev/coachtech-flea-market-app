# 要件定義書

本ドキュメントは、本プロジェクトにおける要件全体を統合的に整理したトップレベル文書とする。
機能要件、UI要件、データ要件、テスト要件などの詳細は、各専用ドキュメントに分割して管理する。

---

## 1. 概要

本アプリケーションは、ユーザーが商品を出品し、購入できるマーケットプレイス機能を中心としたWebアプリケーションである。
ユーザー登録、ログイン、商品一覧、商品詳細、購入、プロフィール管理などの機能を提供する。

概要および開発プロセスは以下にまとめる。

- [プロジェクト概要](requirements/overview.md)
- [開発プロセス](requirements/development_process.md)

---

## 2. 機能要件

ユーザーストーリーに基づく機能要件は以下にまとめる。

- [機能要件](requirements/functional_requirements.md)

主な機能カテゴリは以下である。

- 会員登録 / ログイン / ログアウト
- 商品一覧 / 商品詳細
- いいね機能 / コメント機能
- 商品購入 / 支払い方法選択 / 配送先変更
- プロフィール確認 / 編集
- 商品出品
- メール認証 (応用要件)

---

## 3. 画面設計

画面仕様およびUIデザインは以下にまとめる。

- [画面設計](requirements/ui_design.md)

UIに関連する画像ファイルは以下に配置する。


---

## 4. テーブル設計

テーブル仕様書およびER図は以下にまとめる。

- [テーブル設計](requirements/database_schema.md)
- ER図
    <img src="requirements/er_diagram.drawio.png" alt="ロゴ" width="600">

---

## 5. 基本設計

アプリケーションの基本設計 (ルーティング、コントローラ、モデル、ビュー、バリデーション、ダミーデータ等)は以下にまとめる。

- [基本設計](requirements/basic_design.md)

---

## 6. テスト仕様書

テストケース一覧は以下にまとめる。

- [テストケース一覧](requirements/test_cases.md)

---

## 7. 要件ドキュメント構造
```
docs/
├── requirements.md
└── requirements/
    ├── basic_design.md
    ├── database_schema.md
    ├── development_process.md
    ├── er_diagram.drawio.png
    ├── functional_requirements.md
    ├── overview.md
    ├── test_cases.md
    ├── ui_design.md
    ├── fig/
    └── ui/
```




