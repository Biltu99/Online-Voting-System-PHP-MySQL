# Viva Voce Preparation — Expected Questions & Answers

## Q1. What is the objective of your project?
To build an online voting system using PHP and MySQL where registered users can
vote for candidates, each voter can vote only once, and results are shown after
the election ends.

## Q2. Why did you use PHP and MySQL?
PHP is a widely-used server-side scripting language that integrates easily with
MySQL and Apache via XAMPP. It is ideal for a beginner-level college project
because it is easy to learn, runs without complex setup, and supports secure
patterns like prepared statements and password hashing.

## Q3. How do you prevent a user from voting twice?
Two layers of protection:
1. **Unique database constraint** on `(election_id, user_id)` in the `votes` table.
2. **Server-side check inside a transaction** — before inserting, we SELECT with
   `FOR UPDATE` to lock the row and reject duplicates. The INSERT would also fail
   on the unique constraint if two requests arrived simultaneously.

## Q4. Why do you use `password_hash()` instead of MD5?
MD5 is outdated and fast, making it vulnerable to brute force and rainbow-table
attacks. `password_hash()` uses bcrypt, which is slow by design, adds a random
salt per password, and is the recommended way to store passwords in PHP.

## Q5. What is CSRF and how did you protect against it?
CSRF (Cross-Site Request Forgery) is when a malicious site silently submits a
form on behalf of a logged-in user. We generate a random token in the session
and include it as a hidden field in every POST form. On submission, we compare
the two using `hash_equals()`. If they don't match, we reject the request.

## Q6. What is SQL Injection and how did you prevent it?
SQL Injection is when a user inserts SQL code through form inputs to manipulate
queries. We use **PDO prepared statements**, which send SQL and data separately,
so user input is always treated as data, never as SQL code.

## Q7. What is the role of the `votes` table's UNIQUE constraint?
It ensures the database itself refuses a second row with the same
`(election_id, user_id)` combination. Even if a bug or race condition bypassed
the PHP check, the constraint would still block duplicate votes.

## Q8. How do you determine the election status?
In `functions.php`, `get_election_status()` compares the current server time
with `start_time` and `end_time`. If status is manually set to `ended` or now is
after end_time, it's ended. If now is before start_time, it's not_started.
Otherwise it's open.

## Q9. Why do you hide results before the election ends?
To prevent voters from being influenced by partial results. This matches real
election practices and is enforced server-side, not just hidden with CSS.

## Q10. What is the difference between `require_voter()` and `require_admin()`?
Both check that a user is logged in, but `require_voter()` additionally checks
that `$_SESSION['role'] === 'voter'` and redirects to the voter login page if
not. `require_admin()` does the same for role `admin`, redirecting to admin
login. This prevents role-based access violations.

## Q11. What is a database transaction and why do you use one in `vote.php`?
A transaction groups multiple SQL statements so they either all succeed or all
fail together. In voting, we need to (a) check for an existing vote and (b)
insert a new vote. If two requests run at the same moment, the FOR UPDATE lock
prevents both from succeeding — this is how we prevent double-voting under
concurrency.

## Q12. How is the winner identified?
In `results.php`, after the election ends, we GROUP BY candidate and COUNT
votes. The candidate with the highest count is the winner. If two or more
candidates have the same maximum count, we display a tie notice.

## Q13. Why can't a user change their username?
The username is used as the primary login identifier and is referenced in
sessions and logs. Locking it after registration avoids confusion and duplicate
identity issues. Users can still change their full name and email.

