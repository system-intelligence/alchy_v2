# Alchy V2 - Inventory Management System

## Overview

Alchy V2 is a comprehensive inventory management system built with Laravel 12, Livewire 3.6, and Tailwind CSS. It provides role-based access control for managing inventories, clients, expenses, and tracking operational history. The system supports image uploads for inventories and clients, real-time updates via Livewire, and includes features like stock level monitoring, expense tracking, and job order management.

### Key Technologies
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 3.6, Alpine.js, Tailwind CSS
- **Database**: MySQL/SQLite (configurable)
- **Authentication**: Laravel Breeze
- **Media Management**: Spatie Laravel Media Library
- **Testing**: Pest PHP

## Features

### User Management
- Role-based authentication (Developer, System Admin, User)
- Profile management with avatar uploads
- Online/offline status tracking
- Email verification and password reset

### Inventory Management
- CRUD operations for inventory items
- Stock level monitoring (normal, critical, out of stock)
- Image uploads (stored as base64 blobs)
- Bulk operations (delete, release)
- Search and filtering by status

### Client Management
- Client CRUD with job order details
- Job types (service, installation)
- Status tracking (in progress, settled)
- Logo/image uploads
- Date range management (start/end dates)

### Expense Tracking
- Record material releases to clients
- Cost calculation per unit
- Calendar-based filtering
- Expense editing for admins
- Automatic inventory stock updates

### History & Auditing
- Comprehensive logging of all CRUD operations
- User-specific history views
- Grid/table view modes
- Developer access to all logs

### Dashboard
- Role-based dashboards
- Quick stats and recent activities
- Developer: User management, system logs
- System Admin: Inventory counts, low stock alerts
- User: Personal expense history

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or SQLite database

### Setup Steps
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd alchy_v2
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy environment file and configure:
   ```bash
   cp .env.example .env
   ```
   Update database credentials and other settings in `.env`

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run database migrations:
   ```bash
   php artisan migrate
   ```

7. (Optional) Seed the database:
   ```bash
   php artisan db:seed
   ```

