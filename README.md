<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Parcelmind Documentation
## Introduction
Parcelmind is a full-featured, Laravel-based e-commerce system that empowers users to sell, manage, and track their products through a smart and easy-to-use control panel.
This platform provides a complete solution for businesses or individuals who want to:
- Create and manage product orders
- Track order status in real-time
- Organize and monitor their sales activity

Whether you're running an e-commerce business or managing orders for clients, Parcelmind simplifies the process and offers a smooth user experience through an easy-to-use panel.

Parcelmind empowers users to grow their business with minimal effort by handling the core aspects of order creation, tracking, and sales reporting — all from one place.
## Installation Guide

This guide will help you set up and run the Parcelmind Laravel-based e-commerce system on your local machine or server.
##  Requirements

### Before starting, make sure you have the following installed:
- XAMPP (PHP >= 8.1, MySQL)
- Composer (for Laravel dependencies)
- Git 
- A web server (Apache) or Laravel's built-in server
###  Git Repository Clone Guide

This section explains how to clone the Parcelmind repository from GitHub to your local machine.

###  Steps to Clone the Repository

### 🔐 Git Access Provided by the Company

Access to the Git repository is managed and provided by the company.

### ✅ To Get Access:

1. Share your GitHub username with the team lead or manager.
2. Wait until you're added as a collaborator or added to the organization/team.
3. Once added, you will receive an email invitation from GitHub.

---
### ✅ Once access is confirmed, continue with cloning the repo 
---

###  Clone the Repository

Run the following command in the terminal/command prompt to clone the Parcelmind repository:

- git clone https://github.com/Parcelmind/pos.git

- Navigate to the Project Folder
### Verify Cloning Success

#### Run the following command to verify Git status:   git status

If it shows:

On branch main
nothing to commit, working tree clean
It means your repo is successfully cloned.

---

###  You're Done!

The Parcelmind project is now available on your local system. You can open it in your editor and continue working or running the project..

### Update Composer Dependencies

After cloning the Laravel project, make sure to install or update all necessary PHP dependencies using Composer.

### ✅ Run the Following Command:

- composer update

> 📦 This command will update all the required packages defined in composer.json and generate the vendor/ folder.
### Database Setup & Migration

Follow these steps to configure your database, run migrations, and seed data.

---

### ✅ Step 1: Create Database in phpMyAdmin

1. Open your browser and go to:  
   [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on Databases tab.
3. Create a new database.  
   Example name: Parcelmind_db

   ---
   ### ✅  Configure .env File

Open the .env file in your project root and update the database settings:

- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=Parcelmind_db
- DB_USERNAME=root
- DB_PASSWORD=

 If your MySQL has a password, add it to DB_PASSWORD.

   ### 🧱 Create Tables Using Laravel Migrations

Parcelmind uses Laravel's migration system to create all database tables.

### ✅ Step: Run Migrations

After setting up your database configuration in .env, run the following command from the project root:

This command will automatically:

- Create all necessary tables in your connected MySQL database
- Ensure proper relationships and structure as defined in the migration files

> 📂 Migration files are located in:  
> database/migrations/

---

### 🔄 If You Want to Reset and Re-run Migrations:

To refresh all tables from scratch:
- php artisan migrate:fresh

---

✅ That's it! Your tables are now created through Laravel — no need to create them manually from phpMyAdmin.

### ✅ Run Specific Seeders (For Initial Project Data)

Run the following commands one by one to seed important default data into your database:
- php artisan db:seed --class=ChannelsTableSeeder
- php artisan db:seed --class=CouriersTableSeeder
- php artisan db:seed --class=CountriesTableSeeder
- php artisan db:seed --class=StatesTableSeeder
- php artisan db:seed --class=LeadStatusesTableSeeder
- php artisan db:seed --class=ShipmentStatusesSeeder
- php artisan db:seed --class=OrderStatusesSeeder
- php artisan db:seed --class=PlanSeeder
- php artisan db:seed --class=PlanDurationSeeder
- php artisan db:seed --class=NotificationTemplateSeeder
 
 ---

### ✅ Your database now contains all required  data to start using the Parcelmind system.

Next step: Run the application using php artisan serve

###   Create Your Own Git Branch (After Cloning)

After successfully cloning the repository, it’s a good practice to create your own branch before making any changes.

### ✅ Run the Following Commands:

1. Check you are on the main branch:
- git branch
2. Create and switch to a new branch:
- git checkout -b your-branch-name
3. Verify you’re now on the new branch:

---

✅ Now you're working safely on your own branch.  
Once you're done, you can commit and push your work without affecting the main branch.

### Step: Commit and Push Your Changes to Git

After you’ve made changes in your project (e.g., code, migrations, views, documentation), follow these steps to commit and push them to your Git branch.

---

### ✅ Step 1: Stage the Changed Files

This command adds all modified files to the staging area:
- git add .
> 📌 You can also add a specific file instead:
> 
> git add path/to/file.php
> 

---

### ✅ Step 2: Commit the Changes

Commit your changes with a meaningful message:

- git commit -m "Your commit message here"

### ✅ Step 3: Push to Your Branch

Push your committed changes to your branch on GitHub:
- git push origin your-branch-name
> 🔄 Replace your-branch-name with the actual name of your branch.

---

🎉 Done!  
Your changes are now saved and pushed to your GitHub repository under your branch.

You can now create a pull request (PR) if you're collaborating with a team or want your changes to be merged into the main branch.

