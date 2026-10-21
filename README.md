# Online Voting System - College Assignment
A simple PHP + MySQL online voting system for college elections.

## 📂 Location
`C:\xampp_new\htdocs\VotingSystemCollegeAssign`

## 🗄️ Database
- Name: `online_voting_college_assign`
- Import: `database/online_voting_college_assign.sql`

## 🚀 Setup Instructions

1. **Start XAMPP** — Start Apache and MySQL from `C:\xampp_new\xampp-control.exe`.
2. **Import the database:**
   - Go to `http://localhost/phpmyadmin`
   - Click **Import** → choose `database/online_voting_college_assign.sql` → **Go**
3. **Place the project** in `C:\xampp_new\htdocs\VotingSystemCollegeAssign`
4. **Open in browser:** `http://localhost/VotingSystemCollegeAssign/`

## 🔑 Creating an Admin User

1. Register a normal account through `register.php` (e.g. username `admin1`).
2. In phpMyAdmin, run:
   ```sql
   UPDATE users SET role = 'admin' WHERE username = 'admin1';

   Login via admin_login.php with that username and password.

🔄 Main Workflow
Register → Login → View Candidates → Vote Once → View Results

✨ Features
Voter registration with hashed passwords

One vote per election (enforced by DB unique constraint + transaction)

Admin dashboard with stats

Manage elections and candidates

Results hidden until election ends

CSRF protection on all forms

Prepared statements (PDO) for SQL safety

Responsive design

🧪 Testing Checklist
✅ Register + login as voter

✅ Duplicate username/email rejected

✅ Vote once — second vote blocked

✅ Results hidden while election is open

✅ Admin can add/edit elections and candidates

✅ Logged-out users redirected to login

📝 Notes
This is an academic simulation. It is not a real election system.

text

---

## ✅ Testing Guide

### 1. Import Database
Open `http://localhost/phpmyadmin` → Import → select `online_voting_college_assign.sql` → Go.

### 2. Test Homepage
Visit `http://localhost/VotingSystemCollegeAssign/` — you should see the sample election.

### 3. Test Voter Registration
Go to `register.php`, create a user (e.g. `student1` / `student1@test.com` / `test123`).

### 4. Make an Admin
In phpMyAdmin → SQL tab:
```sql
USE online_voting_college_assign;
INSERT INTO users (full_name, username, email, password_hash, role)
VALUES ('Admin User', 'admin', 'admin@test.com', 
'$2y$10$YourHashHere', 'admin');
Easier way: Register a normal user, then run:

sql
UPDATE users SET role='admin' WHERE username='admin';
5. Test Voting Flow
Login as voter → dashboard → Vote Now

Select candidate → Confirm → success message

Try voting again → blocked

6. Test Results
While election is open → results hidden

Set election status to ended in manage_elections.php → results visible

7. Test Admin
Login via admin_login.php

Add candidates, create elections, view voters, view stats

🔧 Quick Fixes
Problem	Solution
DB connection error	Check config/db.php — DB name must be online_voting_college_assign
Can't login as admin	Make sure role = 'admin' in users table
Election not showing	Check start_time / end_time in phpMyAdmin
Vote button disabled	Ensure election status is open or current time is within range

Project Structure: 
C:\xampp_new\htdocs\VotingSystemCollegeAssign\ 
│ 
├── index.php                  # Homepage 
├── register.php               # Voter registration 
├── login.php                
  # Voter login 
├── logout.php              
├── dashboard.php       
   # Logout 
       # 
Voter dashboard 
├── vote.php                   # Voting page 
├── results.php                # Public results 
├── profile.php                # Voter profile 
├── admin_login.php    
        # 
Admin login 
├── admin_dashboard.php        # Admin dashboard 
├── admin_profile.php          # Admin profile 
├── manage_candidates.php      # Admin: candidates 
├── manage_elections.php       # Admin: elections 
├── voters.php                 # Admin: voter list 
├── export_results.php         # Admin: CSV export 
├── health.php                 # System health check 
│ 
├── config/ 
│   └── db.php              
│ 
├── includes/ 
│   
├── functions.php          
│   
   # PDO connection + auto-status 
# Helpers (e, csrf_token, flash) 
├── auth.php               # is_logged_in, require_voter, require_admin 
│   
├── header.php             # Shared header + nav 
│   └── footer.php             # Shared footer 
│ 
├── assets/ 
│   
├── css/style.css          # All styles 
│   
├── js/script.js         
│   └── images/parties/      
│ 
└── database/ 
  # Client JS 
  # Party logos 
└── online_voting_college_assign.sql
