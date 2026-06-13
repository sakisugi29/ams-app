# ams-app

## プロジェクト概要
フリマアプリのクローンアプリケーションです。商品の出品・購入・いいね・コメント機能などを実装しています。

## 環境構築

### Dockerビルド

```bash
git clone https://github.com/sakisugi29/furima-app.git
cd furima-app
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