## Q14. What is XSS and how did you prevent it?
XSS (Cross-Site Scripting) is when attacker-supplied HTML or JavaScript runs in
another user's browser. We escape every dynamic output using the helper `e()`,
which wraps `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

## Q15. What are sessions and how are they used here?
Sessions store server-side state across multiple requests, identified by a
cookie. After successful login, we store `user_id`, `username`, `full_name`,
and `role` in `$_SESSION`. Protected pages check these before allowing access.

## Q16. How would you add a new election?
Login as admin → Manage Elections → fill in title, description, start_time,
end_time → click Create Election. It appears immediately on the homepage.

## Q17. How would you end an election manually?
Admin Dashboard → Manage Elections → change the status dropdown to "Ended" →
Update. The results then become visible to everyone.

## Q18. What happens if the database connection fails?
`config/db.php` catches `PDOException` and stops execution with a clear error
message. In production we would log this and show a friendly message instead,
but for a college assignment it's fine to display the technical error.

## Q19. Why did you use PDO instead of MySQLi?
PDO supports named and positional placeholders, multiple database drivers
(MySQL, PostgreSQL, SQLite), and has a cleaner exception-based error model.
MySQLi is procedural or OO but is limited to MySQL only.

## Q20. What are the limitations of your system?
- No email verification
- No CAPTCHA on registration
- Vote records are linked to user IDs (not fully anonymous)
- No photo uploads for candidates
- No real-time result streaming during an open election
These are acceptable for an academic simulation and can be added later.

## Q21. What did you learn from this project?
- Building a multi-page PHP application with includes
- Designing normalized relational schema with foreign keys
- Securing forms with CSRF tokens and prepared statements
- Using transactions to handle concurrency
- Structuring code for a beginner-level project that is still easy to explain

## Q22. Why are you not using Bootstrap?
The requirement stated to write CSS directly. This keeps the codebase
framework-free, easy to understand, and every style is written by hand for
clarity during the viva.

## Q23. What is the purpose of the `assets/js/script.js` file?
It handles the client-side interaction on the voting page: selecting a
candidate card, highlighting it, updating the confirmation text, enabling the
submit button, and asking for a final `confirm()` before submission. Server-side
checks still enforce the real rules, so the JS is only for usability.

## Q24. Why do you have both client-side and server-side validation?
Client-side validation improves user experience (instant feedback) but can be
bypassed. Server-side validation is the real security layer and cannot be
bypassed by the user. Both are needed.

## Q25. How would you extend this to multiple contests in one election?
The schema already supports multiple elections via `elections.id`. To support
multiple contests (e.g. President + Secretary) within a single election event,
we would add a `contests` table between `elections` and `candidates`, and add
`contest_id` to `votes`. The one-vote-per-contest rule would become a unique
constraint on `(contest_id, user_id)`.
Step 11: Screenshots Guide
File: SCREENSHOTS_GUIDE.md
Location: C:\xampp_new\htdocs\VotingSystemCollegeAssign\SCREENSHOTS_GUIDE.md

markdown
# Screenshots Guide for Report

Create a folder `screenshots/` inside the project and save your screenshots
there with these exact names. Then embed them in your Word / PDF report.

## Recommended Screenshot List

| File Name                  | Page / View                                | What to Show                                  |
|----------------------------|--------------------------------------------|-----------------------------------------------|
| 01_homepage.png            | index.php                                  | Hero, notice, election list                   |
| 02_register.png            | register.php                               | Filled registration form                      |
| 03_login.png               | login.php                                  | Voter login form                              |
| 04_dashboard.png           | dashboard.php                              | Welcome + election with Vote Now button       |
| 05_voting.png              | vote.php                                   | Candidate cards with one selected (highlight) |
| 06_vote_confirm.png        | vote.php (after clicking Select)           | Confirm bar showing selection                 |
| 07_vote_success.png        | dashboard.php after voting                 | Green "already voted" success message         |
| 08_duplicate_block.png     | dashboard.php second attempt               | Blocked / already voted notice                |
| 09_results_locked.png      | results.php (before end)                   | Locked message                                |
| 10_results_final.png       | results.php (after end)                    | Winner + vote counts                          |
| 11_admin_login.png         | admin_login.php                            | Admin login form                              |
| 12_admin_dashboard.png     | admin_dashboard.php                        | Statistics cards + election overview table    |
| 13_manage_candidates.png   | manage_candidates.php                      | Add form + candidates table                   |
| 14_manage_elections.png    | manage_elections.php                       | Create form + status dropdown                 |
| 15_voters_list.png         | voters.php                                 | Table of registered voters                    |
| 16_phpmyadmin.png          | phpMyAdmin                                 | Database `online_voting_college_assign` tables|
| 17_er_diagram.png          | (drawn externally)                         | ER diagram of 4 tables                        |
| 18_flowchart.png           | (drawn externally)                         | Voting workflow flowchart                     |

## Tips

- Use **Windows + Shift + S** for snip screenshots.
- Resize browser to about 1280×800 for desktop shots.
- Optionally resize to 375×700 for one mobile shot of the homepage (bonus).
- Crop out XAMPP control panel and taskbar if possible.

## Diagram Tools (free)

- **draw.io** — https://app.diagrams.net — for ER diagram and flowchart
- **Lucidchart** — free tier
- **MS Paint / Paint.NET** — simple fallback

## ER Diagram — Simple Rectangle Form

Draw 4 rectangles, one per table, each listing its columns (mark PK and FK),
then connect with crow's-foot lines:

- elections (1) ——< candidates (N)
- elections (1) ——< votes (N)
- users (1) ——< votes (N)
- candidates (1) ——< votes (N)

## Voting Flowchart
[Start] → [Register] → [Login] → [Dashboard]
↓
[Election Open?]
/
No Yes
↓ ↓
[Wait / View] [View Candidates]
↓
[Select Candidate]
↓
[Confirm Vote]
↓
[Already Voted in DB?]
/
Yes No
↓ ↓
[Reject: Duplicate] [Save Vote]
↓
[Success Message]
↓
[View Results?]
/
No Yes
↓ ↓
[End] [After End: Show]

text
Step 12: Quick Start Sheet
File: QUICK_START.md
Location: C:\xampp_new\htdocs\VotingSystemCollegeAssign\QUICK_START.md

markdown
# ⚡ Quick Start — 5 Minutes

## 1. Start XAMPP
Open `C:\xampp_new\xampp-control.exe`
Click **Start** next to **Apache** and **MySQL**.

## 2. Import Database
- Browser: `http://localhost/phpmyadmin`
- Click **Import**
- Choose: `database/online_voting_college_assign.sql`
- Click **Go**

