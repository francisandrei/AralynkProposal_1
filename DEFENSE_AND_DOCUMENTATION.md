# Aralynk: Defense Guide and Capstone Documentation Draft

Edit the wording to match your school's template and your adviser's comments.

---

## PART A. Defense guide: Feature, Purpose, How it works, Why we need it

| Feature | Purpose | How it works | Why we need it |
|---|---|---|---|
| Registration and login | Give each user a safe account | Passwords are hashed with `password_hash()`; login uses `password_verify()`; a session stores the user id and role | Protects accounts and separates what each role may see |
| Business approval | Keep fake or unsafe businesses out | New business accounts have status `pending`; the admin approves them | Students are meeting employers, so trust matters |
| Student profile | Describe the student fairly | Course, year, skills (from a fixed list), experience, weekly availability | Gives the matching algorithm and businesses real information |
| Availability | Show when a student can work | One time slot per weekday in `student_availability`, plus optional one-off dates in `student_day_availability` ("Available for this day") | Student schedules are limited by classes, and some days are free only once |
| Job posting | Let businesses describe short-term work | `jobs` table plus `job_skills`, `job_courses`, `job_days` | Supports 1-Day, Several-Day, Recurring and Part-Time jobs |
| Job search and filters | Help students find jobs fast | A SQL query built with safe placeholders from keyword, category, type, skill, location, weekday, date | Students should not scroll through irrelevant jobs |
| Application system | Record who applied | `applications` row with a UNIQUE key on (student, job) | Prevents duplicate applications and tracks status |
| Job matching | Help students find relevant jobs and show businesses why | Three weighted components (see Part B) | Makes recommendations explainable and fair |
| Messaging | Let both sides ask questions | Rows in `messages` with read/unread flag; inbox style | Simple, no real-time server needed |
| Notifications | Keep users updated | Rows in `notifications` created on events | Users know when something changes |
| Admin dashboard and reports | Monitor the platform | Count queries and Chart.js charts | The municipality/facilitator can see results and manage records |
| Ratings (both ways) | Build trust on both sides | After a job is Completed, the business rates the student and the student rates the business, 1 to 5 stars with an optional comment. One rating per person per job (unique key). Averages are calculated from the `ratings` table | Students and businesses can see who was reliable, and the admin can remove abusive ratings |
| Report a job post | Handle inappropriate posts | Student submits a report; admin hides, resolves or dismisses | Safety of students |

## PART B. The matching algorithm (whiteboard version)

**Total = Skills (40) + Availability (30) + Relevant Background (30) = 100**

1. **Skills** = (required skills the student has / required skills) x 40. No required skills = full points.
2. **Availability** = for each workday of the job: student free for the whole shift = 1, partly = 0.5, not free = 0 (free means the weekly schedule **or** a specific date the student marked "available for this day"). Average x 30.
3. **Relevant Background** = 30 if the student has a **related course OR related experience** (experience in the same job category). Otherwise 0. Both together do not earn extra.

Labels: **Strong** 80% or more of a component, **Partial** 40% to 79%, **Low** below 40%.
Tiers: **Good match** 70+, **Possible match** 40 to 69, **Worth exploring** below 40.

**Why not use the first example weights?** The example had a separate "Job Requirement Match" which overlaps with skills, course and experience (double counting), and it scored course and experience separately. That would let a student with more experience outscore a student with a more related course, which is what our adviser warned about.

**Answer to the adviser's concern (Student A vs Student B):**
- Student A (more experience, less related course) and Student B (related course, less experience) both receive full Background points, because the algorithm uses "course OR experience".
- Businesses see Skills, Availability, Course and Experience as separate labels, not a single rank.
- The applicants list is sorted by date applied by default, not by score.
- No one is auto-rejected. Every student can apply to every open job.
- Scores are recalculated for display only and never change an application status.
- The final decision is the business's. We state this honestly as a limitation: a business could still favor higher scores, so the system reduces the risk but cannot remove it.

**Possible panel questions**
- *Why not machine learning?* The data is small and local, and a transparent rule-based score can be explained to students and businesses. ML would be a black box.
- *Why these weights?* Skills are the most direct evidence of fit (40). Availability decides whether the student can actually work (30). Background is shared by course or experience (30). The weights are constants in `config/constants.php` and can be adjusted after testing with users.
- *What if a job lists no skills?* Everyone gets full skills credit so no one is penalized for something the business did not specify.

## PART C. Database design (ERD explanation)

20 domain tables in 3NF (the users table plus 19 more, including `ratings`), plus Laravel's own system tables (sessions, cache, jobs, migrations). Main relationships:

