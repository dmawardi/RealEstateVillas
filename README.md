# RealEstate Application

A modern real estate property management application built with Laravel and Vue.js, featuring property listings, booking management, favorites, and email notifications.

## Features

- 🏠 Property listings and management
- 📅 Booking system with availability management
- ❤️ Favorites functionality
- 📧 Email notifications for bookings and contact inquiries
- 🗺️ Google Maps integration
- 📱 Responsive design with Tailwind CSS
- 🔐 User authentication and authorization

## Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Vue.js 3 with Inertia.js
- **CSS**: Tailwind CSS
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **Testing**: Pest PHP
- **Build Tool**: Vite
- **Type Safety**: TypeScript

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm/yarn
- SQLite (or MySQL/PostgreSQL)

## Installation

### 1. Clone the repository
```bash
git clone <your-repository-url>
cd RealEstate
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Install Node.js dependencies
```bash
npm install
```

### 4. Environment setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure your environment
Edit `.env` file with your settings:
```env
APP_NAME="RealEstate App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite is default, no additional config needed)
DB_CONNECTION=sqlite

# Google Maps (required for maps functionality)
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

# Mail configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Database setup
```bash
# Create SQLite database file (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
```

## Development

### Running in Development Mode

#### Option 1: Using Laravel's built-in server
```bash
# Start the Laravel backend
php artisan serve

# In a separate terminal, start the Vite dev server for frontend
npm run dev

# To start both at once in Laravel 12
composer run dev
```

#### Option 2: Using Laravel Sail (Docker)
```bash
# Start all services
./vendor/bin/sail up -d

# Start Vite dev server
./vendor/bin/sail npm run dev
```

The application will be available at:
- **Local server**: http://localhost:8000
- **Sail (Docker)**: http://localhost

### Building for Production
```bash
# Build frontend assets
npm run build

# Build with SSR support
npm run build:ssr
```

## Testing

This project uses **Pest PHP** for testing.

### Running Tests
```bash
# Run all tests
php artisan test

# With Sail
./vendor/bin/sail test

# Run specific test file
php artisan test tests/Feature/PropertyTest.php

# Run tests with coverage
php artisan test --coverage

# Watch mode (re-run tests on file changes)
php artisan test --watch
```

### Test Categories
- **Feature Tests**: End-to-end functionality tests
- **Unit Tests**: Individual component tests

### Writing Tests
Tests are located in the `tests/` directory:
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests

Example test:
```php
test('user can create a property', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/properties', [
            'title' => 'Beautiful Villa',
            'description' => 'A stunning villa...',
            'price' => 250000,
        ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('properties', ['title' => 'Beautiful Villa']);
});
```

## Code Quality

### Formatting and Linting
```bash
# Format Vue/JS/TS code
npm run format

# Check formatting
npm run format:check

# Lint and fix JavaScript/TypeScript/Vue files
npm run lint

# Format PHP code (Laravel Pint)
./vendor/bin/pint

# Check PHP code style
./vendor/bin/pint --test
```

### Static Analysis
```bash
# Run PHPStan (if configured)
./vendor/bin/phpstan analyse
```

## Key Artisan Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Generate IDE helper files (if installed)
php artisan ide-helper:generate

# Create a new controller
php artisan make:controller PropertyController

# Create a new model with migration
php artisan make:model Property -m

# Create a new test
php artisan make:test PropertyTest

# Run queue workers
php artisan queue:work
```

## Server Deployment

### PHP Configuration for File Uploads

Update your `php.ini` file to handle larger file uploads:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 30
```

Check current settings:
```bash
php -i | grep -E "(upload_max_filesize|post_max_size|max_file_uploads|memory_limit)"
```

Find and edit php.ini:
```bash
# Find php.ini location
php --ini

# Edit the file (common locations)
sudo nano /etc/php/8.2/apache2/php.ini
sudo nano /etc/php/8.2/fpm/php.ini

# Restart web server
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### Production Deployment Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Run `npm run build`
6. Configure web server (Apache/Nginx)
7. Set up SSL certificate
8. Configure database backups
9. Set up monitoring and logging

## Environment Variables

Key environment variables to configure:

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_URL` | Application URL | Yes |
| `DB_*` | Database configuration | Yes |
| `MAIL_*` | Email service configuration | For notifications |
| `GOOGLE_MAPS_API_KEY` | Google Maps integration | For map features |

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use Prettier for JavaScript/Vue formatting
- Write tests for new features
- Update documentation as needed

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@yourdomain.com or create an issue in this repository.