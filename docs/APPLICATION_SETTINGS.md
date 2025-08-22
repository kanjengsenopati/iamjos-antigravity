# Application Settings Documentation

## Overview

The Application Settings module provides system administrators with tools to monitor system health and create database backups.

## Features

### 1. System Information

-   **PHP Version**: Current PHP version running on the server
-   **Laravel Version**: Current Laravel framework version
-   **Server Software**: Web server information (Apache, Nginx, etc.)
-   **Memory Limit**: PHP memory limit configuration
-   **Max Execution Time**: PHP maximum execution time
-   **Upload Max Size**: Maximum file upload size
-   **Free Disk Space**: Available disk space on the server

### 2. Database Information

-   **Database Name**: Current database name
-   **Database Size**: Total size of the database in MB
-   **Table Count**: Number of tables in the database
-   **Recent Backups**: List of recent backup files with size and creation date

### 3. Database Backup

-   **Create Backup**: Generate a complete MySQL database backup
-   **Download**: Automatic download of backup file
-   **Backup History**: View recent backup files

## Requirements

### System Requirements

-   **mysqldump**: Must be installed and accessible from command line
-   **PHP Process Extension**: Required for executing system commands
-   **Storage Permissions**: Write access to `storage/app/backups` directory

### Laravel Requirements

-   **Symfony Process Component**: For executing mysqldump command
-   **Carbon**: For date formatting
-   **Laravel Storage**: For file management

## Installation

### 1. Controller

The `ApplicationSettingController` is located at:

```
app/Http/Controllers/Admin/ApplicationSettingController.php
```

### 2. Routes

Routes are defined in `routes/web.php`:

```php
Route::get('application-setting', [ApplicationSettingController::class, 'index']);
Route::post('application-setting/backup', [ApplicationSettingController::class, 'backupDatabase']);
Route::get('application-setting/system-info', [ApplicationSettingController::class, 'getSystemInfo']);
Route::get('application-setting/database-info', [ApplicationSettingController::class, 'getDatabaseInfo']);
```

### 3. Views

The main view is located at:

```
resources/views/admins/application-setting/index.blade.php
```

### 4. Navigation

Menu item is added to the sidebar with permission check:

```blade
@can('application-setting')
    <!-- Application Settings Menu -->
@endcan
```

## Usage

### Accessing Application Settings

1. Login to admin panel
2. Navigate to "Application Settings" in the sidebar
3. Click on "System Settings"

### Creating Database Backup

1. Go to Application Settings page
2. Scroll to "Database Backup" section
3. Click "Create Backup" button
4. Confirm the action in the popup
5. Wait for backup creation
6. File will download automatically

### Viewing System Information

System and database information is loaded automatically when the page loads.

## Security Considerations

### Permissions

-   Only users with `application-setting` permission can access this module
-   Backup files are stored in `storage/app/backups` (not publicly accessible)
-   CSRF protection is enabled for all POST requests

### Backup Security

-   Backup files contain sensitive database information
-   Files are automatically deleted after download
-   Backup directory should not be publicly accessible
-   Consider implementing backup file encryption for production

## Troubleshooting

### Common Issues

#### 1. mysqldump not found

**Error**: `mysqldump: command not found`
**Solution**: Install MySQL client tools or ensure mysqldump is in system PATH

#### 2. Permission denied

**Error**: `Permission denied` when creating backup
**Solution**: Ensure web server has write permissions to `storage/app/backups`

#### 3. Backup file empty

**Error**: Backup file is created but empty
**Solution**: Check database credentials and connection

#### 4. Timeout during backup

**Error**: Process timeout during large database backup
**Solution**: Increase timeout in controller or use queue for large backups

### Windows-Specific Issues

#### 1. Path separators

The controller handles Windows path separators automatically.

#### 2. mysqldump location

Ensure MySQL bin directory is in system PATH or specify full path to mysqldump.

## Configuration

### Environment Variables

Make sure these are properly configured in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Backup Directory

Backups are stored in: `storage/app/backups/`

### File Naming Convention

Backup files are named: `backup_{database_name}_{timestamp}.sql`

Example: `backup_phri_2024-08-21_14-30-45.sql`

## API Endpoints

### GET /application-setting

Returns the main application settings page.

### POST /application-setting/backup

Creates and downloads database backup.

-   **Response**: File download (SQL file)
-   **Error Response**: JSON with error message

### GET /application-setting/system-info

Returns system information as JSON.

-   **Response**: JSON object with system details

### GET /application-setting/database-info

Returns database information and recent backups as JSON.

-   **Response**: JSON object with database details and backup history

## Future Enhancements

### Planned Features

1. **Scheduled Backups**: Automatic backup scheduling
2. **Backup Encryption**: Encrypt backup files
3. **Cloud Storage**: Upload backups to cloud storage
4. **Backup Restoration**: Restore from backup files
5. **Email Notifications**: Send backup completion emails
6. **Backup Compression**: Compress backup files to save space
7. **Multiple Database Support**: Backup multiple databases
8. **Incremental Backups**: Create incremental backups

### Performance Improvements

1. **Queue Integration**: Use Laravel queues for large backups
2. **Progress Tracking**: Real-time backup progress
3. **Chunked Processing**: Process large databases in chunks
4. **Background Processing**: Non-blocking backup creation

## Support

For issues or questions regarding the Application Settings module, please contact the development team.
