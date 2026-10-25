# How everything connects (XAMPP, MySQL, Laravel and Aralynk)

Read this once from top to bottom. After that you can explain the whole system in your defense.

## 1. The big picture

```
 Browser  --->  php artisan serve  --->  Laravel project  --->  MySQL (XAMPP)
 (user)         (web server)            (routes, controllers,    (database
                                         models, views)           "aralynk_laravel")
```

| Part | What it is | Where it lives |
|---|---|---|
| **XAMPP** | Gives you **MySQL** (the database) and **phpMyAdmin** (a tool to look at the database). With Laravel you do not need Apache, because `php artisan serve` is the web server. | `C:\xampp` |
| **PHP** | The language. Laravel needs PHP 8.2 or newer. | `C:\xampp\php` |
| **Composer** | Downloads Laravel and its libraries. | Installed on Windows |
| **Laravel project** | Your Aralynk code. | `C:\xampp\htdocs\aralynk-laravel` |
| **Database** | Tables and data. | MySQL database `aralynk_laravel` |

Start XAMPP's **MySQL** (click Start next to MySQL). Then in the project folder run `php artisan serve`.

## 2. Connect Laravel to the database

Laravel reads the file **`.env`** in the project folder. These lines point to the database you created in phpMyAdmin:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aralynk_laravel      <- the database name in phpMyAdmin
DB_USERNAME=root                 <- the XAMPP default
DB_PASSWORD=                     <- empty in XAMPP by default
```

How the connection travels: `.env` -> `config/database.php` (reads the values) -> Laravel connects to MySQL when a page needs data.

**Test the connection:** in the project folder run `php artisan migrate:status`.
- A list of migrations (Yes or No) means it is connected.
- An error means something is wrong. Use the table below.

| Error | Meaning and fix |
|---|---|
| `SQLSTATE[HY000] [1049] Unknown database` | The database does not exist or the name in `.env` is wrong. Create it in phpMyAdmin or fix `DB_DATABASE`. |
| `SQLSTATE[HY000] [2002] ... refused` | MySQL is not running. Start MySQL in the XAMPP Control Panel. |
| `Access denied for user 'root'` | `DB_PASSWORD` does not match. XAMPP's default is empty. |
| `could not find driver` | In `C:\xampp\php\php.ini` remove the `;` in front of `extension=pdo_mysql`, save, and run the command again. |
| You changed `.env` but nothing changes | Run `php artisan config:clear`. |

## 3. Create the tables: choose ONE way

**Way A: migrations (recommended, this is the Laravel way)**
1. Create an empty database `aralynk_laravel` in phpMyAdmin.
2. In the project folder run `php artisan migrate:fresh --seed`.
3. Laravel reads the files in `database/migrations` and builds every table, then the seeders in `database/seeders` fill the 17 programs, 40 categories, 86 skills and the admin account.
4. Optional demo data: `php artisan db:seed --class=DemoSeeder`.

**Way B: import the ready-made SQL file**
1. In phpMyAdmin click **Import**, choose `Aralynk_Laravel.sql`, and import. It creates the database `aralynk_laravel` with all tables and the starter data.
2. Create the admin account: `php artisan db:seed --class=AdminSeeder`.
3. Optional demo data: `php artisan db:seed --class=DemoSeeder`.
4. Do NOT run `migrate:fresh` after importing, because it erases the data.

Both ways give the same database. The SQL file is for Laravel 11 or newer. Check the result in phpMyAdmin: you should see the tables `users`, `student_profiles`, `business_profiles`, `job_posts`, `applications`, `ratings` and the others.

## 4. The five pieces of Laravel (and where each lives)

| Piece | Folder | Job | Example in Aralynk |
|---|---|---|---|
| **Route** | `routes/web.php` | Matches a web address to a controller method | `/student/apply` goes to `Student\ApplicationController@store` |
| **Middleware** | `app/Http/Middleware` | A guard in front of the page | `RoleMiddleware` lets only students into student pages |
| **Controller** | `app/Http/Controllers` | The logic: check, save, decide | `ApplicationController` saves an application |
| **Model** | `app/Models` | One class for each table | `Application` is the `applications` table |
| **View** | `resources/views` | The HTML page (Blade) | `student/applications.blade.php` |

Other parts: **Migration** (builds tables), **Seeder** (fills starter data), **Service** (`app/Services/MatchService.php`, the matching algorithm).

## 5. Follow one request: a student applies for a job

1. On the job page the student presses **Apply**. The form in `resources/views/student/job_details.blade.php` sends a POST to `/student/apply`. `@csrf` adds a secret token that proves the form is real.
2. **Route** (`routes/web.php`): `Route::post('/apply', [StudentApplicationController::class, 'store'])`. It sits in a group protected by `auth` (must be logged in) and `RoleMiddleware:student` (must be a student with an active account).
3. **Controller** `Student/ApplicationController@store`:
   - `$request->validate(...)` checks the typed values.
   - `JobPost::findOrFail(...)` loads the job from the `job_posts` table (Model).
   - It checks the job is open and the student has not applied before.
   - `Application::create(...)` saves a row in the `applications` table.
   - `Notification::send(...)` saves a row in `notifications` for the business and the student.
4. The controller redirects to `/student/applications`. That route runs `ApplicationController@index`, which loads the student's applications and returns the **View** `student/applications.blade.php`.
5. Blade turns the view into HTML, and the browser shows it.

## 6. Follow another: the rating system

1. A business presses **Mark as completed** on a job (or marks one applicant Completed). The application status becomes `Completed`.
2. On the Completed application both people see a rating form (`partials/rating_form.blade.php`).
3. The form posts to `/applications/{id}/rate`, handled by `RatingController@store`:
   - It finds out who is rating whom: the student rates the business, or the business rates the student. Anyone else gets 403.
   - It refuses if the status is not Completed.
   - It refuses a second rating from the same person (also blocked by the unique key in the `ratings` table).
   - It saves 1 to 5 stars and an optional comment, and notifies the other person.
4. Averages come from `User::averageRating()`, which averages the `stars` column. They show on dashboards, profiles and applicant cards.
5. The admin can remove an abusive rating on the **Ratings** admin page.

## 7. The matching algorithm in one picture

`app/Services/MatchService.php` gives each student and job a score out of 100:

```
Skills (40)  +  Availability (30)  +  Background (30)  =  Match %
```
- **Skills**: skills the student has divided by skills the job needs.
- **Availability**: the weekly schedule OR a date marked "Available for this day" must cover the job hours.
- **Background**: full points for a related course OR related experience (the fairness rule).
The score only guides. It never rejects anyone, and the business decides.

## 8. Where to change things (cheat sheet)

| I want to... | Edit |
|---|---|
| Change a page's look | `resources/views/...` and `public/css/style.css` |
| Change the match weights | `app/Services/MatchService.php` (40, 30 and 30) |
| Add a new page | A route in `routes/web.php`, a method in a controller, a Blade view |
| Add a column to a table | A new migration (`php artisan make:migration add_x_to_y_table`), add the name to `$fillable` in the model, add the input to the form and the rule to `validate()` |
| Add a program or category | The admin page (categories), or the seeders in `database/seeders` |
| Change the admin password | Edit `AdminSeeder.php`, then run `php artisan db:seed --class=AdminSeeder` |

## 9. Commands you will use

| Command | What it does |
|---|---|
| `php artisan serve` | Starts the website at http://127.0.0.1:8000 (stop with Ctrl+C) |
| `php artisan migrate:status` | Shows if the database is connected and which migrations ran |
| `php artisan migrate:fresh --seed` | Erases all tables, rebuilds them, and fills starter data |
| `php artisan db:seed --class=DemoSeeder` | Adds sample students, businesses and jobs |
| `php artisan route:list` | Lists every web address and its controller |
| `php artisan tinker` | A small console to try code, for example `App\Models\User::count()` |
| `php artisan config:clear` and `php artisan view:clear` | Clears saved settings and views after a change that does not show |

## 10. Optional: use XAMPP's Apache instead of `php artisan serve`

Not needed for your defense. If you still want a nice address like `http://aralynk.test`:
1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add:
   ```
   <VirtualHost *:80>
       ServerName aralynk.test
       DocumentRoot "C:/xampp/htdocs/aralynk-laravel/public"
       <Directory "C:/xampp/htdocs/aralynk-laravel/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Open Notepad as administrator, open `C:\Windows\System32\drivers\etc\hosts`, and add the line `127.0.0.1 aralynk.test`.
3. Restart Apache in XAMPP and set `APP_URL=http://aralynk.test` in `.env`.

## 11. When something breaks

1. Read the first line of the error. It usually names the file and the line.
2. Laravel keeps a log in `storage/logs/laravel.log`. Open it and look at the bottom.
3. Run `php artisan config:clear`, `php artisan view:clear`, then try again.
4. Check MySQL is running in XAMPP.
5. Send me the exact error text and the file name.
