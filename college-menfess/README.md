# College Menfess - Anonymous Message Platform

A beautiful, pink-themed platform for college students to share anonymous messages and songs with their batchmates. Built with PHP, MySQL, and Tailwind CSS.

## Features

- **User Authentication**
  - Secure registration and login system
  - Batch-specific accounts (2022, 2023, 2024)
  - Profile management with password change

- **Message System**
  - Anonymous message posting
  - Batch-specific visibility
  - Spotify song integration
  - Like and comment functionality

- **Batch Separation**
  - Dedicated spaces for each batch (2022, 2023, 2024)
  - Filtered message viewing
  - Batch-specific statistics

- **Modern UI/UX**
  - Pink-themed design
  - Responsive layout
  - Mobile-friendly interface
  - Modern typography with Google Fonts
  - Icon integration with Font Awesome

## Tech Stack

- **Backend**
  - PHP 7.4+
  - MySQL 5.7+
  - Apache/Nginx

- **Frontend**
  - HTML5
  - Tailwind CSS
  - JavaScript
  - Google Fonts
  - Font Awesome Icons

- **APIs**
  - Spotify API for song integration

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/college-menfess.git
   cd college-menfess
   ```

2. **Database Setup**
   - Create a MySQL database named 'college_menfess'
   - Import the database structure from config/database.php
   - Update database credentials in config/database.php:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'college_menfess');
     ```

3. **Configuration**
   - Update site configuration in config/config.php
   - Set up Spotify API credentials:
     ```php
     define('SPOTIFY_CLIENT_ID', 'your_spotify_client_id');
     define('SPOTIFY_CLIENT_SECRET', 'your_spotify_client_secret');
     ```

4. **Web Server Setup**
   - Configure your web server to point to the project directory
   - Enable mod_rewrite for Apache
   - Ensure .htaccess is enabled
   - Set proper file permissions:
     ```bash
     chmod 755 -R college-menfess
     chmod 777 -R college-menfess/uploads  # If implementing file uploads
     ```

## Directory Structure

```
college-menfess/
├── config/
│   ├── database.php   # Database configuration
│   └── config.php     # Site configuration
├── pages/
│   ├── register.php   # User registration
│   ├── login.php      # User login
│   ├── submit.php     # Message submission
│   ├── browse.php     # Message browsing
│   ├── profile.php    # User profile
│   └── logout.php     # Logout handler
├── includes/
│   ├── header.php     # Common header
│   └── footer.php     # Common footer
├── .htaccess          # URL rewriting rules
└── index.php          # Main entry point
```

## Security Features

- Password hashing using PHP's password_hash()
- CSRF protection with tokens
- SQL injection prevention with prepared statements
- XSS protection through input sanitization
- Secure session handling
- Security headers in .htaccess

## Usage

1. **Registration**
   - Visit /register
   - Enter username, email, password
   - Select batch year (2022, 2023, or 2024)

2. **Posting Messages**
   - Login to your account
   - Click "New Message"
   - Enter recipient name
   - Write your message
   - Add a song from Spotify
   - Choose batch visibility
   - Toggle anonymous posting

3. **Browsing Messages**
   - Visit /browse
   - Filter by batch year
   - Search for specific messages
   - Like and comment on messages

4. **Profile Management**
   - Update profile information
   - Change password
   - View your sent messages
   - Manage preferences

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Tailwind CSS for the beautiful UI components
- Font Awesome for the icon set
- Google Fonts for typography
- Spotify API for music integration

## Support

For support, email support@college-menfess.com or open an issue in the repository.
