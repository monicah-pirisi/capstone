================================================================================
SamEWS — Samburu Early Warning System
Capstone Project | Ashesi University | 2026
Student: Monicah Lekupe
Supervisor: Gideon Ofori Osabutey
================================================================================

LIVE DEPLOYMENT
---------------
The system is live and publicly accessible at:
    http://samews.42web.io

No login is required to view the platform.
Admin panel: http://samews.42web.io/admin.php (credentials in Section 4 below)


GITHUB REPOSITORY
-----------------
All source code is hosted on GitHub at:
    https://github.com/monicah-pirisi/capstone

The repository contains:
    - All PHP source files (backend, API, includes)
    - All frontend assets (HTML, CSS, JavaScript)
    - JSON data files (NDMA, KMD, indigenous indicators)
    - SQL schema file (samburu_ews.sql)
    - Documentation (docs/)


SYSTEM REQUIREMENTS
-------------------
To run this system locally you need:
    - PHP 8.0 or higher
    - MySQL 5.7 or higher
    - Apache web server (XAMPP recommended for local development)
    - A modern web browser (Chrome, Firefox, Edge)
    - Internet connection (for Chart.js CDN)


HOW TO INSTALL AND RUN LOCALLY
--------------------------------

Step 1 — Install XAMPP
    Download XAMPP from https://www.apachefriends.org
    Install and start both Apache and MySQL from the XAMPP Control Panel.

Step 2 — Clone or download the repository
    Option A (Git):
        Open a terminal and run:
        git clone https://github.com/monicah-pirisi/capstone.git
        Move the cloned folder into: C:\xampp\htdocs\capstone_web\

    Option B (Download):
        Download the ZIP from GitHub (Code > Download ZIP)
        Extract the folder into: C:\xampp\htdocs\capstone_web\

Step 3 — Create the database
    Open your browser and go to: http://localhost/phpmyadmin
    Click "New" in the left panel and create a database named: samburu_ews
    Select the samburu_ews database
    Click the "Import" tab
    Click "Choose File" and select: samburu_ews.sql (in the project root folder)
    Click "Go" to import all tables

Step 4 — Configure the database connection
    Open the file: config.php (in the project root)
    Set the following values:
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'samburu_ews');
        define('DB_USER', 'root');
        define('DB_PASS', '');   // leave blank for default XAMPP

Step 5 — Open the platform
    Open your browser and go to: http://localhost/capstone_web/
    The platform should load immediately.


HOW TO DEPLOY TO INFINITYFREE (LIVE HOSTING)
----------------------------------------------

Step 1 — Create an InfinityFree account at https://infinityfree.com
Step 2 — Create a subdomain (e.g. samews.42web.io)
Step 3 — In the Control Panel, open MySQL Databases and create a new database
Step 4 — Open phpMyAdmin, select the database, and import samburu_ews.sql
Step 5 — Update config.php with the InfinityFree database credentials:
            DB_HOST = your InfinityFree MySQL hostname
            DB_NAME = your database name (e.g. if0_41763079_samburu)
            DB_USER = your InfinityFree username
            DB_PASS = your database password
Step 6 — Use FileZilla (https://filezilla-project.org) to upload all files:
            FTP Host:     ftpupload.net
            FTP Username: your InfinityFree username
            FTP Port:     21
            Upload all files into the htdocs/ folder on the server


ADMIN PANEL
-----------
URL:      http://samews.42web.io/admin.php
          OR http://localhost/capstone_web/admin.php (local)
Password: admin123

The admin panel allows viewing of contact form submissions and feedback
stored in the MySQL database.

To change the admin password:
    Run in terminal: php -r "echo password_hash('yournewpassword', PASSWORD_BCRYPT);"
    Copy the output hash and replace the value of ADMIN_PASSWORD_HASH in config.php


PROJECT STRUCTURE
-----------------
capstone_web/
    index.php               Homepage
    current-alert.php       Live risk score and alert dashboard
    prototype.php           Integrated knowledge comparison interface
    scientific-data.php     KMD and NDMA data visualizations
    findings.php            Research findings dashboard
    indigenous-data.php     Indigenous indicator reference
    channels.php            Dissemination channel templates
    ussd-simulator.php      USSD phone simulator (*384#)
    stakeholders.php        Stakeholder profiles and actions
    resources.php           Education hub and methodology
    problem.php             Problem background page
    solution.php            Solution overview page
    admin.php               Admin panel (password protected)
    config.php              Database and site configuration
    samburu_ews.sql         MySQL database schema
    api/
        current-alert-data.php    Risk engine API endpoint
    includes/
        RiskEngine.php            Drought risk scoring engine
        DataRepository.php        JSON data loader
        Db.php                    Database abstraction layer
        header.php / footer.php   Shared layout components
        nav.php                   Navigation
    assets/
        css/                      Stylesheets
        js/                       JavaScript files
    data/
        ndma_latest.json          NDMA Samburu bulletin data
        kmd_summary.json          KMD monthly forecast data
        kmd_seasonal.json         KMD seasonal forecast history
        indigenous_indicators.json  Community indigenous indicators
        channels_content.json     Alert message templates
        ndma_history.json         4-month NDMA trend data
    docs/
        README.txt                This file
        SOFTWARE_MANUAL.md        Full user and technical manual


UPDATING DATA
-------------
The platform reads live data from JSON files in the data/ folder.
To update with a new NDMA or KMD bulletin:
    1. Open data/ndma_latest.json or data/kmd_summary.json
    2. Update the values from the latest official bulletin
    3. Save the file and re-upload it to the server via FileZilla


TECHNOLOGY STACK
----------------
    Backend:    PHP 8+ (Apache/XAMPP)
    Database:   MySQL (PDO prepared statements)
    Frontend:   HTML5, CSS3, Vanilla JavaScript
    Charts:     Chart.js v4.4.0 (CDN)
    Hosting:    InfinityFree (free PHP/MySQL shared hosting)
    Version Control: Git / GitHub


CONTACT
-------
Student: Monicah Lekupe
Email:   monicah.lekupe@ashesi.edu.gh
University: Ashesi University, Ghana
Year:    2026

================================================================================
