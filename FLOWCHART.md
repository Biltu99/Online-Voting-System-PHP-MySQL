# Voting System Flowcharts

Copy these into draw.io for your report, or hand-draw them on paper.

## 1. Voter Flowchart
┌─────────┐
│ START │
└────┬────┘
▼
┌────────────────┐
│ Visit Home │
│ Page │
└────────┬───────┘
▼
╱──────────╲
╱ Registered╲ NO
╱ already? ╲────────┐
╲ ╱ │
╲───────────╱ ▼
│ YES ┌────────────┐
│ │ Register │
│ │ (validate)│
│ └──────┬─────┘
│ │
▼ ▼
┌─────────────────────┐
│ Login Page │
└──────────┬──────────┘
▼
╱─────────╲
╱ Valid ╲ NO
╱credentials? ╲────► [Show Error]
╲ ╱
╲───────────╱
│ YES
▼
┌─────────────────────┐
│ Voter Dashboard │
└──────────┬──────────┘
▼
╱───────────╲
╱ Election ╲ NOT OPEN
╱ status? ╲──────► [Show Status]
╲ ╱
╲─────────────╱
│ OPEN
▼
┌─────────────────────┐
│ View Candidate │
│ Cards │
└──────────┬──────────┘
▼
┌─────────────────────┐
│ Select Candidate │
└──────────┬──────────┘
▼
┌─────────────────────┐
│ Confirm & Submit │
└──────────┬──────────┘
▼
╱───────────╲
╱ Already ╲ YES
╱ voted? ╲──────► [Reject]
╲ ╱
╲─────────────╱
│ NO
▼
┌─────────────────────┐
│ Save Vote (DB Tx) │
└──────────┬──────────┘
▼
┌─────────────────────┐
│ Success Message │
└──────────┬──────────┘
▼
╱─────────╲
╱ Election ╲ NOT ENDED
╱ ended? ╲──────► [Wait]
╲ ╱
╲───────────╱
│ ENDED
▼
┌─────────────────────┐
│ View Results │
└──────────┬──────────┘
▼
┌───────┐
│ END │
└───────┘

text

## 2. Admin Flowchart
┌─────────┐
│ START │
└────┬────┘
▼
┌──────────────┐
│ Admin Login │
└──────┬───────┘
▼
╱──────────╲
╱ Valid ╲ NO
╱ credentials? ╲────► [Show Error]
╲ ╱
╲────────────╱
│ YES
▼
┌──────────────────┐
│ Admin Dashboard │
│ • Total Voters │
│ • Candidates │
│ • Votes Cast │
│ • Not Voted │
└────────┬─────────┘
▼
┌────────────────────┐
│ Choose Action │
└────────┬───────────┘
│
┌────────────┼────────────┬──────────────┐
▼ ▼ ▼ ▼
┌────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│Manage │ │Manage │ │View │ │Export │
│Elections│ │Candidates│ │Voters │ │Results │
└───┬────┘ └─────┬────┘ └─────┬────┘ └─────┬────┘
│ │ │ │
└────────────┴────────────┴─────────────┘
│
▼
┌───────┐
│ END │
└───────┘

text

## 3. Database ER Diagram
┌──────────────────┐ ┌──────────────────┐
│ users │ │ elections │
├──────────────────┤ ├──────────────────┤
│ 🔑 id (PK) │ │ 🔑 id (PK) │
│ full_name │ │ title │
│ username (U) │ │ description │
│ email (U) │ │ start_time │
│ password_hash │ │ end_time │
│ role │ │ status │
│ created_at │ │ created_at │
└────────┬─────────┘ └────────┬─────────┘
│ │
│ │ 1
│ │
│ 1 ▼ N
│ ┌──────────────────┐
│ │ candidates │
│ ├──────────────────┤
│ │ 🔑 id (PK) │
│ │ 🔗 election_id │
│ │ candidate_name│
│ │ party_name │
│ │ symbol │
│ │ created_at │
│ └────────┬─────────┘
│ │
│ N │ 1
│ │
▼ ▼ N
┌───────────────────────────────────────┐
│ votes │
├───────────────────────────────────────┤
│ 🔑 id (PK) │
│ 🔗 election_id │
│ 🔗 user_id │
│ 🔗 candidate_id │
│ voted_at │
│ ⚠ UNIQUE (election_id, user_id) │
└───────────────────────────────────────┘

Legend: 🔑 Primary Key 🔗 Foreign Key (U) Unique ⚠ Constraint

text

## 4. Authentication Flow
[Browser Request]
│
▼
┌──────────────┐
│ session_start│
└──────┬───────┘
▼
┌──────────────┐ NO
│ $_SESSION │──────────► [Redirect to Login]
│ user_id set?│
└──────┬───────┘
│ YES
▼
┌──────────────┐ NO
│ Role matches │──────────► [Redirect to Correct Login]
│ page? │
└──────┬───────┘
│ YES
▼
┌──────────────┐
│ Grant Access │
└──────────────┘

text
📊 Updated File Structure
text
C:\xampp_new\htdocs\VotingSystemCollegeAssign\
│
├── 📄 index.php                        ✅ (updated: countdown)
├── 📄 register.php                     ✅ (updated: password strength)
├── 📄 login.php                        ✅
├── 📄 logout.php                       ✅
├── 📄 dashboard.php                    ✅ (updated: countdown, my vote)
├── 📄 vote.php                         ✅
├── 📄 results.php                      ✅ (updated: bar chart)
├── 📄 profile.php                      ✅
├── 📄 admin_login.php                  ✅
├── 📄 admin_dashboard.php              ✅ (updated: export button)
├── 📄 manage_candidates.php            ✅
├── 📄 manage_elections.php             ✅
├── 📄 voters.php                       ✅ (updated: search)
├── 📄 export_results.php               ✅ (NEW — CSV)
├── 📄 health.php                       ✅ (NEW — system check)
│
├── 📁 config/
│   └── 📄 db.php                       ✅ (updated: auto-status)
│
├── 📁 includes/
│   ├── 📄 auth.php                     ✅
│   ├── 📄 functions.php                ✅
│   ├── 📄 header.php                   ✅
│   └── 📄 footer.php                   ✅
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── 📄 style.css                ✅ (updated: 6 new sections)
│   ├── 📁 js/
│   │   └── 📄 script.js                ✅ (updated: 3 new features)
│   └── 📁 images/
│
├── 📁 database/
│   └── 📄 online_voting_college_assign.sql   ✅
│
├── 📄 README.md                        ✅
├── 📄 REPORT.md                        ✅
├── 📄 VIVA_QA.md                       ✅
├── 📄 SCREENSHOTS_GUIDE.md             ✅
├── 📄 QUICK_START.md                   ✅
├── 📄 FLOWCHART.md                     ✅ (NEW)
└── 📄 BONUS_FEATURES.md                ✅ (NEW — this document)