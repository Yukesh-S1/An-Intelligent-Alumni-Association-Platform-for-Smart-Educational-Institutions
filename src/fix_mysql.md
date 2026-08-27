# MySQL Won't Start - Troubleshooting Guide

## Common Solutions

### Solution 1: Port Conflict (Most Common)
MySQL default port 3306 might be in use by another application.

**Check what's using port 3306:**
```cmd
netstat -ano | findstr :3306
```

**If something is using it:**
- Option A: Stop the conflicting service
- Option B: Change MySQL port in XAMPP:
  1. Click "Config" next to MySQL in XAMPP
  2. Select "my.ini"
  3. Find `port=3306` and change to `port=3307`
  4. Save and restart XAMPP

### Solution 2: Run as Administrator
- Close XAMPP completely
- Right-click XAMPP Control Panel
- Select "Run as Administrator"
- Try starting MySQL again

### Solution 3: Check MySQL Error Log
1. In XAMPP Control Panel, click "Logs" next to MySQL
2. Select "MySQL Error Log"
3. Look for specific error messages at the bottom

### Solution 4: Firewall/Antivirus
- Temporarily disable antivirus
- Add XAMPP to Windows Firewall exceptions

### Solution 5: Corrupted MySQL Data
If MySQL crashed previously:
1. Stop all XAMPP services
2. Go to `C:\xampp\mysql\data\`
3. Delete files: `ib_logfile0` and `ib_logfile1`
4. Restart XAMPP and start MySQL

### Solution 6: Reset MySQL
**WARNING: This will delete all databases!**
1. Backup `C:\xampp\mysql\data\` folder
2. Stop MySQL
3. Delete everything in `C:\xampp\mysql\data\` EXCEPT `mysql` folder
4. Start MySQL

## Quick Test
Once MySQL is running, test the connection:
```cmd
cd C:\xampp\mysql\bin
mysql -u root -p
```
(Press Enter when asked for password if you haven't set one)
