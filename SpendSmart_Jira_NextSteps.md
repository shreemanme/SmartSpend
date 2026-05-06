**SpendSmart** Jira Next Steps Guide

# **SpendSmart**
### Personal Expense Tracker

## **Jira Project Setup & Sprint Guide**

A step-by-step guide for setting up your Jira project,
product backlog, sprints, and team agreements before coding begins.

|Project|SpendSmart — Personal Expense Tracker|
|---|---|
|**Module**|CTEC2713 Agile Development Team Project|
|**Scrum Master**|Shreeman Bhandari|
|**Team**|Nandan Kumar Yadav | Ratnesh Kumar Yadav | Suraj Rai | Bibek<br>Timsena|
|**Tool**|Jira (Scrum template) — jira.atlassian.com|


#### **Contents**

**Step 1** Create Your Jira Project

**Step 2** Set Up Your Epics

**Step 3** Create Your Product Backlog

**Step 4** Set Up Your Sprint Board

**Step 5** Configure Your Board Columns

**Step 6** Team Agreements Before Coding

**Step 7** Ongoing Sprint Ceremonies

Summary — Order of Work


CTEC2713 Agile Development Team Project | Page 1


**SpendSmart** Jira Next Steps Guide

### **1 Create Your Jira Project**


Go to **jira.atlassian.com** and sign in (or create a free account), then follow these steps:


 - Click **Create project** and choose **Scrum** as the project template

 - Set the project name: **SpendSmart**

 - Set the project key: **SS**

 - Invite all team members using their email addresses

 - Assign **Shreeman Bhandari** as the project lead / Scrum Master


**Team member emails to invite:**

|Name|Role|
|---|---|
|Shreeman Bhandari|Scrum Master (Project Lead)|
|Nandan Kumar Yadav|Developer|
|Ratnesh Kumar Yadav|Developer|
|Suraj Rai|Developer|
|Bibek Timsena|Developer|


### **2 Set Up Your Epics**


Epics are the large feature areas — one per team member component. Go to **Backlog** → **Create Epic** for
each of the following:

|Epic Name|Assignee|
|---|---|
|Expense Entry Management|Shreeman Bhandari|
|User / Account Management|Nandan Kumar Yadav|
|Category Management|Ratnesh Kumar Yadav|
|Reporting and Summary|Suraj Rai|
|Audit / History Log|Bibek Timsena|


### **3 Create Your Product Backlog (User Stories)**


Under each Epic, create User Stories using the format: _"As a [user], I want to [action], so that [benefit]."_


CTEC2713 Agile Development Team Project | Page 2


**SpendSmart** Jira Next Steps Guide


Assign **Story Points** to each story (use 1, 2, 3, 5 or 8).


**Epic: Expense Entry Management — Shreeman Bhandari**

|User Story|Points|
|---|---|
|As a user, I want to add a new expense with amount, category and date|3|
|As a user, I want to view a list of all my expenses|2|
|As a user, I want to edit an existing expense to correct mistakes|3|
|As a user, I want to delete an expense (soft delete)|2|
|As a staff member, I want to view all expenses across users|3|



**Epic: User / Account Management — Nandan Kumar Yadav**

|User Story|Points|
|---|---|
|As a visitor, I want to register with my name, email and password|3|
|As a user, I want to log in with my credentials|2|
|As a user, I want to update my personal details|2|
|As a user, I want to close my account|2|
|As a staff member, I want to add, edit and delete user accounts|3|



**Epic: Category Management — Ratnesh Kumar Yadav**

|User Story|Points|
|---|---|
|As a staff member, I want to add new expense categories|2|
|As a staff member, I want to rename existing categories|2|
|As a staff member, I want to deactivate a category|2|
|As a user, I want to see only active categories when adding an expense|2|



**Epic: Reporting and Summary — Suraj Rai**

|User Story|Points|
|---|---|
|As a user, I want to see my total spending by category|3|
|As a user, I want to filter expenses by date range|3|
|As a user, I want to see a monthly spending breakdown|3|
|As a staff member, I want to generate and export reports across all users|5|



CTEC2713 Agile Development Team Project | Page 3


**SpendSmart** Jira Next Steps Guide


**Epic: Audit / History Log — Bibek Timsena**

|User Story|Points|
|---|---|
|As a system, I want to log every CREATE action on expense records|3|
|As a system, I want to log every UPDATE action on expense records|3|
|As a system, I want to log every DELETE action on expense records|3|
|As a staff member, I want to view the full audit log with filters|3|
|As a staff member, I want to flag a log entry as reviewed|2|



CTEC2713 Agile Development Team Project | Page 4


**SpendSmart** Jira Next Steps Guide

### **4 Set Up Your Sprint Board**


 - In the Backlog view, click **Create Sprint**  - name it **Sprint 1**

 - Drag the highest-priority stories from the backlog into Sprint 1

 - Set the Sprint duration: **2 weeks**

 - Click **Start Sprint**, set start and end dates

 - Each member moves their story to **In Progress** when they begin, and **Done** when complete


**Recommended Sprint 1 stories (foundation first):**

|Story|Assignee|Points|
|---|---|---|
|User registration and login|Nandan Kumar Yadav|3|
|Add and view expenses|Shreeman Bhandari|5|
|Add and manage categories|Ratnesh Kumar Yadav|4|


### **5 Configure Your Board Columns**


Set up the Jira board with these four columns (Kanban-style within your Scrum board):








|To Do|In Progress|In Review|Done|
|---|---|---|---|
|Story is in the sprint but<br>not started yet|Actively being worked on<br>right now|Code written, awaiting<br>peer review or testing|Completed and tested|


### **6 Team Agreements Before Coding Starts**

Discuss and agree on all of the following as a team **before Sprint 1 begins:**


I Decide on coding language / framework (e.g. Python + Flask, or PHP)

I Create a **GitHub repository** and invite all five team members

I Agree on database — **MySQL** is recommended for this module

I Shreeman (Scrum Master) sets up the initial project skeleton and shared folder structure

I Agree on a weekly stand-up time (e.g. every Monday 10 mins before lab)

I Link your GitHub repo to Jira (optional — allows Jira to track commits per story)

### **7 Ongoing Sprint Ceremonies**


CTEC2713 Agile Development Team Project | Page 5


**SpendSmart** Jira Next Steps Guide


Repeat these ceremonies every sprint throughout the project:

|Ceremony|When|Who|Purpose|
|---|---|---|---|
|Sprint Planning|Start of each sprint|Whole team|Pick stories, estimate points|
|Daily Stand-up|Each session / lab|Whole team|Progress, plans, blockers|
|Sprint Review|End of each sprint|Whole team|Demo what was built|
|Sprint Retro|End of each sprint|Whole team|What went well / what to improve|
|Journal Entry|Each Monday|Each member|Submit weekly journal, meeting notes, peer scores|


#### **Summary — Order of Work**

|Weeks 1–2|System Specification submitted → Jira set up → GitHub repo created|
|---|---|
|**Weeks 3–4**|Sprint 1: Foundation — login, add expense, categories|
|**Weeks 5–6**|Sprint 2: Read / edit / delete, reporting basics|
|**Weeks 7–8**|Sprint 3: Audit log, reporting export, admin panel|
|**Weeks 9–10**|Sprint 4: Testing, bug fixes, final polish|
|**Weeks 11+**|Final submission preparation|



_Tip: Keep your Jira board up to date every session. Move stories between columns as you work — this is what your_
_tutor will check._


CTEC2713 Agile Development Team Project | Page 6


