# Match Making : Business relationship application

Match Making is an application to organize business speed dating.

![MatchMaking](201811-matchmaking.gif)

### Prerequisites

You need [Composer](https://getcomposer.org/download/) and [Yarn](https://yarnpkg.com/fr/docs/install#debian-stable) in your computer.

### Installing

* Clone the project `git clone https://github.com/Amandine1345/201901-matchmaking-application.git`
* Open the folder, configure the constants in *.env* file 
```
APP_ENV=dev
APP_SECRET=your-secret-message
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
MAILER_URL=null://localhost
```
* Run commands
```
composer install
yarn install
yarn encore dev
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
* Create an user admin
`php bin/console app:create-user <email> <password> <society>`
* Run server `php bin/console server:run`

### Build With
* HTML 5
* CSS 3
* Bootstrap 4
* JS / jQuery
* PHP 7
* Symfony 4
* Twig
* Doctrine

### Versioning

* Git

### Project management

* Scrum / Agile
* Git Workflow
 
### Contributors

* Mathieu Hoarau - [Mathelchrist](https://github.com/Mathelchrist)
* Teddy Milon - [milonte](https://github.com/milonte)
* Vincent Reinoso - [vigi3](https://github.com/vigi3)
* Eric Rousselet - [eric-rousselet](https://github.com/eric-rousselet)
* Amandine Turpin - [Amandine1345](https://github.com/Amandine1345)

###### Duration of the project : 8 weeks