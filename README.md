# CONTACT-FORM-APP

## 概要
COACHTECH 確認テストで作成した成果物です。
ユーザーからのお問い合わせ管理を想定したアプリ。お問い合わせフォームにてユーザーの基本情報、問い合わせ種分類、詳細内容を入力し、管理者が問い合わせを確認する想定。
※ISSUE１が完了したタイミングで、メインブランチに初期コミットをプッシュしていない
事に気づき、ISSUE１完了済状態でメインにプッシュ。
ISSUE１とメインが同一だとプルリクエストが作成できないため、本READ MEファイルに
このコメントを追加。

## ER図

## 環境構築手順
1. Laravelプロジェクトの作成 (Laravel 10.x)
以下のDockerコマンドを実行。
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \

2. Laravel Sailのインストール
以下のコマンドを実行し、プロジェクトディレクトリに移動→Laravel Sailをインストール。
cd contact-form-app
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer require laravel/sail --dev
以下のDockerコマンドを実行し、Sailの設定ファイルをパブリッシュ（MySQLを選択）
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    php artisan sail:install --with=mysql
※M1/M2/M3 Mac（Apple Silicon）をお使いの方
Apple Silicon搭載のMacでは、`sail up -d`実行時に以下のエラーが発生することがありますが、解決方法の詳述は、割愛。

3. .env ファイルの設定
.env ファイルを開き、データベース接続情報が以下と一致していることを確認します。
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
重要: DB_HOST は localhost や 127.0.0.1 ではなく、Dockerコンテナ名である mysql を指定します。

4. フロントエンドのセットアップ (Vite & Tailwind CSS)
本プロジェクトでは、フロントエンドのスタイリングにTailwind CSSを使用します。
Sailを起動する。
./vendor/bin/sail up -d

NPM依存パッケージをインストール。
sail npm install

Tailwind CSSのインストール
sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer
sail npm install alpinejs

設定ファイルの生成
sail npx tailwindcss init -p

Tailwind CSSのテンプレートパス設定
プロジェクトフォルダ直下のtailwind.config.js を開き、以下のように設定。
/** @type {import("tailwindcss").Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

提供リポジトリのresourcesディレクトリと入れ替え
以下のリポジトリをクローンし、resourcesディレクトリを丸ごと入れ替える。
git clone https://github.com/coachtech-prepared-file/Preparedblade-ConfirmationTest-ContactForm.git

入れ替え手順:
①プロジェクトとは別のフォルダ（ダウンロード等）でクローンコマンドを実行
② Finderでプロジェクトフォルダを開きます。
open .
③ プロジェクト内の resources フォルダを削除します。
④クローンしたリポジトリ内の resources フォルダをプロジェクト直下にコピーします。

6. Vite開発サーバーの起動
sail npm run dev
注意: sail npm run dev は実行したままにしておく必要があるため、別のターミナルで実行。

5. phpMyAdminの追加
compose.yaml を開き、mysql サービスの後に以下の設定を挿入する。
※mysqlと同じ階層に挿入しないと正常動作しないため、注意する事。
compose.yaml に追加する内容:

    phpmyadmin:
        image: 'phpmyadmin:latest'
        ports:
            - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'
        environment:
            PMA_HOST: mysql
            PMA_USER: '${DB_USERNAME}'
            PMA_PASSWORD: '${DB_PASSWORD}'
        networks:
            - sail
        depends_on:
            - mysql

6. Sailの起動とエイリアス設定
Sailをバックグラウンドで起動
./vendor/bin/sail up -d

エイリアスを設定して 'sail' だけでコマンドを実行できるようにする
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc

シェルを再起動するか、新しいターミナルを開いてエイリアスを有効にする
exec $SHELL

7. アプリケーションキーの生成
ルートで以下のコマンドを実行する
sail artisan key:generate

8. データベースのマイグレーションと初期データ投入
以下のコマンドでテーブルを作成し、初期データを投入します。
sail artisan migrate --seed

## 使用技術
- PHP 8.x
- Laravel 10.x
- 
- 
- 
- 
- 

## APIエンドポイント一覧
メソッド	  パス	                  概要
GET      /api/v1/contacts	         お問い合わせ一覧（検索・ページネーション付き）
GET      /api/v1/contacts/{contact}  お問い合わせ詳細（カテゴリ・タグ含む）
POST     /api/v1/contacts	         お問い合わせ新規作成
PUT      /api/v1/contacts/{contact}  お問い合わせ更新
DELETE   /api/v1/contacts/{contact}  お問い合わせ削除

## 開発環境URL


## 作成者
甲田　太志