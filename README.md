# Spacexinfo

## 📄 Project Readme: Spacexinfo Trading Dashboard

This repository contains a user-facing dashboard template (`prepare-page.php`) and associated login/redirect scripts for the Spacexinfo platform. It features user authentication, session management, and database integration to display real-time user data.

### 🚀 Key Features

  * **WordPress Template Integration:** Designed to function as a custom WordPress Page Template.
  * **Secure Authentication:** Uses PHP sessions for logged-in status validation and role-based access control.
  * **Database Integration:** Securely fetches user data (specifically the **balance**) from a custom MySQL table.
  * **Modern UI:** Responsive, dark-mode dashboard interface with HTML/CSS.
  * **Modular Scripts:** Includes JavaScript functions for navigation, quick actions, and mobile menu toggling.

### 🛠️ Technology Stack

  * **Core:** **PHP** (Server-side logic, sessions, database queries).
  * **Platform:** **WordPress** (Used for templating and database environment).
  * **Database:** **MySQL/MariaDB** (accessed via WordPress's `$wpdb` object).
  * **Frontend:** **HTML5**, **CSS3**, and **Vanilla JavaScript**.

-----

## ⚙️ Setup and Installation (WordPress)

Follow these steps to deploy and run the code within a WordPress environment.

### 1\. Database Setup

You are using a custom table (assumed to be named `users`) which is **not** one of the default WordPress tables.

1.  **Table Creation:** Ensure your WordPress database contains the `users` table with the following schema:

    ```sql
    CREATE TABLE users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(10) NOT NULL DEFAULT 'user', -- e.g., 'user', 'admin'
        balance DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```

2.  **User Authentication:** The login script (`login-page.php`) must handle user authentication by querying this custom table and verifying the password hash using PHP's `password_verify()`. Upon success, it must set the following essential PHP session variables:

      * `$_SESSION['logged_in'] = true;`
      * `$_SESSION['user_id'] = [User ID];`
      * `$_SESSION['username'] = [Username];`

### 2\. File Placement & Integration

1.  **Dashboard Template:** Place `prepare-page.php` into your active WordPress theme directory (or child theme).
2.  **Create Page:** In the WordPress Admin, create a new Page (e.g., "Dashboard") and select **Spacexinfo Prepare Page** from the Page Attributes Template dropdown.
3.  **Authentication Files:** Ensure your login script (`login-page.php`) and any other necessary scripts are correctly set up to handle session creation *before* redirecting to the Dashboard page URL (e.g., `/dashboard/`).

### 3\. Key PHP Code Snippet in `prepare-page.php`

The dashboard uses WordPress's built-in database functions for secure data retrieval:

```php
// Load WordPress environment and database access
global $wpdb;

// ... Session check logic ...

// Securely retrieve balance for the logged-in user
$table_name = 'users'; // Your custom table name
$user_id = $_SESSION['user_id']; 

$sql = $wpdb->prepare("SELECT balance FROM $table_name WHERE id = %d", $user_id);
$current_balance = $wpdb->get_var($sql);

if ($current_balance === null) {
    $current_balance = 0.00;
}
```

-----

## 📂 Project Structure & Navigation

The primary files included in this repository serve the following purposes:

| File / Path | Description | Notes |
| :--- | :--- | :--- |
| `prepare-page.php` | The main user dashboard template. Contains PHP logic for balance fetching and all HTML/CSS/JS for the UI. | Requires `$_SESSION['user_id']` to be set. |
| `login-page.php` | *(Assumed)* Handles user credential submission, database lookup, password verification, and sets the required `$_SESSION` variables upon successful login. | Should redirect to `/dashboard/` on success. |
| `/log-in/` | The target URL for unauthorized redirects. | Must be configured in your WordPress permalinks/pages. |

### 🔑 Essential Session Variable

The dashboard relies on this variable being set after a successful login:

| Variable | Purpose |
| :--- | :--- |
| `$_SESSION['user_id']` | The unique primary key from the `users` database table, used to fetch the correct balance. |

### 🚪 Logout

The logout function in `prepare-page.php` is a placeholder that redirects to `/log-in/`. For a full logout, you must ensure that this link points to a PHP script that calls `session_destroy()` and then redirects the user.
