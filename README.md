# Database Fundamentals Group Project

### To Start
1. **Clone the Repository**
   ```bash
   # Open terminal
   # For Mac:
   cd /Applications/XAMPP/xamppfiles/htdocs
   # OR for Windows:
   cd C:\xampp\htdocs
   
   git clone https://github.com/roham-123/database_project.git
   ```

2. **Set Up Database**
   - Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create new database named 'AuctionDB'
   - Import database schema:
     - Click 'Import' tab
     - Select `AuctionDB.sql` from the repository
     - Click 'Go'

3. **Configure Database Connection**
   - Verify config.php settings match your setup:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'AuctionDB');
     ```

4. **Access the Website**
   - Open [http://localhost/database_project](http://localhost/database_project)

## 💻 Development Workflow
1. **Before Starting Work**
   ```bash
   git pull origin main
   ```

2. **Making Changes**
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

3. **If Push is Rejected**
   ```bash
   git pull origin main
   # resolve any conflicts
   git push origin main
   ```

## 📁 Project Structure
```
database_project/
├── css/              # Stylesheets
├── js/               # JavaScript files
├── config.php        # Database configuration
├── header.php        # Site header
├── footer.php        # Site footer
├── index.php         # Homepage
├── register.php      # User registration
└── [other files]     # Additional functionality
```

## 🔑 Core Features
1. User Registration/Roles (10%)
2. Auction Creation (10%)
3. Search/Browse Functionality (15%)
4. Bidding System (15%)
5. Additional Features (30%)

## 📅 Important Dates
- Deadline: 02/12/2024

## ⚠️ Common Issues
1. **White Screen**
   - Check error logs in XAMPP
   - Verify PHP error reporting is enabled

2. **Database Connection Failed**
   - Ensure MySQL is running
   - Check database credentials in config.php

3. **Permission Issues**
   - Set proper folder permissions:
     ```bash
     chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/database_project
     ```

## 🤝 Contributing
1. Pull latest changes
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request
