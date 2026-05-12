# 🔥 Habit Tracker Web Application

A full-stack Habit Tracker web application that helps users build consistent habits by tracking daily progress, streaks, and weekly performance using interactive charts.

---

## 📌 Project Overview

This project allows users to:

- Register and login securely
- Create and manage daily habits
- Mark habits as completed
- Track streaks automatically
- View weekly progress in charts
- Switch between dark and light mode

It is built using **PHP, MySQL, JavaScript, AJAX, and Chart.js**.

---

## 🚀 Features

### 👤 User Authentication
- Secure registration and login system
- Password hashing using `password_hash()`
- Session-based authentication

### 📝 Habit Management
- Add new habits
- Delete habits
- Mark daily completion

### 🔥 Streak Tracking
- Automatically calculates streaks
- Displays current progress

### 📊 Weekly Progress Chart
- Interactive bar chart using Chart.js
- Displays habit completion for last 7 days

### 🌙 Dark Mode
- Toggle between dark and light themes
- Saves user preference

### 📱 Responsive Design
- Works on desktop and mobile devices

---

## 🛠️ Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript
- Chart.js

### Backend
- PHP (Core PHP)
- MySQL Database
- AJAX (Fetch API)

### Tools
- Laragon (Local Server)
- phpMyAdmin
 📂 Project Structure
```bash
habit-tracker/
│── api/ # Backend API files
│── assets/ # CSS, JS, Images
│── config/ # Database connection
│── includes/ # Reusable PHP components
│── sql/ # Database SQL file
│── dashboard.php
│── login.php
│── register.php
│── logout.php
│── index.php
                           ---------------------------------------------------------------------------------------------------------------------------------------------------
  ```
---

## ⚙️ How to Run This Project

### 1️⃣ Install Requirements
- Install Laragon or XAMPP
- Install Git

---
2️⃣ Clone Repository

```bash
git clone https://Aakif009-new/habit-tracker.git
3️⃣ Move to Server Fold

Place project inside:

C:\laragon\www\
4️⃣ Import Database

Open phpMyAdmin

Create database:

habit_tracker

Import file:

sql/database.sql
5️⃣ Configure Database

Open:

config/database.php

Set credentials:

host = localhost
username = root
password = ""
database = habit_tracker
6️⃣ Run Project

Start Laragon and open:

http://localhost/habit-tracker
```
Application Screenshots:
<img width="1897" height="930" alt="image" src="https://github.com/user-attachments/assets/5c00097a-a27d-4ebe-8707-1319a52776e4" />
<img width="1914" height="913" alt="image" src="https://github.com/user-attachments/assets/5ca65e59-2ae3-4998-b4ac-454663836cfb" />
<img width="1893" height="920" alt="image" src="https://github.com/user-attachments/assets/18165e7b-c970-436f-a084-91686b71acb0" />

🔐 Security Features

Prepared SQL statements
Password hashing
Session authentication
Input sanitization

📊 Future Improvements

Email reminders
Habit categories
Monthly analytics
Mobile app integration

👨‍💻 Author

Name: S. Mohammed Aakif
Course: BCA (Bachelor of Computer Applications)
Project Type: Full-Stack Web Development Project
Mail for suggestion and changes : syedmdaakif007@gmail.com







