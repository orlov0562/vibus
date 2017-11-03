# Vibus, версия 1
## Настройка нового пользователя

### Структура папок

Структура папок пользователя */opt/vibus/site/*
```plain
- /user/                    = root:user = 0751
  - /site.com/              = root:user = 0751
    - /backup/              = root:user = 0755
      - /script/            = root:user = 0770
        - script.sh         = user:user = 0644
      - /store/             = root:user = 0770
        - www.tar.gz        = user:user = 0644
    - /log/                 = root:user = 0755
      - /httpd/             = root:user = 0750
      - /nginx/             = root:user = 0750
      - /php-fpm/           = root:user = 0750
    - /public_html/         = root:user = 0771
      - /folder/            = user:user = 0755
      - index.php           = user:user = 0644
    - /secret/              = root:user = 0771
      - htpasswd            = user:user = 0644
    - /session/             = root:user = 0770
    - /tmp/                 = root:user = 0770
```    
    
