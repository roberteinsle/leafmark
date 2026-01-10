# Admin System Setup Guide

This document describes the multi-user admin system implementation and deployment steps.

## What Has Been Implemented

### 1. Database Changes
- ✅ MariaDB integration in docker-compose.yaml
- ✅ `is_admin` field added to users table
- ✅ `system_settings` table for configuration
- ✅ `invitations` table for invitation management

### 2. Models
- ✅ `SystemSetting` model with helper methods
- ✅ `Invitation` model for managing invitations

### 3. Controllers
- ✅ `AdminController` - Full admin functionality
- ✅ `RegisterController` - Extended with access controls

### 4. Middleware
- ✅ `IsAdmin` middleware for admin routes

### 5. Routes
- ✅ Admin routes under `/admin` prefix
- All protected with `auth` and `admin` middleware

### 6. Translations
- ✅ German and English translations complete

### 7. Registration Modes Supported
1. **Open** - Anyone can register
2. **Domain-restricted** - Only specific email domains
3. **Invitation-only** - Requires invitation link
4. **Code-required** - Personal registration code needed

## Deployment Steps

### Step 1: Backup Current Data (CRITICAL!)

```bash
cd ~/leafmark/app-source
./backup.sh
```

### Step 2: Stop Current Services

```bash
docker compose down
```

### Step 3: Update Environment Variables

Edit your `.env` file and add/update:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE
MYSQL_ROOT_PASSWORD=YOUR_SECURE_ROOT_PASSWORD_HERE
```

### Step 4: Start Services with MariaDB

```bash
docker compose up -d
```

Wait for database to be healthy:

```bash
docker compose ps
# Wait until db service shows "healthy"
```

### Step 5: Run Migrations

```bash
docker compose exec app php artisan migrate --force
```

### Step 6: Create Admin User

```bash
docker compose exec app php artisan db:seed --class=AdminUserSeeder
```

**IMPORTANT:** The default password is `password`. Log in and change it immediately!

### Step 7: Set Admin Password

After first login as robert@einsle.com:
1. Go to Settings
2. Change your password
3. Optionally update your profile

### Step 8: Configure Registration Settings

1. Log in as admin
2. Go to Admin → System Settings
3. Choose registration mode:
   - **Open**: Anyone can register
   - **Domain**: Only emails from `einsle.com` (or your domains)
   - **Invitation**: Admin must send invitations
   - **Code**: Users need a registration code

## Admin Features

### User Management (`/admin/users`)
- View all users
- Grant/revoke admin privileges
- Delete users (except yourself)
- See user book counts

### System Settings (`/admin/settings`)
- Enable/disable registration
- Set registration mode
- Configure allowed email domains
- Set registration code
- Manage invitations

### Invitations (`/admin/settings#invitations`)
- Create invitation for specific email
- Copy invitation link
- View invitation status
- Delete unused invitations

## Remaining Tasks (Views to Create)

The following view files still need to be created:

### 1. `/resources/views/admin/users.blade.php`
Shows user list with management options.

### 2. `/resources/views/admin/settings.blade.php`
System settings and invitation management.

### 3. Update `/resources/views/auth/register.blade.php`
Add fields for:
- Registration code (when mode = 'code')
- Invitation token (when mode = 'invitation')
- Show appropriate messages based on registration mode

## Testing the System

### Test Registration Modes

1. **Open Mode**:
   ```
   - Set mode to "open"
   - Try registering with any email
   - Should work
   ```

2. **Domain Mode**:
   ```
   - Set mode to "domain"
   - Set allowed domains to "einsle.com"
   - Try registering with user@einsle.com (should work)
   - Try registering with user@gmail.com (should fail)
   ```

3. **Invitation Mode**:
   ```
   - Set mode to "invitation"
   - Create invitation for user@example.com
   - Use invitation link to register
   - Should work only with correct email
   ```

4. **Code Mode**:
   ```
   - Set mode to "code"
   - Set code to "LEAFMARK2026"
   - Try registering with correct code (should work)
   - Try registering with wrong code (should fail)
   ```

## Security Notes

1. **Default Admin Password**: Change immediately after first login!
2. **Database Passwords**: Use strong passwords in production
3. **Registration**: Consider starting with "invitation" mode
4. **Backups**: Always backup before migrations

## Troubleshooting

### Can't Access Admin Panel
- Check if user has `is_admin = 1` in database
- Clear cache: `docker compose exec app php artisan cache:clear`

### Registration Not Working
- Check `system_settings` table values
- Verify registration mode configuration
- Check logs: `docker compose logs app`

### Database Connection Issues
- Verify MariaDB is healthy: `docker compose ps`
- Check database credentials in `.env`
- Wait longer for DB to start (can take 30-60 seconds)

## API Documentation

### SystemSetting Helper Methods

```php
// Get setting
SystemSetting::get('key', 'default');

// Set setting
SystemSetting::set('key', 'value');

// Check if registration enabled
SystemSetting::isRegistrationEnabled(); // bool

// Get registration mode
SystemSetting::getRegistrationMode(); // string

// Get allowed domains
SystemSetting::getAllowedEmailDomains(); // array

// Check if email domain allowed
SystemSetting::isEmailDomainAllowed('user@example.com'); // bool
```

### Invitation Model Methods

```php
// Create invitation
Invitation::create([
    'email' => 'user@example.com',
    'invited_by' => auth()->id(),
]);

// Find valid invitation
$invitation = Invitation::findValidByTokenAndEmail($token, $email);

// Check if valid
$invitation->isValid(); // bool

// Mark as used
$invitation->markAsUsed();
```

## Future Enhancements

- Family accounts (shared book collections)
- User activity logs
- Email notifications for invitations
- Bulk user import
- Advanced user permissions
