# Vibus, версия 1
## Восстановление базы данных в формате InnoDB при наличии только файлов frm и ibd

### Проверяем чтобы версия mysql была 5.6+
```bash
mysql -V
```
### Устанавливаем mysql-utils
```bash
yum install mysql-utils
```

### Восстанавливаем структуру таблицы
```bash
mysqlfrm --server={mysql-user}:{mysql-password}@localhost --user=root --port 3308 '~/broken/table.frm' > table-struct.sql
```
Тут table.frm исходный файл вашей таблицы.

На выходе должны получить CREATE TABLE ... в файле table-struct.sql

### Подготовка таблицы
- Создаем новую базу данных, например fixed
- Выполняем инструкции по созданию таблицы из файла table-struct.sql, создадутся два файла table.ibd и table.frm, тут: /var/lib/mysql/fixed/
- Удаляем связь с ibd файлом table.ibd, выполняем SQL
```bash
ALTER TABLE table DISCARD TABLESPACE;
```
файл /var/lib/mysql/fixed/table.ibd должен исчезнуть

### Копируем файл старый файл table.ibd в созданную базу
```bash
cp ~/broken/table.ibd /var/lib/mysql/fixed/table.ibd
```
### Импортируем tablespace, выполняем SQL
```bash
ALTER TABLE table IMPORT TABLESPACE;
```

Если получаете ошибку ***"ERROR 1030 (HY000) at line 1: Got error -1 from storage engine"***, то проверьте версию mysql, должны быть 5.6+.
Если нет возможности использовать версию 5.6, то смотрите инструкцию тут: [http://www.chriscalender.com/tag/innodb-error-tablespace-id-in-file/](http://www.chriscalender.com/tag/innodb-error-tablespace-id-in-file/)

Далее повторяем для всех таблиц