8. Build assets:
   ```bash
   npm run build
   ```
   Or for development:
   ```bash
   npm run dev
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

### Development Server
For concurrent development with hot reloading:
```bash
composer run dev
```
This runs the Laravel server, queue worker, and Vite dev server simultaneously.

## Database Schema

### Users Table
- `id` (bigint, primary)
- `name` (string)
- `email` (string, unique)
- `email_verified_at` (timestamp, nullable)
- `password` (string, hashed)
- `role` (enum: developer, system_admin, user, default: user)
- `last_seen` (timestamp, nullable)
- `timestamps`

### Inventories Table
- `id` (bigint, primary)
- `brand` (string, uppercase)
- `description` (text, uppercase)
- `category` (string, uppercase)
- `quantity` (integer, default: 0)
- `status` (enum: normal, critical, out_of_stock, default: normal)
- `min_stock_level` (integer, default: 5)
- `image_blob` (longText, nullable, base64)
- `image_mime_type` (string, nullable)
- `image_filename` (string, nullable)
- `timestamps`

### Clients Table
- `id` (bigint, primary)
- `name` (string, uppercase)
- `branch` (string, uppercase)
- `start_date` (date, nullable)
- `end_date` (date, nullable)
- `job_type` (enum: service, installation, nullable)
- `status` (enum: in_progress, settled, default: in_progress)
- `image_blob` (longText, nullable, base64)
- `image_mime_type` (string, nullable)
- `image_filename` (string, nullable)
- `timestamps`

### Expenses Table
- `id` (bigint, primary)
- `client_id` (bigint, foreign key to clients)
- `inventory_id` (bigint, foreign key to inventories)
- `quantity_used` (integer)
- `cost_per_unit` (decimal 10,2)
- `total_cost` (decimal 10,2)
- `released_at` (timestamp)
- `timestamps`

### Histories Table
- `id` (bigint, primary)
- `user_id` (bigint, foreign key to users)
- `action` (string: create, update, delete)
- `model` (string: inventory, client, expense, etc.)
- `model_id` (unsigned bigint)
- `changes` (json: old/new values)
- `timestamps`

### Media Table (Spatie Media Library)
- Standard media library fields for file management

## Models

### User Model
**Relationships:**
- Has many histories

**Key Methods:**
- `isDeveloper()`, `isSystemAdmin()`, `isUser()`
- `hasRole($role)`, `hasPermission($permission)`
- `updateLastSeen()`, `isOnline()`, `getStatusAttribute()`
- `getAvatarUrlAttribute()`

**Media Collections:**
- `avatar` (single file, image types)

### Client Model
**Relationships:**
- Has many expenses

**Key Methods:**
- `storeImageAsBlob($path)`, `getImageBlobAttribute()`
- `getLogoUrlAttribute()`, `hasImageBlob()`, `deleteImageBlob()`

**Mutators:**
- Auto-uppercase for `name` and `branch`
- Enum validation for `job_type`

### Inventory Model
**Relationships:**
- Has many expenses

**Key Methods:**
- `storeImageAsBlob($path)`, `getImageBlobAttribute()`
- `getImageUrlAttribute()`, `hasImageBlob()`, `deleteImageBlob()`

**Mutators:**
- Auto-uppercase for `brand`, `description`, `category`
- Enum validation for `status`

### Expense Model
**Relationships:**
- Belongs to client
- Belongs to inventory

### History Model
**Relationships:**
- Belongs to user

## Controllers

### ProfileController
- `edit(Request $request)`: Display profile edit form
- `update(ProfileUpdateRequest $request)`: Update user profile
- `destroy(Request $request)`: Delete user account with password confirmation

### Authentication Controllers (Breeze)
- Standard Laravel Breeze controllers for login, registration, password reset, email verification

## Livewire Components

### Dashboard
Displays role-based dashboard data:
- **Developer**: All users, recent history logs
- **System Admin**: Inventory count, low stock alerts
- **User**: Recent personal expenses

### Expenses
Manages client expenses with advanced features:
- Client selection and expense viewing
- Calendar-based date filtering
- Month/year filtering with custom dropdowns
- Expense editing (cost per unit)
- Client CRUD (name, branch, dates, job type, logo upload)
- Theme selection for calendar
- Password-protected client deletion

### Masterlist
Inventory management interface:
- CRUD operations for inventory items
- Search and status filtering
- Bulk selection and deletion
- Image upload for inventory items
- Material release to clients (creates expenses, updates stock)
- Duplicate prevention for releases
- Automatic status updates based on stock levels

### History
Audit log viewer:
- Grid/table view modes
- Role-based access (users see own logs, developers see all)
- Displays action, model, changes, and timestamps

### ImageUpload
Generic image upload component (used in forms)

### Profile/AvatarUpload
Avatar upload for user profiles

### Developer/UserManagement
User management for developers (assumed, not detailed in code)

## Routes

### Web Routes
- `/` → Redirect to dashboard or login
- `/dashboard` → Role-based dashboard view
- `/masterlist` → Inventory management
- `/history` → Audit logs
- `/expenses` → Client expenses
- `/developer/user-management` → User management (developer only)
- `/profile` → Profile edit (GET), update (PATCH), delete (DELETE)

### Authentication Routes
- `/login`, `/register`, `/forgot-password`, `/reset-password`
- `/verify-email`, `/confirm-password`
- `/logout`

## Middleware

### RoleMiddleware
Enforces role-based access control (likely applied to protected routes)

### UpdateLastSeen
Updates user's last seen timestamp on authenticated requests

## Views

### Layouts
- `app.blade.php`: Main application layout with navigation
- `guest.blade.php`: Layout for unauthenticated pages
- `navigation.blade.php`: Main navigation bar

### Components
- Reusable Blade components (buttons, inputs, modals, etc.)
- Heroicons integration via Blade UI Kit

### Livewire Views
- `livewire/dashboard.blade.php`: Dashboard content
- `livewire/expenses.blade.php`: Expenses interface
- `livewire/masterlist.blade.php`: Inventory list
- `livewire/history.blade.php`: History logs
- `livewire/image-upload.blade.php`: Image upload form
- `livewire/profile/avatar-upload.blade.php`: Avatar upload

### Authentication Views
- Standard Breeze auth views (login, register, etc.)

### Profile Views
- `profile/edit.blade.php`: Profile edit form
- `profile/partials/`: Profile form components

## Configuration

### Key Config Files
- `config/app.php`: Application configuration
- `config/database.php`: Database connections
- `config/filesystems.php`: File storage (local, public disks)
- `config/mail.php`: Email configuration
- `config/session.php`: Session settings

### Environment Variables
- Standard Laravel `.env` variables
- Database connection settings
- Mail configuration
- App key, debug mode, etc.

## User Roles & Permissions

### Developer
- Full system access
- View all users and system logs
- Manage all data
- Access to developer-specific features

### System Admin
- Manage inventories (CRUD, bulk operations)
- Manage clients (CRUD, job orders)
- Record and edit expenses
- View system statistics
- Access restricted to admin functions

### User
- View personal expense history
- Limited dashboard access
- No administrative functions
- Basic profile management

## Testing

Run tests with:
```bash
php artisan test
```

Or with Pest:
```bash
./vendor/bin/pest
```

## Deployment

1. Set up production environment (PHP 8.2+, web server)
2. Configure production `.env` file
3. Run migrations: `php artisan migrate`
4. Build assets: `npm run build`
5. Set proper file permissions
6. Configure web server (Apache/Nginx) to serve `public/` directory

## Security Features

- CSRF protection
- Input validation and sanitization
- Password hashing
- Email verification
- Role-based access control
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade templating

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes with proper testing
4. Submit a pull request

## License

This project is licensed under the MIT License.
