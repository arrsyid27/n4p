# N4P (Not4Posers) POS System - Complete Setup Guide

## Quick Start Guide

### Minimum Requirements
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.1
- Apache 2.4 or Nginx
- 50MB disk space
- Modern web browser

### Installation Steps

#### 1. Download & Extract Files
```bash
# Download project to your web root
# Windows: C:\xampp\htdocs\n4p-web
# Linux: /var/www/html/n4p-web
# Mac: /Library/WebServer/Documents/n4p-web
```

#### 2. Create Database

**Option A: Using MySQL Command Line**
```bash
mysql -u root -p < database/n4p_database.sql
```

**Option B: Using phpMyAdmin**
1. Go to phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import"
3. Select `database/n4p_database.sql`
4. Click "Go"

**Option C: Manual Database Creation**
```sql
-- Open MySQL command line and paste contents of n4p_database.sql
```

#### 3. Configure Database Connection

Edit `includes/config.php`:

```php
<?php
// Change these values to match your setup
define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASSWORD', '');           // Your MySQL password
define('DB_NAME', 'n4p_pos');        // Database name
define('APP_URL', 'http://localhost/n4p-web');  // Your application URL
?>
```

#### 4. Set File Permissions (Linux/Mac)

```bash
cd /var/www/html/n4p-web
chmod -R 755 .
chmod -R 755 assets/
chmod -R 755 pages/
chmod -R 755 includes/
chmod -R 755 api/
```

#### 5. Access Application

Open your browser:
```
http://localhost/n4p-web/
```

## Login Credentials

| Field | Value |
|-------|-------|
| Username | admin |
| Password | admin123 |

### ⚠️ IMPORTANT: Change Password Immediately!

After first login:
1. Click "👤" → "My Profile"
2. Go to "Change Password" section
3. Enter old password: `admin123`
4. Enter new secure password
5. Save changes

## Database Structure Overview

### Users
- id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- full_name
- phone
- user_role (admin/cashier)
- status (active/inactive)
- Timestamps

### Products
- id (Primary Key)
- category_id (Foreign Key)
- name
- description
- sku (Unique)
- purchase_price
- selling_price
- stock
- min_stock
- max_stock
- image_url
- status
- Timestamps

### Transactions
- id (Primary Key)
- transaction_number (Unique)
- user_id (Foreign Key)
- customer_name
- customer_phone
- customer_email
- subtotal, discount, tax, total
- payment_method
- payment_status (pending/completed/cancelled)
- Timestamps

### Stock Adjustments
- id (Primary Key)
- product_id (Foreign Key)
- user_id (Foreign Key)
- adjustment_type (in/out/correction)
- quantity
- reason
- Timestamp

### Transaction Items
- Links products to transactions
- Stores product details at time of sale
- Tracks quantity and price per item

## Feature Overview

### Dashboard
- Sales metrics (today, this month)
- Pending transactions count
- Low stock alerts
- Recent transactions
- Best-selling products
- Quick action buttons

### POS (Point of Sale)
- Product search and filter
- Add to cart
- Quantity adjustment
- Discount percentage
- Multiple payment methods
- Real-time calculations

### Products Management
- Add/Edit/Delete products
- Category management
- Stock tracking
- Pricing management
- Bulk operations

### Transactions
- View all transactions
- Filter by status
- Transaction details and receipt
- Print receipts
- Track payment status

### Inventory
- Stock adjustments
- Low stock alerts
- Adjustment history
- Stock in/out/correction
- Reorder recommendations

### Admin Features
- User management
- Add/Remove users
- Assign roles
- Sales reports
- Detailed analytics
- Daily summaries

## Customization

### Change Company Name
Edit these files:
- `includes/config.php`: APP_NAME
- `index.php`: Hero section
- `pages/dashboard.php`: Navbar brand
- `assets/css/style.css`: Colors and fonts