- `users` holds login data; each user has exactly one `student_profiles` or `business_profiles` row (1:1).
- `courses` 1:N `student_profiles`. `categories` 1:N `jobs`. `business_profiles` 1:N `jobs`.
- Many-to-many relationships use link tables: `student_skills` (students and skills), `job_skills` (jobs and skills), `job_courses` (jobs and preferred courses).
- `student_availability`, `student_day_availability` and `student_experiences` belong to a student (1:N). `job_days` belongs to a job (1:N).
- `applications` links a student and a job (N:M with its own data). `UNIQUE(student_id, job_id)` prevents duplicates.
- `messages` and `notifications` reference `users`. `job_reports` references a job and a reporter. `activity_logs` records actions.
- Skills, courses and categories are separate lookup tables so that "PHP", "php" and "Php" are one value and matching stays reliable.

## PART D. Security summary

| Threat | Measure in the code |
|---|---|
| Plain-text passwords | `password_hash()` / `password_verify()` |
| SQL injection | PDO prepared statements everywhere (`getRows`, `getRow`, `getValue`, `runQuery` in `includes/functions.php`) |
| XSS | Every output goes through `e()` (`htmlspecialchars`) |
| CSRF | Token in session, hidden field in every POST form, `checkCsrf()` on every POST |
| Session fixation | `session_regenerate_id(true)` after login; logout destroys the session and cookie |
| Unauthorized pages | `requireRole()` at the top of each protected page |
| Other users' data | Ownership is part of each query (for example `WHERE id=? AND business_id=?`) |
| Brute force | 5 failed logins lock the account for 5 minutes |
| Inactive accounts | Role check re-reads the account status on every request |
| Privacy | Student contact details are shown to a business only after acceptance; consent checkbox at registration (Data Privacy Act of 2012) |

---

## PART E. Capstone documentation draft

### 1. System overview
Aralynk is a web-based system that connects BASC college students with local businesses in San Ildefonso, Bulacan for short-term and part-time work. Students create profiles with skills, course and availability. Businesses post jobs. The system recommends relevant jobs with clear reasons and lets students and businesses communicate. An administrator manages accounts and monitors records.

### 2. Background of the study (starter)
Many college students need income and experience but have limited time because of classes. Local businesses often need temporary help but have no easy way to reach students. Job searching is usually done through word of mouth or social media posts, which is unorganized and hard to track. Aralynk provides a local platform focused on short-term and part-time work, so students can find schedule-friendly jobs and businesses can find nearby helpers.

### 3. Objectives
**General:** To develop a local job matching system that connects BASC students with businesses in San Ildefonso, Bulacan.
**Specific:**
1. To let students create profiles with skills, course, experience and availability.
2. To let businesses post and manage short-term and part-time jobs.
3. To provide job search and a transparent, fair job matching score.
4. To manage applications, messages and notifications between students and businesses.
5. To give the administrator tools to manage users and jobs and view reports.
6. To protect user data using basic security practices.

### 4. Scope and limitations
**Scope:** BASC college students and businesses in San Ildefonso, Bulacan; short-term, part-time, one-day, several-day and recurring jobs; three roles (student, business, admin); web access through a browser.
**Limitations:**
- Aralynk is not an employment agency and does not guarantee employment. The business makes the hiring decision.
- Payment, contracts and work conditions are outside the system.
- Messaging is inbox style and refreshes on page load; it is not real-time chat.
- The match score is a guide based on listed skills, course, experience and availability; it cannot measure attitude or ability.
- Student status is checked through the student ID number and optional admin review; the system has no link to the school's records.
- Notifications are in-app only (no email or SMS).
- The system covers one municipality only.

### 5. Functional requirements
Registration and login for three roles; student profile with skills, experience and availability; business profile; job posting, editing, closing and deletion; job search and filtering; job details; applications with status tracking and duplicate prevention; match score with factor breakdown; applicant review, accept and reject; messaging; notifications; admin management of students, businesses, jobs, categories and skills; admin reports; reporting of inappropriate job posts.

### 6. Non-functional requirements
- **Security:** hashed passwords, prepared statements, output escaping, CSRF tokens, role-based access.
- **Usability:** simple, responsive layout (Bootstrap) that works on phones.
- **Performance:** pages load quickly for the expected number of local users.
- **Reliability:** transactions for multi-table saves; foreign keys keep data consistent.
- **Maintainability:** plain PHP, shared helper files, matching code in one file.
- **Portability:** runs on XAMPP (Apache, PHP, MySQL).

### 7. User roles
- **Student:** manage profile, search jobs, apply, track applications, message businesses.
- **Business:** manage profile, post and manage jobs, review applicants, accept or reject, message students.
- **Administrator:** approve businesses, manage accounts, jobs, categories and skills, handle reports, view statistics. Does not make hiring decisions.

