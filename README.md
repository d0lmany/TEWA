# TEWA
**TEWA** (Template E-commerce Web Application) - это шаблон SPA онлайн-магазина, созданный для быстрого запуска интернет-бизнеса без долгих настроек, как в традиционных CMS.

> 🎓 *Проект разработан как дипломная работа и может служить основой для реальных коммерческих решений, автор не несёт ответственность за возможные риски, но всегда готов выслушать вас*

## 🚀 Основные возможности
- 🛍️ Просмотр каталога товаров
- 🔍 Поиск и фильтрация продукции
- 👤 Регистрация и авторизация пользователей
- 🛒 Корзина покупок
- ❤️ Избранное
- *Полный список функций приведён в файле [Functions](functions.md)*
- *...другие функции в разработке, следите за [CHANGELOG](CHANGELOG.md)!*

![Пример работы приложения](./vid.gif)
## 🛠️ Технологический стек
### Frontend
- **Vue.js 3** - *The Progressive JavaScript Framework. An approachable, performant and versatile framework for building web user interfaces.*
- **Vue Router** - *Vue Router. Expressive, configurable and convenient routing for Vue.js.*

### Backend
- **Laravel 12** - *Laravel is a web application PHP framework with expressive, elegant syntax, a robust ecosystem.*
- **PHP 8.4** - *A popular general-purpose scripting language that is especially suited to web development.*

### Инструменты разработки
- **phpMyAdmin** - *Free software tool written in PHP, intended to handle the administration of MySQL over the Web.*
- **VSCodium** - *Free/Libre Open Source Software Binaries of VS Code.*
- **Drawio** - *Security-first diagramming for teams.*
- **Bruno** - *Re-Inventing the API Client. Git-integrated, fully offline, and open-source API client.*

## 📦 Установка и запуск
### Требования
- PHP 8.4+
- Node.js 16+
- Composer
- MySQL 8+/MariaDB 10+

### Быстрый старт
1. **Клонирование репозитория**
   ```bash
   git clone https://github.com/d0lmany/TEWA
   cd tewa
   ```

2. **Установка зависимостей Frontend**
   ```bash
   cd frontend
   npm install
   ```

3. **Установка зависимостей Backend**
   ```bash
   cd ../backend
   composer install
   ```

4. **Запуск миграций**
   ```bash
   php artisan migrate
   ```

5. **Запуск приложений (режим разработчика)**
   ```bash
   # Frontend (в папке frontend)
   npm run dev
   
   # Backend (в папке backend)
   php artisan serve # ИЛИ composer run dev
   ```

## 📚 Документация
Пока документация крайне бедная, но я обязательно сделаю её широкой и читабельной позже.  
В папке `docs/` содержатся технические диаграммы:
- `TEWA - 1 step.drawio` - текущая архитектура и планы развития

## 🤝 Участие в разработке
Проект открыт к контрибуции! Как это сделать?
1. Сделайте форк
2. Создайте свою ветку для вашего нововведения
3. Сделайте коммит с ёмким но ясным объяснением
4. Отправьте изменения и откройте пулл-реквест
