@echo off
echo Creating School Website Directory Structure...
echo.

REM Create main directory
mkdir school-website
cd school-website

REM Create subdirectories
mkdir admin
mkdir api  
mkdir images

REM Create main files
echo. > index.html
echo. > style.css
echo. > script.js
echo. > config.php
echo. > db.sql
echo. > admin_demo.php
echo. > fix_admin_login.php

REM Create admin files
echo. > admin\register.php
echo. > admin\login.php
echo. > admin\dashboard.php
echo. > admin\logout.php
echo. > admin\auth.php

REM Create API files
echo. > api\notices.php
echo. > api\gallery.php

cd..

echo.
echo ✅ Directory structure created successfully!
echo.
echo Next steps:
echo 1. Copy code from the previous messages into each file
echo 2. Right-click 'school-website' folder and compress to ZIP
echo 3. Follow setup instructions in CREATE_WEBSITE.txt
echo.
pause