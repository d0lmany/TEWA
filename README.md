<p align="center">
   <img src="./banner.svg">
</p>

*English* | [Русский](README.ru.md)

**TEWA** (Template E-commerce Web Application) - This is an online store/marketplace SPA template designed to quickly launch an online business without lengthy settings, as in traditional CMS.

> *The project can serve as a basis for real commercial solutions, but the author is not responsible for possible risks*

## Main features
- View products, their details and reviews
- Product search and filtering
- Registration and authorization of users
- Functional favorites lists
- Functional shopping cart
- The full list of functions is given in [this file](./functions.md ) *(russian language)*, stay tuned in [CHANGELOG](./CHANGELOG.md )

<video src="./video.mp4" controls>
    Your browser does not support the video tag.
</video>

## Technology stack
### Frontend
- **Vue.js 3** - *The Progressive JavaScript Framework. An approachable, performant and versatile framework for building web user interfaces*
- **Vue Router** - *Vue Router. Expressive, configurable and convenient routing for Vue.js*
- **Pinia** - *The intuitive store for Vue.js*
- **Element Plus** - *A Vue 3 based component library for designers and developers*
- **TypeScript** - *Strongly typed programming language that builds on JavaScript, giving you better tooling at any scale*

### Backend
- **Laravel 12** - *is a web application PHP framework with expressive, elegant syntax, a robust ecosystem*
- **PHP 8.4** - *A popular general-purpose scripting language that is especially suited to web development*

### Development tools
- **phpMyAdmin** - *Free software tool written in PHP, intended to handle the administration of MySQL over the Web*
- **VSCode** - *The open source AI code editor*
- **Drawio** - *Security-first diagramming for teams*
- **Bruno** - *Re-Inventing the API Client. Git-integrated, fully offline, and open-source API client*

**A complete list of dependencies and their licenses is provided in [this file](./ATTRIBUTIONS.md)**

## Installation and launch
### Requirements
- PHP 8.4+
- Node.js 16+
- Composer
- MySQL 8+/MariaDB 10+

### Quick start
```bash
# 1. Clone the repository and go to it
git clone https://github.com/d0lmany/TEWA
cd tewa

# 2. Install dependencies
# for the frontend
cd frontend 
npm install 
# for the backend
cd ../backend
composer install

# 3. Creating a database
# create a database named 'tewa' in your DBMS (for more fine-tuning, refer to the `./backend/.env` file)
# start migrations
php artisan migrate

# 4. Launch the app in dev mode
cd ..
./run # will start the servers for the backend and frontend, disable using Ctrl+C
```
## Documentation
At the moment, diagrams and examples of legal documents are presented, everything is stored in the 'docs` folder.
- Diagrams (architecture, development plans)
    - `TEWA - 1 step.drawio' - plans for the first stage (brainstorm, UX, useCases, ER)
- Legal documents (examples of codes and rules)
- `term of service.md` - template for the rules for using the platform
    - `personal data policy.md` - personal data processing policy template

## License
This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE](./LICENSE) file for more information.

## Contributing
The project is open for contribution! Please read its rules in [this file](./CONTRIBUTING.md ).