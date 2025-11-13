# InstaPrint USB Backup & Restore System

## Overview
Your InstaPrint system now has a fully automated backup and restore system using your USB flash drive labeled **"INSTAPRINT_BACKU"**.

## Quick Start

### Create a Backup
```bash
sudo bash /var/www/html/laravel/backup-to-usb.sh
```

### Restore from Backup
```bash
sudo bash /mnt/instaprint_backup/RESTORE.sh
```

### Interactive Menu
```bash
bash /var/www/html/laravel/usb-manager.sh
```

## Features

### 1. **Manual Backup** (`backup-to-usb.sh`)
- Backs up entire Laravel application
- Backs up SQLite database
- Excludes unnecessary files (node_modules, caches, logs)
- Creates timestamped backups
- Generates restore script automatically

### 2. **Automatic USB Detection**
- udev rule automatically detects when USB drive is inserted
- Logs detection events to `/var/log/instaprint-usb.log`
- Mounts drive automatically
- Ready for quick restore

### 3. **Restore System** (`RESTORE.sh`)
- Located on the USB drive after first backup
- Restores complete system from latest backup
- Backs up current system before restoring
- Preserves permissions and ownership
- Clears caches automatically

### 4. **Interactive Manager** (`usb-manager.sh`)
- User-friendly menu interface
- Options to backup, restore, or view backup information
- Shows all available backups with dates and sizes

## What Gets Backed Up

✅ **Included:**
- All Laravel application files
- SQLite database
- Configuration files (.env)
- Python servers (Bluetooth, USB, Coin, Dispenser)
- Public assets (icons, fonts, CSS, JS)
- Git repository
- Admin credentials
- All custom scripts

❌ **Excluded:**
- node_modules folder
- vendor folder (composer packages)
- Storage logs
- Framework caches
- Session files

## What Gets Preserved During Restore

The following are saved before restore:
- Current system is moved to `/var/www/html/laravel_backup_TIMESTAMP`
- You can access the old system if needed

## Backup Location Structure

```
/mnt/instaprint_backup/
├── RESTORE.sh              # Restore script
├── backup_info.txt         # Backup metadata
└── instaprint_backup_YYYYMMDD_HHMMSS/
    ├── laravel/            # Full application backup
    ├── database.sqlite     # Database backup
    └── backup_info.txt     # This backup's info
```

## Step-by-Step Usage

### Creating a Backup

1. Insert the USB drive labeled "INSTAPRINT_BACKU"
2. Run the backup script:
   ```bash
   sudo bash /var/www/html/laravel/backup-to-usb.sh
   ```
3. Wait for completion (shows progress)
4. USB drive now contains complete backup
5. Keep USB drive safe

### Restoring from Backup

1. Insert the USB drive with backups
2. Run the restore script:
   ```bash
   sudo bash /mnt/instaprint_backup/RESTORE.sh
   ```
3. Confirm when prompted
4. Wait for restoration to complete
5. Restart the Laravel server:
   ```bash
   cd /var/www/html/laravel
   php artisan serve --host=0.0.0.0 --port=8000
   ```

### Using the Interactive Menu

1. Insert the USB drive
2. Run the manager:
   ```bash
   bash /var/www/html/laravel/usb-manager.sh
   ```
3. Select option:
   - `1` - Create new backup
   - `2` - Restore from backup
   - `3` - View backup info
   - `4` - Exit

## Automatic Features

### USB Auto-Detection
- When you insert the USB drive, it's automatically detected
- Event is logged to `/var/log/instaprint-usb.log`
- Drive is auto-mounted to `/mnt/instaprint_backup`
- Check logs: `tail -f /var/log/instaprint-usb.log`

### udev Rule
Location: `/etc/udev/rules.d/99-instaprint-usb.rules`

To reload udev rules after changes:
```bash
sudo udevadm control --reload-rules
sudo udevadm trigger
```

## Troubleshooting

### USB Not Detected
```bash
# Check if USB is connected
lsblk | grep -i INSTAPRINT

# Manually mount
sudo mkdir -p /mnt/instaprint_backup
sudo mount /dev/sda1 /mnt/instaprint_backup
```

### Backup Failed
```bash
# Check disk space on USB
df -h /mnt/instaprint_backup

# Check USB is writable
touch /mnt/instaprint_backup/test.txt
rm /mnt/instaprint_backup/test.txt
```

### Restore Failed
```bash
# Check if backup exists
ls -la /mnt/instaprint_backup/instaprint_backup_*

# Check restore script is executable
chmod +x /mnt/instaprint_backup/RESTORE.sh
```

### View Logs
```bash
# USB detection log
tail -50 /var/log/instaprint-usb.log

# System log
journalctl -u laravel-server -n 50
```

## Emergency Recovery

If the system is broken and won't start:

1. **Boot the Raspberry Pi**
2. **Insert backup USB drive**
3. **Open terminal** (if GUI available) or SSH in
4. **Run restore:**
   ```bash
   sudo bash /mnt/instaprint_backup/RESTORE.sh
   ```
5. **Restart services:**
   ```bash
   cd /var/www/html/laravel
   php artisan serve --host=0.0.0.0 --port=8000 &
   ```

## Best Practices

### Regular Backups
- Create backup after major changes
- Create backup before updates
- Create weekly backups during regular operation
- Keep multiple USB drives with rotating backups

### USB Drive Care
- Use quality USB drive (not cheap/old drives)
- Label clearly: "INSTAPRINT_BACKU"
- Store in safe, dry location
- Don't remove during backup/restore operations

### Testing
- Test restore process periodically
- Verify backups contain all necessary files
- Check backup sizes are reasonable

## Files Created

| File | Purpose |
|------|---------|
| `/var/www/html/laravel/backup-to-usb.sh` | Backup script |
| `/var/www/html/laravel/usb-auto-handler.sh` | USB auto-detection handler |
| `/var/www/html/laravel/usb-manager.sh` | Interactive menu |
| `/etc/udev/rules.d/99-instaprint-usb.rules` | udev rule for USB detection |
| `/mnt/instaprint_backup/RESTORE.sh` | Restore script (on USB) |
| `/var/log/instaprint-usb.log` | USB detection log |

## Safety Features

✅ **Backup Safety:**
- Current system backed up before restore
- Timestamped backups prevent overwriting
- Confirmation prompts before destructive operations

✅ **Data Integrity:**
- rsync with verification
- sync command ensures all data written to USB
- Checksums via rsync's built-in verification

✅ **Permissions:**
- Restores correct file ownership
- Sets proper folder permissions
- Maintains executable flags

## Questions or Issues?

Check the logs first:
```bash
# USB operations
tail -f /var/log/instaprint-usb.log

# System logs
journalctl -xe
```

Your backup system is now ready to use! Keep that USB drive safe - it's your insurance policy.