## 3. Open Project
Browser: `http://localhost/VotingSystemCollegeAssign/`

## 4. Register & Test Vote
1. Click **Register as Voter**
2. Fill: full name, username `student1`, email, password
3. Login with your username/password
4. On dashboard click **Vote Now**
5. Select a candidate → Confirm → Success ✅

## 5. Create Admin Account
Easiest method:
1. Register a second user via `register.php` (e.g. username `admin`)
2. Open phpMyAdmin → `online_voting_college_assign` → `users` table
3. Find the row → click **Edit** → set `role` to `admin` → **Go**
4. Logout → go to `admin_login.php` → login with `admin` credentials

## 6. View Results
- While election is open: results are hidden.
- To end election:
  1. Login as admin
  2. `Manage Elections` → change status dropdown to **Ended** → Update
  3. Visit `results.php` → winner displayed 🏆

## Common Issues

| Problem                        | Fix                                                    |
|--------------------------------|--------------------------------------------------------|
| "Database connection failed"   | Check MySQL is started in XAMPP control panel          |
| "Unknown database"             | Import the SQL file (step 2)                           |
| White blank page               | Enable PHP errors: add `ini_set('display_errors',1);`  |
| Admin login fails              | Ensure `role='admin'` for that user in `users` table   |
| Vote button missing            | Election must be `open` and now must be within window  |
| Results hidden                 | Election must be ended                                 |

## File Permissions (Windows)
No special permission needed on Windows. XAMPP runs as your user.

## Reset Everything
Drop database in phpMyAdmin:
```sql
DROP DATABASE online_voting_college_assign;
Then re-import the SQL file.

text

---

## 📋 Complete File Checklist

After these steps, your project folder should contain:
C:\xampp_new\htdocs\VotingSystemCollegeAssign
│
├── 📄 index.php ✅
├── 📄 register.php ✅
├── 📄 login.php ✅
├── 📄 logout.php ✅
├── 📄 dashboard.php ✅
├── 📄 vote.php ✅
├── 📄 results.php ✅
├── 📄 profile.php ✅
├── 📄 admin_login.php ✅
├── 📄 admin_dashboard.php ✅
├── 📄 manage_candidates.php ✅
├── 📄 manage_elections.php ✅
├── 📄 voters.php ✅
│
├── 📁 config/
│ └── 📄 db.php ✅
│
├── 📁 includes/
│ ├── 📄 auth.php ✅
│ ├── 📄 functions.php ✅
│ ├── 📄 header.php ✅
│ └── 📄 footer.php ✅
│
├── 📁 assets/
│ ├── 📁 css/
│ │ └── 📄 style.css ✅
│ ├── 📁 js/
│ │ └── 📄 script.js ✅
│ └── 📁 images/ (optional — for symbols/logos)
│
├── 📁 database/
│ └── 📄 online_voting_college_assign.sql ✅
│
├── 📄 README.md ✅
├── 📄 REPORT.md ✅
├── 📄 VIVA_QA.md ✅
├── 📄 SCREENSHOTS_GUIDE.md ✅
└── 📄 QUICK_START.md ✅

text

---

## ✅ Final Submission Checklist

Before you submit, verify:

- [ ] Database imported successfully (`online_voting_college_assign`)
- [ ] Homepage loads without errors
- [ ] Voter registration works
- [ ] Login / logout works
- [ ] Voting works once, duplicate blocked
- [ ] Admin account created and admin pages accessible
- [ ] Election status can be changed to `ended`
- [ ] Results show after ending the election
- [ ] Profile update and password change work
- [ ] All 18+ screenshots captured
- [ ] REPORT.md exported to PDF or Word
- [ ] Project zipped for submission (or handed in on USB)

---

## 🎯 What to Submit

Package these as your college assignment submission:

1. **Source Code** — the entire `VotingSystemCollegeAssign` folder
2. **Database Export** — `online_voting_college_assign.sql`
3. **Report** — `REPORT.md` (or converted to `.docx` / `.pdf`)
4. **Screenshots** — inside a `screenshots/` folder
5. **ER Diagram** — image file
6. **Flowchart** — image file

---

## 🚀 You're Done!

Your complete **Online Voting System** assignment is ready:

- ✅ 13 PHP pages
- ✅ 4 database tables with proper constraints
- ✅ Full authentication + role-based access
- ✅ Duplicate vote prevention (server + DB level)
- ✅ CSRF protection + XSS prevention + SQL injection prevention
- ✅ Admin dashboard with statistics
- ✅ Candidate & election management
- ✅ Public results page with tie handling
- ✅ Responsive CSS design
- ✅ Full documentation (README, REPORT, VIVA_QA, QUICK_START, SCREENSHOTS_GUIDE)

If you want me to generate **sample screenshots as ASCII mockups**, **convert the report to Word format content**, or **add a bonus feature** (e.g., a simple results chart using pure CSS bars), just say the word.