### Modify Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #000;
    --secondary-color: #333;
    --accent-color: #6b21a8;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
}
```

### Add New Features
1. Create new page in `pages/new_feature.php`
2. Add function to `includes/functions.php`
3. Add API handler if needed in `api/`
4. Add navigation link in sidebar

### Integrate Payment Gateway
1. Edit `api/checkout.php`
2. Add payment gateway API call
3. Update transaction status based on gateway response

## Troubleshooting

### Problem: "Connection failed: Unknown MySQL user"
**Solution:**
- Check database username in `config.php`
- Verify user exists in MySQL
- Check user permissions

### Problem: "Table n4p_pos doesn't exist"
**Solution:**
- Run database script again
- Verify import completed successfully
- Check database name in config.php

### Problem: "Login fails with correct credentials"
**Solution:**
- Check password is hashed for user
- Default password is `admin123`
- Clear browser cookies/cache
- Check user status is 'active'

### Problem: "Cannot execute PHP files"
**Solution:**
- Verify PHP is installed and enabled
- Check Apache has mod_php enabled
- Test with `<?php phpinfo(); ?>`

### Problem: "Files not uploading or CSS not loading"
**Solution:**
- Check .htaccess is in root directory
- Verify file permissions (755)
- Check mod_rewrite is enabled
- Restart Apache server

### Problem: "Session expires too quickly"
**Solution:**
- Edit SESSION_TIMEOUT in `config.php`
- Increase PHP session timeout in php.ini
- Default is 30 minutes

## Maintenance

### Regular Tasks

**Weekly:**
- Monitor low stock alerts
- Check transaction logs
- Review system performance

**Monthly:**
- Backup database
- Archive old transactions
- Review sales reports
- Update inventory counts

**Quarterly:**
- Optimize database tables
- Clean up logs
- Review and update prices
- Audit user accounts

### Backup Database

**Using MySQL Command:**
```bash
mysqldump -u root -p n4p_pos > backup_n4p_$(date +%Y%m%d).sql
```

**Using phpMyAdmin:**
1. Select database `n4p_pos`
2. Click "Export"
3. Choose SQL format
4. Click "Go"

## Advanced Configuration

### Enable Debug Mode
Edit `includes/config.php`:
```php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
```

### Configure Email Notifications
Add to `includes/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('ADMIN_EMAIL', 'admin@yoursite.com');
```

### Increase Upload Size
Edit `includes/config.php`:
```php
php_value upload_max_filesize 100M
php_value post_max_size 100M
```

## Performance Optimization

### Database Optimization
```sql
-- Optimize all tables
OPTIMIZE TABLE products, transactions, transaction_items;

-- Add indexes
ALTER TABLE products ADD INDEX idx_sku (sku);
ALTER TABLE transactions ADD INDEX idx_user (user_id);
```

### Enable Caching
This is already configured in `.htaccess`:
- Static files: 60 days cache
- CSS/JS: 30 days cache
- HTML: 1 day cache

### Monitor Database Size
```sql
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'n4p_pos'
ORDER BY (data_length + index_length) DESC;
```

## Security Best Practices

✅ **Do:**
- Change default admin password
- Keep database credentials safe
- Regularly backup data
- Update PHP and MySQL
- Use strong passwords
- Monitor access logs
- Regular security audits

❌ **Don't:**
- Share admin credentials
- Store passwords in plain text
- Use default database name
- Expose sensitive files
- Trust user input without validation
- Leave debug mode on production

## Support & Troubleshooting

### Common Issues & Solutions

1. **White page error**
   - Check error_log in Apache logs
   - Enable error reporting in config.php
   - Check PHP version compatibility

2. **Session not working**
   - Check session directory permissions
   - Verify PHP session.auto_start is OFF
   - Clear browser cookies

3. **Database errors**
   - Check MySQL is running
   - Verify credentials are correct
   - Check database and tables exist

4. **File permission issues**
   - Run: `chmod -R 755 n4p-web/`
   - Check Apache user has read access
   - Verify directories are executable

## Getting Help

For support:
1. Check README.md
2. Review error logs
3. Check database integrity
4. Verify configuration
5. Test with sample data

## Next Steps

After successful installation:

1. ✅ Login with admin credentials
2. ✅ Change admin password
3. ✅ Add product categories
4. ✅ Create test products
5. ✅ Add cashier users
6. ✅ Process test transactions
7. ✅ Review reports
8. ✅ Configure settings
9. ✅ Backup database
10. ✅ Start using the system!

---

**Version:** 1.0.0
**Last Updated:** 2024
**Support:** Included in package
