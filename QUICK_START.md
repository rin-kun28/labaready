# Quick Start Guide - Latest XAMPP

## Prerequisites
- XAMPP 8.2.x or higher installed
- Composer installed (optional but recommended)

## Setup Instructions

### 1. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** 
3. Start **MySQL**

### 2. Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `laundry_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 3. Import Database Schema
1. Select `laundry_db` database
2. Click "Import" tab
3. Choose your SQL file from `database/` folder
4. Click "Go"

### 4. Install Dependencies (Optional)
Open Command Prompt/PowerShell:
```bash
cd c:\xampp\htdocs\lab
composer install
```

If you don't have Composer, download from: https://getcomposer.org/

### 5. Configure Database Connection
The database connection is already configured in `db_connect.php`:
- Host: `localhost`
- Username: `root`
- Password: `` (empty)
- Database: `laundry_db`

If your setup is different, edit `db_connect.php` lines 11-14.

### 6. Access the Application
Open browser and navigate to:
```
http://localhost/lab/
```

## Default Login Credentials
Check your database `users` table for login credentials, or create a new user:

```sql
INSERT INTO users (name, username, password, type) 
VALUES ('Admin', 'admin', 'admin123', 1);
```

## Troubleshooting

### Apache Won't Start
- **Port 80 in use**: Change Apache port in XAMPP config
- **Skype conflict**: Disable Skype's use of ports 80/443
- **IIS running**: Stop IIS service

### MySQL Won't Start
- **Port 3306 in use**: Change MySQL port in XAMPP config
- **Previous instance**: Kill mysql.exe in Task Manager

### PHP Errors Displayed
Check `c:\xampp\apache\logs\error.log` for details

### Database Connection Failed
1. Verify MySQL is running in XAMPP
2. Check credentials in `db_connect.php`
3. Ensure database `laundry_db` exists
4. Check MySQL error log: `c:\xampp\mysql\data\*.err`

### Blank Page / No Output
1. Check PHP error log: `c:\xampp\php\logs\php_error_log`
2. Enable error display temporarily in `db_connect.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

### File Upload Issues
1. Check folder permissions on `uploads/` directory
2. Verify PHP settings in `.htaccess`:
   - `upload_max_filesize = 50M`
   - `post_max_size = 50M`

### Session Issues
1. Clear browser cookies
2. Check session folder permissions
3. Restart Apache

## File Structure
```
lab/
├── admin_class.php       # Main business logic (updated for PHP 8.x)
├── ajax.php              # AJAX endpoints
├── db_connect.php        # Database connection (updated)
├── composer.json         # Dependencies (updated)
├── .htaccess            # Apache config (new)
├── index.php            # Entry point
├── login.php            # Login page
├── assets/              # CSS, JS, images
├── database/            # SQL files
├── uploads/             # User uploads
└── vendor/              # Composer packages

```

## PHP Version Check
Create a file `phpinfo.php`:
```php
<?php phpinfo(); ?>
```

Access: `http://localhost/lab/phpinfo.php`

Verify:
- PHP Version: 8.0 or higher
- mysqli extension: Enabled
- mbstring extension: Enabled

**Important**: Delete `phpinfo.php` after checking!

## Security Notes

### For Development
- Error display is ON (see errors on screen)
- All debugging enabled

### For Production
1. Edit `db_connect.php` line 7:
   ```php
   // Comment out or remove this line:
   // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
   ```

2. Edit `.htaccess` lines 4-5:
   ```apache
   php_flag display_errors Off
   ```

3. Use strong passwords
4. Enable HTTPS
5. Implement CSRF protection

## Performance Tips

### Enable OPcache
In `c:\xampp\php\php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Enable Apache Modules
In XAMPP config, ensure these are enabled:
- mod_rewrite
- mod_deflate
- mod_expires
- mod_headers

## Getting Help

### Log Files
- Apache errors: `c:\xampp\apache\logs\error.log`
- PHP errors: `c:\xampp\php\logs\php_error_log`
- MySQL errors: `c:\xampp\mysql\data\*.err`

### Common Commands
```bash
# Check PHP version
php -v

# Check Composer version
composer -V

# Update dependencies
composer update

# Clear Composer cache
composer clear-cache
```

## Next Steps
1. ✅ Application is running
2. ✅ Database is connected
3. ✅ Login works
4. 📝 Read `UPGRADE_NOTES.md` for detailed changes
5. 🔒 Review security recommendations
6. 🚀 Start using the application!

## Support Resources
- XAMPP Documentation: https://www.apachefriends.org/
- PHP 8 Documentation: https://www.php.net/manual/en/
- MySQL 8 Documentation: https://dev.mysql.com/doc/
