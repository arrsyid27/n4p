# N4P (Not4Posers) POS System

A complete, full-featured Point of Sale (POS) system built with PHP and MySQL. Simple, fast, and reliable for managing sales, inventory, and business analytics.

## Features

### Core POS Features
- ✅ **Point of Sale (POS)** - Fast and intuitive checkout interface
- ✅ **Product Management** - Add, edit, and delete products with categories
- ✅ **Inventory Management** - Track stock levels and low stock alerts
- ✅ **Stock Adjustment** - Record stock in, out, and corrections
- ✅ **Payment Processing** - Support for Cash, Card, and Bank Transfer
- ✅ **Transactions** - Complete transaction history with details
- ✅ **Sales Reports** - Detailed sales analytics and best sellers

### Admin Features
- ✅ **Dashboard** - Sales overview and key metrics
- ✅ **User Management** - Multi-user support with roles (Admin/Cashier)
- ✅ **Inventory Reports** - Low stock alerts and inventory status
- ✅ **Best Sellers** - Track top-selling products
- ✅ **Daily Sales Summary** - Automatic daily summaries

### User Features
- ✅ **Authentication** - Secure login and signup
- ✅ **Profile Management** - Update profile information
- ✅ **Password Change** - Secure password management
- ✅ **Role-Based Access** - Different permissions for admins and cashiers

## Project Structure

```
n4p-web/
├── assets/
│   ├── css/
│   │   └── style.css              # Main styling
│   ├── js/
│   │   └── (JavaScript files)
│   └── images/
├── includes/
│   ├── config.php                 # Database configuration
│   └── functions.php              # Helper functions
├── pages/
│   ├── login.php                  # Login & signup page
│   ├── dashboard.php              # Main dashboard
│   ├── pos.php                    # POS/Checkout page
│   ├── products.php               # Product management
│   ├── transactions.php           # Transaction history
│   ├── transaction_detail.php    # Transaction details & receipt
│   ├── inventory.php              # Inventory management
│   ├── users.php                  # User management
│   ├── profile.php                # User profile
│   ├── settings.php               # Application settings
│   └── reports.php                # Sales reports
├── api/
│   ├── checkout.php               # Payment processing
│   └── logout.php                 # User logout
├── database/
│   └── n4p_database.sql          # Database schema
├── index.php                      # Landing page
└── README.md                      # This file
```

## Installation Guide

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Basic knowledge of databases

### Step 1: Setup Database

1. Open your MySQL client (phpMyAdmin or MySQL command line)
2. Import the database schema:
   ```sql
   -- Copy and paste the contents of database/n4p_database.sql
   ```

   Or use command line:
   ```bash
   mysql -u root -p < database/n4p_database.sql
   ```

3. Verify the database was created:
   ```sql
   USE n4p_pos;
   SHOW TABLES;
   ```

### Step 2: Configure Application

1. Edit `includes/config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'n4p_pos');
   ```

2. Update the APP_URL if not using localhost:
   ```php
   define('APP_URL', 'http://your-domain.com/n4p-web');
   ```

### Step 3: Place Files

1. Copy all files to your web server directory:
   - For Apache: `/var/www/html/n4p-web/`
   - For local Windows: `C:/xampp/htdocs/n4p-web/`
   - For local Mac: `/Library/WebServer/Documents/n4p-web/`

2. Set proper permissions (Linux/Mac):
   ```bash
   chmod -R 755 n4p-web/
   ```

### Step 4: Access Application

1. Open your browser and go to:
   ```
   http://localhost/n4p-web/
   ```

2. Login with default credentials:
   - **Username**: admin
   - **Password**: admin123

## Database Schema Overview

### Users Table
- User authentication and role management
- Supports admin and cashier roles

### Products Table
- Product catalog with pricing and stock information
- Linked to categories

### Categories Table
- Product categorization

### Transactions Table
- Sales transaction records
- Tracks payment status, discount, tax

### Transaction Items Table
- Individual items in each transaction

### Stock Adjustments Table
- Records all stock movements (in/out/correction)

### Best Selling Summary Table
- Automatic summary of best-selling products

### Daily Sales Summary Table
- Daily automated sales summary

## Usage Guide

### For Cashiers

1. **Making a Sale**
   - Click "POS / Checkout"
   - Search or select products
   - Adjust quantities using +/- buttons
   - Apply discount if needed
   - Select payment method
   - Click "Pay" to complete

2. **Viewing Transactions**
   - Click "Transactions"
   - Filter by status (Pending/Completed/Cancelled)
   - Click product to view receipt

### For Administrators

1. **Managing Products**
   - Click "Products"
   - Add new products with pricing and stock
   - Edit existing products
   - Delete discontinued items

2. **Inventory Management**
   - Click "Inventory"
   - Record stock adjustments (in/out/correction)
   - View low-stock alerts
   - Track adjustment history

3. **User Management**
   - Click "Users"
   - Add new cashiers or admin users
   - Assign roles and permissions

4. **Viewing Reports**
   - Click "Reports"
   - Select date range
   - View sales analytics
   - Check best sellers
   - Print detailed reports

## API Endpoints

### POST /api/checkout.php
Process a transaction

**Parameters:**
```json
{
  "cart": [
    {
      "id": 1,
      "name": "Product Name",
      "price": 50000,
      "quantity": 2,
      "stock": 10
    }
  ],
  "customerName": "Customer Name",
  "paymentMethod": "cash",
  "discountPercentage": 10
}
```

**Response:**
```json
{
  "success": true,
  "transaction_id": 1,
  "transaction_number": "TRX20240101120000XXXX",
  "total": 99000
}
```

### GET /api/logout.php
Logout the current user

## Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection protection with prepared statements
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input data sanitization
- ✅ CSRF protection ready

## Customization

### Adding New Reports
1. Create new page in `pages/reports_custom.php`
2. Use helper functions from `includes/functions.php`
3. Add link to sidebar navigation

### Modifying CSS
Edit `assets/css/style.css` to customize:
- Colors (update CSS variables at top)
- Fonts and typography
- Layout and spacing
- Responsive breakpoints

### Adding Payment Methods
1. Edit the payment method dropdown in `pages/pos.php`
2. Add handler in `api/checkout.php`
3. Update transaction processing logic

## Troubleshooting

### Database Connection Error
- Check DB credentials in `includes/config.php`
- Verify MySQL is running
- Ensure database user has proper permissions

### Login Issues
- Clear browser cookies/cache
- Check if user exists in database
- Verify password was hashed with bcrypt

### Missing Products in POS
- Check if products are marked as 'active'
- Verify product category exists
- Check stock levels

### Session Timeout
- Adjust SESSION_TIMEOUT in `includes/config.php`
- Default is 30 minutes

## Default Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |

**⚠️ Important: Change admin password immediately after first login!**

## Support & Maintenance

### Database Maintenance
- Regular backups recommended
- Archive old transactions monthly
- Optimize tables quarterly

### Performance Tips
- Index frequently searched fields
- Archive completed transactions older than 1 year
- Clear old adjustment logs
- Monitor database size

## Version History

**v1.0.0** (2024)
- Initial release
- Complete POS functionality
- Inventory management
- Sales reporting
- Multi-user support

## License

This project is proprietary software. All rights reserved.

## Credits

Built with:
- PHP 7.4+
- MySQL 5.7+
- HTML5 & CSS3
- Vanilla JavaScript

## Contact & Support

For issues, features, or support, contact the administrator.

---

**Last Updated**: 2024
**Author**: N4P Development Team