### 8. Use case diagram description
Actors: Student, Business, Administrator.
- Student: Register, Log in, Manage profile, Set availability, Search jobs, View recommended jobs, Apply, Withdraw application, Track applications, Message business, View notifications, Report job post.
- Business: Register, Log in, Manage profile, Post job, Edit/close/delete job, View applicants, View student profile, Accept/Reject applicant, Message student, Mark job completed.
- Administrator: Log in, Approve business, Activate/deactivate accounts, Manage jobs, Manage categories and skills, Monitor applications, Handle job reports, View reports.
(Draw each as an oval connected to its actor. "Apply" includes "Calculate match"; "Post job" includes "Notify matching students".)

### 9. Data flow diagram description
- **Context diagram:** the Aralynk system exchanges data with three external entities. Students send registration, profile, search and application data and receive job lists, match results, status updates and messages. Businesses send registration, job and decision data and receive applicants and messages. The Administrator sends approvals and management commands and receives reports.
- **Level 1 processes:** 1.0 Account management, 2.0 Profile management, 3.0 Job management, 4.0 Search and matching, 5.0 Application management, 6.0 Messaging and notifications, 7.0 Administration and reports.
- **Data stores:** D1 Users, D2 Profiles (student/business), D3 Jobs, D4 Applications, D5 Messages and Notifications, D6 Lookup lists (courses, categories, skills).

### 10. System architecture
Three layers on one server: **Presentation** (HTML, CSS, Bootstrap, small JavaScript) → **Application** (PHP pages and helper files, sessions, role checks, matching algorithm) → **Data** (MySQL accessed with PDO). The browser sends requests to Apache; PHP reads or writes MySQL and returns HTML.

### 11. Testing and evaluation
Use `docs/TESTING_PLAN.md` for functional testing (authentication, student, business, admin, matching, security). For evaluation, let sample users (students, business owners, an administrator) try the system and answer a short questionnaire using a 5-point scale on functionality, usability, reliability and security, then compute the weighted mean for each criterion. Ask your adviser which standard (for example ISO/IEC 25010 criteria) your school prefers.

### 12. Security
See Part D.

### 13. Future improvements
Email or SMS notifications; real-time chat; profile pictures and document uploads; ratings and feedback after a job; verification through school records; schedule-conflict warnings for accepted jobs; mobile app; reports export (PDF/CSV); expansion to other municipalities; weight tuning based on user feedback.


---

## PART F. Rating system (added in the Laravel version)

**Rules**
- A rating is possible only when the application status is **Completed**.
- Only the two people of that application can rate each other: the business owner and the student.
- Each person rates once per application. The database has a unique key on (application, rater).
- 1 to 5 stars, with an optional comment of up to 500 characters.
- The other person gets a notification. The admin can remove abusive ratings.

**Fairness and limits**
- Ratings are shown next to the match factors but are **not** part of the 100-point match score, so a new student with no ratings is not disadvantaged.
- They never reject or accept anyone. The business still decides.
- Limitation: ratings are opinions, so a person can still give an unfair rating. The admin moderation page reduces this risk.

**How it works (for the defense)**
`RatingController@store` finds who is rating whom, checks the status is Completed, checks the person has not rated yet, saves a row in `ratings`, and notifies the other person. `User::averageRating()` averages the `stars` column.

## PART G. Laravel version notes

| Topic | Plain PHP version | Laravel version |
|---|---|---|
| Passwords | `password_hash()` / `password_verify()` | `Hash::make()` / `Hash::check()` (bcrypt) |
| SQL injection | PDO prepared statements | Eloquent and the query builder use prepared statements |
| XSS | `e()` function | Blade `{{ }}` escapes automatically |
| CSRF | Own token functions | `@csrf` in forms and Laravel's built-in check |
| Page access | `requireRole()` | `RoleMiddleware` on route groups |
| Validation | Own functions | `$request->validate([...])` |
| Login lockout | Counter in `users` | `RateLimiter` (5 attempts, 5 minutes) |
| Database setup | `Aralynk.sql` | Migrations and seeders (or `Aralynk_Laravel.sql`) |
| Structure | Pages with includes | MVC: routes, controllers, models and Blade views |

**Why Laravel?** It organizes the code (MVC), gives built-in security, makes database changes repeatable with migrations, and is widely used, so the project is easier to maintain and extend.

**Updated architecture:** Browser, then Laravel (routes, middleware, controllers, models, views), then MySQL. The match algorithm is a service class (`MatchService`) used by the student and business controllers.
