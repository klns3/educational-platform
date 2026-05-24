# Deployment

Краткая памятка для отправки новой версии на GitHub и обновления Ubuntu-сервера.

## 1. Отправка проекта на GitHub

Проверь изменения:

```bash
git status
```

Добавь файлы в коммит:

```bash
git add -A
git commit -m "Update educational platform"
git push origin main
```

Если Git попросит авторизацию, используй GitHub Personal Access Token вместо пароля.

## 2. Обновление сервера

На сервере проект сейчас расположен в `/var/www/lms`.

```bash
cd /var/www/lms
cp .env .env.backup
php artisan down
git pull origin main
```

Обнови зависимости и собери фронтенд:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Подготовь Python-модуль аналитики:

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python ai/train.py
```

В `.env` на сервере укажи Python из виртуального окружения:

```env
PYTHON_PATH=/var/www/lms/.venv/bin/python
```

Примени миграции и очисти кеши:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

Проверь AI-модуль:

```bash
php artisan ai:analytics-check
```

## 3. Большая демо-база для проверки преподавателем

Seeder создает отдельные демо-данные и не удаляет реальные записи. Он пересоздает
только пользователей `demo.*@example.com`, группы `DEMO-*`, курсы `[DEMO] *` и
связанные с ними тестовые данные.

Запуск на сервере:

```bash
cd /var/www/lms
php artisan db:seed --class=LargeDemoSeeder --force
php artisan optimize:clear
```

Демо-входы:

```text
Администратор: demo.admin@example.com / password
Преподаватель: demo.teacher01@example.com / password
Студент: demo.student001@example.com / password
```

Что создается:

- 1 администратор, 4 преподавателя, 160 студентов;
- 8 учебных групп;
- 12 курсов;
- 72 материала;
- 48 тестов с вопросами и вариантами ответов;
- история попыток, ответов, обращений, сообщений, уведомлений, расписания и логов.

Важно: не загружай на GitHub `.env`, `vendor`, `node_modules`, `storage` с пользовательскими файлами, локальные базы и временные output-файлы.
