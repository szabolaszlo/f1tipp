# F1Tipp (Symfony 4)
www.f1tipp.nhely.hu

This project is a refactoring of original f1tipp. (https://github.com/szabolaszlo/f1tipp)

The original project based on own framework, I decide to change it to symfony.
So there is some legacy code from the original code, like src\LegacyService, but If I have time I will refactoring it one by one.

Used technologies:
- Symfony 4.3 https://symfony.com/doc/current/setup.html
- WebPack https://symfony.com/doc/current/frontend.html

# Docker
```
docker-compose up -d
```
## PHPMyAdmin
http://localhost:8080/
root - root

## Maildev
http://localhost:8001/#/

## Localhost
http://localhost/
pointer to public directory in repository

# Database
### Create DataBase
```
php bin/console doctrine:database:create
```
### Create Schema
```
php bin/console doctrine:database:create
```

# Project Production Details

## Cron
- `/cron/search_cover_photo` This cron is try to check new cover image from facebook/boxutca. Recommend refresh hourly.
- `/cron/collect_feeds` This cron is try to refresh news with motorsport.com feed. Recommend refersh every 5 minutes.