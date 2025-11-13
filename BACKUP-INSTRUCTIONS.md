# INSTAPRINT SYSTEM BACKUP GUIDE

## Overview
This guide helps you create a complete backup of your Instaprint system to a USB flash drive.

## What Gets Backed Up
- ✅ Complete Laravel application (315MB)
- ✅ MySQL database (all data)
- ✅ Python server files (Bluetooth, USB, etc.)
- ✅ System configurations (Nginx, systemd services)
- ✅ Environment files (.env)

## Requirements
- USB Flash Drive (minimum 2GB recommended)
- USB drive formatted as FAT32, exFAT, or ext4
- Root/sudo access

## Step-by-Step Backup Process

### 1. Insert USB Flash Drive
```bash
# Check if USB is detected
lsblk

# You should see something like /dev/sda1 or /dev/sdb1
```

### 2. Mount the USB Drive (if not auto-mounted)
```bash
# Create mount point
sudo mkdir -p /media/usb

# Mount the USB drive (replace sda1 with your device)
sudo mount /dev/sda1 /media/usb
```

### 3. Run the Backup Script
```bash
# Navigate to Laravel directory
cd /var/www/html/laravel

# Run the backup script
sudo ./backup-system.sh /media/usb
```

### 4. Wait for Completion
The script will:
- Create a backup directory on your USB
- Backup all system files
- Create a restore script
- Generate a README file

This takes approximately 2-5 minutes depending on USB speed.

### 5. Safely Unmount
```bash
# Sync and unmount
sudo sync
sudo umount /media/usb
```

## What You'll Get on the USB Drive

```
/media/usb/instaprint_backup/
├── laravel_app_YYYYMMDD_HHMMSS.tar.gz    # Complete Laravel app
├── database_YYYYMMDD_HHMMSS.sql          # Database dump
├── python_servers_YYYYMMDD_HHMMSS.tar.gz # Python servers
├── configs/                               # System configs
├── .env                                   # Environment file
├── RESTORE.sh                             # Auto-restore script
└── README.txt                             # Restoration guide
```

## How to Restore (After System Crash)

### Quick Restore (Automated)
1. Boot your Raspberry Pi
2. Plug in the USB drive
3. Open terminal:
```bash
cd /media/[username]/[usb-name]/instaprint_backup
sudo ./RESTORE.sh
```
4. Follow the prompts
5. Reboot: `sudo reboot`

### Manual Restore (If Script Fails)
See the README.txt file in the backup folder for manual steps.

## Scheduling Automatic Backups

To create automatic daily backups:

```bash
# Create cron job (edit crontab)
sudo crontab -e

# Add this line for daily backup at 2 AM:
0 2 * * * /var/www/html/laravel/backup-system.sh /media/usb >> /var/log/instaprint-backup.log 2>&1
```

## Tips

1. **Multiple Backups**: Use 2 USB drives and rotate them weekly
2. **Test Restores**: Periodically test the restore process
3. **Label USB Drives**: Clearly label with date and "INSTAPRINT BACKUP"
4. **Check Backup Size**: Each backup is ~350-500MB
5. **Keep USB Safe**: Store in a secure, dry place away from the machine

## Troubleshooting

### USB Not Detected
```bash
# Check USB devices
lsblk
dmesg | tail -20

# Try different USB port
```

### Permission Denied
```bash
# Make sure you're using sudo
sudo ./backup-system.sh /media/usb
```

### Not Enough Space
```bash
# Check USB space
df -h /media/usb

# You need at least 500MB free
```

### Database Backup Failed
```bash
# Check MySQL credentials
sudo mysql -u root -p

# Manual database backup
sudo mysqldump -u root instaprint_db > /media/usb/manual_backup.sql
```

## Support

For issues:
- Check Laravel logs: `/var/www/html/laravel/storage/logs/`
- Check system logs: `sudo journalctl -xe`
- Verify backup completed: Check USB drive contents

## Important Notes

⚠️ **Always verify your backup after creation**
⚠️ **Test the restore process before you need it**
⚠️ **Keep multiple backup copies**
⚠️ **Update backups regularly (weekly recommended)**
⚠️ **Store USB drives safely**

---

**Script Location**: `/var/www/html/laravel/backup-system.sh`
**Created**: $(date)
