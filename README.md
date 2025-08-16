# 環境構築



## Dockerビルド

・git clone git@github.com:coachtech-material/laravel-docker-template.git

・docker-compose up -d --build


## Laravel環境構築

・docker-compose exec php bash

・composer install

・cp .env.example .env　ーー＞　環境変数の変更

・php artisan key:generate

・php artisan migrate

・php artisan db:seed

## テスト

・php artisan test


## 開発環境

トップ画面　http://localhost/attendance

ユーザー登録　http://localhost/register

管理者ログイン　http://localhost/admin/login

phpMyAdmin　http://localhost:8080/index.php

mailhog　http://localhost:8026

## ダミーデータ

### 一般

メールアドレス　　user@example.com

パスワード　　　　userpass

### 管理者

メールアドレス　　admin@example.com

パスワード　　　　adminpass

## 使用技術

php　7.4.9-fpm

Laravel 　8.75

MySQL　8.0.26

nginx:1.21.1
