
# 🏫 School Management System

A **role-based School Management System** built with **PHP, MySQL, HTML, CSS, and JavaScript**, designed to streamline administrative, academic, and student-related operations. The system provides secure session-based logins, dashboards, analytics, timetables, and more for different user roles.

---

## 🚀 Features

### 🌐 Public Homepage

* View **latest school events and achievements** without login.
* Submit **feedback** via a public feedback corner.

### 🔑 Admin

* Manage users (**Add, Update, Delete**).
* Assign teachers and subjects to classes using the **timetable manager**.
* View feedback from visitors.
* Access **analytics**:

  * Attendance by class
  * Academic performance
  * User statistics
* Manage homepage content (**events and achievements**).
* Dashboard with **dynamic graphs and stats**.

### 👨‍🏫 Teacher

* View assigned classes and subjects.
* **Mark attendance** for students.
* Manage and update student **marks**.
* Access personal timetable.
* View and update profile.

### 👷 Other Staff (Librarians, Guards, etc.)

* Role-specific dashboard with contact details.
* View and update personal profile.
* See **upcoming events and announcements**.

### 👨‍🎓 Student

* View academic performance and attendance.
* Download report card.
* Access timetable.
* Edit and update profile.

---

## 🛠️ Technologies Used

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Web Server:** Apache (XAMPP recommended)
* **Libraries/Tools:** Chart.js (for analytics and graphs)

---

## 📂 Project Structure

```
School-Management-System/
│-- index.php            # Homepage
│-- login.php            # Login page
│-- unauthorized.php     # Unauthorized access page
│-- /admin               # Admin dashboard and modules
│-- /teacher             # Teacher dashboard and modules
│-- /student             # Student dashboard and modules
│-- /staff               # Other staff dashboard
│-- /assets              # CSS, JS, and images
│-- /includes            # Database connection, session management
│-- /uploads             # User profile images, report cards
│-- database.sql         # MySQL database schema
```

---

## 🖥️ System Architecture

```mermaid
flowchart TD
    A[Homepage / Public Users] --> B[Web Interface]
    B --> C[Backend Server - PHP]
    C --> D[(MySQL Database)]
    A1[Admin] --> B
    A2[Teacher] --> B
    A3[Other Staff] --> B
    A4[Student] --> B
```

---

## ⚠️ Security Features

* **Session-based login** with role distinction.
* **SQL Injection prevention** using `mysqli_real_escape_string()`.
* **Unauthorized access handling** with redirection.
* **Data validation** on all user inputs.

---

## 📈 Future Enhancements

* Enable **secondary attendance authority** for teachers.
* Revamp UI using **modern responsive frameworks**.
* Divide modules for **Primary, O/L, and A/L students**.
* Add **real-time notifications** (email/SMS/in-app).
* Introduce **assignment uploads and homework management**.

---

## 📷 Screenshots

<img width="1852" height="869" alt="Screenshot 2025-08-17 134238" src="https://github.com/user-attachments/assets/130c098e-e77c-4c19-be3f-75adefdbbe07" />
<img width="1855" height="871" alt="Screenshot 2025-08-17 134256" src="https://github.com/user-attachments/assets/440a0347-c135-4a7e-bd3d-4f4cc439cebc" />
<img width="1855" height="869" alt="Screenshot 2025-08-17 133800" src="https://github.com/user-attachments/assets/0101f136-585e-43e9-a100-66688905b1b3" />
<img width="1854" height="869" alt="Screenshot 2025-08-17 133834" src="https://github.com/user-attachments/assets/df69c10f-83b6-46ff-b82d-c132aab3c7ee" />
<img width="1856" height="871" alt="Screenshot 2025-08-17 133902" src="https://github.com/user-attachments/assets/bd1cd0ee-fb90-4b38-a40d-cb7c2a0f27f5" />
<img width="1854" height="875" alt="Screenshot 2025-08-17 133929" src="https://github.com/user-attachments/assets/32c77cd9-32fd-4943-b856-33d603e6ecca" />
<img width="1853" height="867" alt="Screenshot 2025-08-17 134009" src="https://github.com/user-attachments/assets/dba61838-5592-4691-8555-e8f109ec5d4b" />
<img width="1853" height="872" alt="Screenshot 2025-08-17 134025" src="https://github.com/user-attachments/assets/922aca4b-1b2d-4e39-be05-7793e51f63e0" />
<img width="1855" height="871" alt="Screenshot 2025-08-17 134051" src="https://github.com/user-attachments/assets/6c6faea9-7225-41c0-9f6c-9139d42440f4" />
<img width="1853" height="869" alt="Screenshot 2025-08-17 134124" src="https://github.com/user-attachments/assets/a8ca081e-54e3-40a0-ad2c-7c5a7dbeb7fb" />
<img width="1855" height="873" alt="Screenshot 2025-08-17 134142" src="https://github.com/user-attachments/assets/6110a3cf-3fc4-4420-8a91-49c46e27653f" />
<img width="1854" height="876" alt="Screenshot 2025-08-17 134206" src="https://github.com/user-attachments/assets/77edc444-f540-463a-87f0-ea2069d4cf24" />


## ⚡ Installation Guide

1. **Clone the repository**

   ```bash
   git clone https://github.com/KPulathisi/School-Management-System.git
   ```

2. **Import the database**

   * Open `phpMyAdmin`.
   * Create a new database (e.g., `school_management`).
   * Import `database.sql`.

3. **Configure database connection**

   * Go to `/includes/db_connect.php`.
   * Update database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

4. **Run the system**

   * Place the project folder in `htdocs` (if using XAMPP).
   * Start Apache & MySQL.
   * Open [http://localhost/School-Management-System](http://localhost/School-Management-System).

---

## 👨‍💻 Author

**Kavinda Pulathisi**
Undergraduate IT Student – SLIATE Tangalle
📍 Colombo, Sri Lanka



