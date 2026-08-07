# ams-app

## プロジェクト概要
勤怠管理アプリケーションです。一般ユーザーの出勤・退勤・休憩の打刻、勤怠の一覧・詳細確認、勤怠修正申請機能、管理者によるスタッフの勤怠一覧・詳細確認、修正申請の承認機能を実装しています。あわせて、公開APIおよびSanctum認証による書き込みAPI、マイ勤怠レポート機能も実装しています。

## 環境構築

1. Dockerを起動する
2. プロジェクト直下で、以下のコマンドを実行する

​```bash
docker compose up -d --build
docker compose exec app bash
composer install
cp .env.example .env
​```

.envを開き環境変数を変更する

​```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
​```

## メール認証

MailHogを使用しています。Docker起動時に自動的に立ち上がるため、追加の会員登録などは不要です。

以下のURLからMailHogの管理画面にアクセスすると、送信されたメールを確認できます。

http://localhost:8025

.envファイルには以下のように設定してください。

​```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=任意のメールアドレス
​```

## テーブル仕様

### users テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| name | varchar(255) | | | ○ | |
| email | varchar(255) | | ○ | ○ | |
| email_verified_at | timestamp | | | | |
| password | varchar(255) | | | ○ | |
| role | varchar(255) | | | ○ | |
| remember_token | varchar(100) | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

### attendance_records テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | ○(dateとの組み合わせ) | ○ | users(id) |
| date | date | | ○(user_idとの組み合わせ) | ○ | |
| clock_in | time | | | | |
| clock_out | time | | | | |
| status | varchar(255) | | | ○ | |
| comment | varchar(255) | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

### breaks テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| attendance_record_id | bigint | | | ○ | attendance_records(id) |
| break_start | time | | | ○ | |
| break_end | time | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

### correction_requests テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| attendance_record_id | bigint | | | ○ | attendance_records(id) |
| approved_by | bigint | | | | users(id) |
| status | varchar(255) | | | ○ | |
| comment | varchar(255) | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

### correction_request_details テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| correction_request_id | bigint | | | ○ | correction_requests(id) |
| field_name | varchar(255) | | | ○ | |
| before_value | varchar(255) | | | | |
| after_value | varchar(255) | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

### attendance_summaries テーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | | ○ | users(id) |
| target_month | varchar(255) | | | | |
| total_working_minutes | int | | | | |
| total_overtime_minutes | int | | | | |
| average_working_minutes | int | | | | |
| late_count | int | | | | |
| early_leave_count | int | | | | |
| long_work_count | int | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

※現状、このテーブルへのデータ保存処理は実装されていません。マイ勤怠レポート機能(`/attendance/report`)は、`attendance_records`から都度動的に集計する方式で実装しています。

## ER図



## テストアカウント

name: user1(一般ユーザー)
email: user1@example.com
password: password

name: user2(一般ユーザー)
email: user2@example.com
password: password

name: user3(管理者)
email: user3@example.com
password: password

## 使用技術(実行環境)
- PHP 8.x
- Laravel 8.x
- MySQL 8.0
- nginx
- MailHog(メール認証)
- Laravel Sanctum(API認証)

## PHPUnitを利用したテストに関して

以下のコマンドで、全80件のテストを実行できます。

​```bash
php artisan test
​```

テストはSQLite(インメモリ)上で実行されるため、事前のテスト用DB作成は不要です。

