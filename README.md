# 1. Clone repository
1- git clone [repository-url]
2- cd invoice-app

# 2. Install dependencies
1- composer install
2- npm install 
3- npm run dev

# 3. Configure environment
1- cp .env.example .env
2- php artisan key:generate

# 4. Set up database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_app
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Serve application
php artisan serve
