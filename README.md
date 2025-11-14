<p align="center">
   <img src="./banner.svg">
</p>

**TEWA** (Template E-commerce Web Application) - это шаблон SPA онлайн-магазина/маркетплейса, созданный для быстрого запуска интернет-бизнеса без долгих настроек, как в традиционных CMS.

> *Проект может служить основой для реальных коммерческих решений, но автор не несёт ответственность за возможные риски*

## Основные возможности
- Просмотр товаров, их подробностей и отзывов
- Поиск и фильтрация товаров
- Регистрация и авторизация пользователей
- Функциональные списки избранного
- Функциональная корзина
- *Полный список функций приведён в [этом файле](./functions.md), следите за обновлениями в [CHANGELOG](./CHANGELOG.md)*

<video src="./video.mp4" controls>
  Ваш браузер не поддерживает видео тег.
</video>

## Технологический стек
### Frontend
- **Vue.js 3** - *The Progressive JavaScript Framework. An approachable, performant and versatile framework for building web user interfaces.*
- **Vue Router** - *Vue Router. Expressive, configurable and convenient routing for Vue.js.*
- **Pinia** - *The intuitive store for Vue.js.*
- **Element Plus** - *A Vue 3 based component library for designers and developers.*

### Backend
- **Laravel 12** - *Laravel is a web application PHP framework with expressive, elegant syntax, a robust ecosystem.*
- **PHP 8.4** - *A popular general-purpose scripting language that is especially suited to web development.*

### Инструменты разработки
- **phpMyAdmin** - *Free software tool written in PHP, intended to handle the administration of MySQL over the Web.*
- **VSCodium** - *Free/Libre Open Source Software Binaries of VS Code.*
- **Drawio** - *Security-first diagramming for teams.*
- **Bruno** - *Re-Inventing the API Client. Git-integrated, fully offline, and open-source API client.*

**Полный перечень зависимостей и их лицензий приведён в [этом файле](./ATTRIBUTIONS.md)**

## Установка и запуск
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

## Документация
Пока документация крайне бедная, но я обязательно сделаю её широкой и читабельной позже.  
В папке `docs/` содержатся технические диаграммы:
- `TEWA - 1 step.drawio` - текущая архитектура и планы развития
- `term of service.md` - шаблон правил использования платформы

## Лицензия
Этот проект лицензирован в соответствии с GNU Affero General Public License v3.0 - см. файл [LICENSE](./LICENSE) для получения подробной информации.

## Участие в разработке
Проект открыт ко внесению вклада! Ознакомьтесь, пожалуйста, с его правилами в [этом файле](./CONTRIBUTING.md).