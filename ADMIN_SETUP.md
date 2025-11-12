# InstaPrint Admin Panel - Setup Guide

## Overview
A complete admin authentication system has been created for managing your InstaPrint application with the following features:
- Secure admin login system
- Price Settings management
- Print Sales reports and analytics
- Voucher management and generation

## Admin Login Credentials

**URL:** `http://your-domain.com/admin/login`

**Admin Account:**
- **Name:** `InstaPrint`
- **Email:** `admin@instaprint.com`
- **Password:** `InstaPrint2025Nov`

## Available Admin Features

### 1. Dashboard (`/admin` or `/admin/dashboard`)
- Overview of total sales, prints, and vouchers
- Quick access to all management modules
- System information display

### 2. Price Settings (`/admin/print-settings`)
- View all print pricing configurations
- Add new price settings for different paper sizes and color options
- Edit existing price settings
- Delete price settings
- Supports: A4, Short, Legal paper sizes
- Supports: Color and Grayscale options

### 3. Sales Report (`/admin/sales-report`)
- View total prints by paper size (A4, Short, Legal)
- View prints by color mode (Color, Grayscale)
- View prints by source (USB, Bluetooth, QR Code)
- Total pages printed
- Total revenue generated

### 4. Voucher Management (`/admin/vouchers`)
- View all vouchers (active, used, expired)
- Filter vouchers by status
- Track voucher usage and expiration

### 5. Generate Vouchers (`/admin/vouchers/generate`)
- Create new vouchers manually
- Set custom amounts
- Generate multiple vouchers at once
- Configure expiration days
- Add source/reference labels

### 6. Voucher Settings (`/admin/voucher-settings`)
- Configure default voucher settings
- Set default expiration days
- Manage voucher generation rules

## Routes Overview

### Public Routes (No Authentication Required)
- `GET /admin/login` - Show admin login form
- `POST /admin/login` - Handle login submission

### Protected Routes (Admin Authentication Required)
All routes below require admin login:

**Dashboard:**
- `GET /admin` - Admin dashboard
- `GET /admin/dashboard` - Admin dashboard (alternative)

**Price Management:**
- `GET /admin/print-settings` - List all price settings
- `GET /admin/print-settings/create` - Create new price setting
- `POST /admin/print-settings` - Store new price setting
- `GET /admin/print-settings/{id}/edit` - Edit price setting
- `PUT /admin/print-settings/{id}` - Update price setting
- `DELETE /admin/print-settings/{id}` - Delete price setting

**Sales:**
- `GET /admin/sales-report` - View sales report

**Vouchers:**
- `GET /admin/vouchers` - List all vouchers
- `GET /admin/vouchers/generate` - Show voucher generation form
- `POST /admin/vouchers/generate` - Generate vouchers
- `GET /admin/voucher-settings` - Voucher settings page
- `POST /admin/voucher-settings` - Update voucher settings

**Logout:**
- `POST /admin/logout` - Logout admin user

## Security Features

1. **Admin Middleware:** All admin routes are protected by the `admin` middleware
2. **Authentication Check:** Only logged-in users can access admin routes
3. **Admin Role Check:** Only users with `is_admin = true` can access admin panel
4. **Session Security:** Sessions are regenerated on login for security
5. **Password Hashing:** All passwords are securely hashed using Laravel's Hash facade

## How to Create Additional Admin Users

### Option 1: Using Tinker (Recommended)
```bash
php artisan tinker
```

Then run:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => Hash::make('your-secure-password'),
    'is_admin' => true
]);
```

### Option 2: Using Database Seeder
Run the seeder again with different credentials:
```bash
php artisan db:seed --class=AdminUserSeeder
```

### Option 3: Direct Database Insert
Update an existing user to become an admin:
```sql
UPDATE users SET is_admin = 1 WHERE email = 'user@example.com';
```

## Files Created/Modified

### New Files:
1. `app/Http/Middleware/IsAdmin.php` - Admin authentication middleware
2. `app/Http/Controllers/Admin/AdminAuthController.php` - Login/logout controller
3. `app/Http/Controllers/Admin/DashboardController.php` - Dashboard controller
4. `database/migrations/2025_11_11_154540_add_is_admin_to_users_table.php` - Admin field migration
5. `database/seeders/AdminUserSeeder.php` - Admin user seeder
6. `resources/views/admin/layout.blade.php` - Admin layout with navigation
7. `resources/views/admin/login.blade.php` - Admin login page
8. `resources/views/admin/dashboard.blade.php` - Admin dashboard

### Modified Files:
1. `app/Models/User.php` - Added is_admin field and isAdmin() method
2. `bootstrap/app.php` - Registered admin middleware
3. `routes/web.php` - Added admin routes with authentication
4. `resources/views/admin/sales-report.blade.php` - Updated to use new layout

## Troubleshooting

### Can't Login?
1. Make sure you ran the migration: `php artisan migrate`
2. Make sure you ran the seeder: `php artisan db:seed --class=AdminUserSeeder`
3. Clear cache: `php artisan cache:clear && php artisan config:clear`

### Getting 403 Forbidden?
- Make sure the user account has `is_admin = true` in the database
- Check that you're logged in with the correct account

### Routes Not Working?
- Clear route cache: `php artisan route:clear`
- Verify routes: `php artisan route:list | grep admin`

## Next Steps

1. **Change Default Password:** Login and change the default admin password
2. **Configure Prices:** Set up your print pricing in Price Settings
3. **Generate Vouchers:** Create vouchers for promotions
4. **Monitor Sales:** Check the Sales Report regularly

## Support

For issues or questions, please contact your development team or refer to the Laravel documentation at https://laravel.com/docs
