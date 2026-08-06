# ams-app

## プロジェクト概要
勤怠管理アプリケーションです。一般ユーザーの出勤・退勤・休憩の打刻、勤怠の一覧・詳細確認、修正申請機能、管理者による勤怠承認・スタッフ管理機能などを実装しています。

## 環境構築

### Dockerビルド

```bash
git clone https://github.com/sakisugi29/ams-app.git
cd ams-app
docker compose up -d --build
```

### Laravel環境構築

```bash
docker compose exec php bash
composer install
cp .env.example .env
```
.envを開き環境変数を変更する
```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

## 使用技術(実行環境)

- PHP 8.x
- Laravel 8.x
- MySQL 8.0.26
- nginx 1.21.1
- MailHog（メール認証）

## テーブル仕様


