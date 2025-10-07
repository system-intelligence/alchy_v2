# Alchy V2 - Production-Ready Inventory Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.6-green.svg)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC.svg)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> A comprehensive, enterprise-grade inventory management system built with Laravel, Livewire, and modern PHP practices. Features role-based access control, real-time UI updates, and complete audit trails.

## 📸 Screenshots

### Dashboard Views
| Developer Dashboard | System Admin Dashboard | User Dashboard |
|-------------------|----------------------|---------------|
| Full system overview with user management | Inventory management and alerts | Personal expense tracking |

### Core Features
| Masterlist Management | Expense Tracking | Client Management |
|----------------------|------------------|------------------|
| Advanced inventory CRUD with bulk operations | Real-time expense monitoring | Client lifecycle management |

## � Table of Contents

- [🎯 Overview](#-overview)
- [✨ Features](#-features)
- [🚀 Installation](#-installation)
- [📊 Database Schema](#-database-schema)
- [🏗️ Architecture](#️-architecture)
- [👥 User Roles & Permissions](#-user-roles--permissions)
- [🔒 Security](#-security)
- [📚 Learning Guide](#-learning-guide)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

## 🎯 Overview

Alchy V2 is a comprehensive, production-ready inventory management system built with modern PHP technologies. This project serves as both a functional business application and an educational resource for learning advanced Laravel development patterns, real-time UI with Livewire, and enterprise-level software architecture.

### 🏗️ System Architecture

**Backend Stack:**
- **Laravel 12** - Full-stack PHP framework with MVC architecture
- **Livewire 3.6** - Real-time, reactive components without JavaScript complexity
- **Alpine.js** - Lightweight JavaScript for interactive UI elements
- **Tailwind CSS** - Utility-first CSS framework for responsive design

**Database & Storage:**
- **SQLite/MySQL** - Configurable database with Eloquent ORM
- **Base64 Blob Storage** - Embedded image storage for portability
- **Spatie Media Library** - Advanced file management for user avatars

**Security & Quality:**
- **Laravel Sanctum** - API authentication (extensible)
- **Role-Based Access Control (RBAC)** - Granular permission system
- **CSRF Protection** - Built-in Laravel security
- **Input Validation** - Comprehensive server-side validation
- **Audit Logging** - Complete operational history tracking

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

### Expense Tracking & Release Management
- **Material Release System**: Record inventory releases to clients with cost tracking
- **Real-time Stock Updates**: Automatic inventory quantity and status updates
- **Advanced Filtering**: Calendar-based date filtering with month/year selectors
- **Cost Management**: Per-unit cost calculation with total expense computation
- **Admin Controls**: Password-protected expense editing and client management
- **Audit Trail**: Complete logging of all release transactions

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

## 🏗️ Build Process: From Zero to Production

This section documents the complete development journey of Alchy V2, showing how a production-ready inventory management system was built from scratch using modern Laravel practices.

### Phase 1: Foundation Setup

#### 1.1 Laravel Installation with Authentication
```bash
# Create new Laravel project
composer create-project laravel/laravel alchy_v2
cd alchy_v2

# Install Laravel Breeze for authentication
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate
```

#### 1.2 Core Dependencies Installation
```bash
# Install Livewire for reactive components
composer require livewire/livewire

# Install Spatie Media Library for file management
composer require spatie/laravel-medialibrary

# Install additional development tools
composer require --dev pestphp/pest pestphp/pest-plugin-laravel
composer require --dev laravel/sail fakerphp/faker
```

#### 1.3 Database Configuration
Configure `.env` for your database (SQLite for development simplicity):
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Phase 2: Database Architecture

#### 2.1 User Roles Extension
```php
// Migration: add_role_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['developer', 'system_admin', 'user'])->default('user');
});
```

#### 2.2 Core Business Tables
```php
// Create inventories table
Schema::create('inventories', function (Blueprint $table) {
    $table->id();
    $table->string('brand');
    $table->text('description');
    $table->string('category');
    $table->integer('quantity')->default(0);
    $table->enum('status', ['normal', 'critical', 'out_of_stock'])->default('normal');
    $table->timestamps();
});

// Create clients table
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('branch');
    $table->timestamps();
});

// Create expenses table (junction table with business logic)
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
    $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
    $table->integer('quantity_used');
    $table->decimal('cost_per_unit', 10, 2);
    $table->decimal('total_cost', 10, 2);
    $table->timestamp('released_at');
    $table->timestamps();
});

// Create audit trail
Schema::create('histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('action'); // create, update, delete
    $table->string('model'); // inventory, client, expense, etc.
    $table->unsignedBigInteger('model_id');
    $table->json('changes'); // old/new values
    $table->timestamps();
});
```

#### 2.3 Feature Enhancements
```php
// Add minimum stock levels
Schema::table('inventories', function (Blueprint $table) {
    $table->integer('min_stock_level')->default(5)->after('quantity');
});

// Add user activity tracking
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('last_seen')->nullable()->after('email_verified_at');
});

// Add job order management to clients
Schema::table('clients', function (Blueprint $table) {
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->enum('job_type', ['service', 'installation'])->nullable();
    $table->enum('status', ['in_progress', 'settled'])->default('in_progress');
});

// Add image storage (base64 blobs for portability)
Schema::table('inventories', function (Blueprint $table) {
    $table->longText('image_blob')->nullable();
    $table->string('image_mime_type')->nullable();
    $table->string('image_filename')->nullable();
});
```

### Phase 3: Model Development

#### 3.1 User Model with RBAC
```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'last_seen'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'password' => 'hashed',
    ];

    // Role-based permissions
    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === 'system_admin';
    }

    public function hasPermission(string $permission): bool
    {
        return match ($permission) {
            'view_expenses' => false,  // Users can't view expenses
            'release_inventory' => true, // Users can release materials
            default => false,
        };
    }

    // Activity tracking
    public function updateLastSeen(): void
    {
        $this->update(['last_seen' => now()]);
    }

    // Avatar management with Spatie Media Library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: asset('images/default-avatar.png');
    }

    // Relationships
    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }
}
```

#### 3.2 Inventory Model with Business Logic
```php
class Inventory extends Model
{
    protected $fillable = [
        'brand', 'description', 'category', 'quantity',
        'status', 'min_stock_level', 'image_blob',
        'image_mime_type', 'image_filename'
    ];

    // Automatic uppercase formatting
    protected static function booted(): void
    {
        static::saving(function ($inventory) {
            $inventory->brand = strtoupper($inventory->brand);
            $inventory->description = strtoupper($inventory->description);
            $inventory->category = strtoupper($inventory->category);
        });
    }

    // Stock status calculations
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->min_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function updateStatus(): void
    {
        $this->status = $this->isOutOfStock() ? 'out_of_stock' :
                       ($this->isLowStock() ? 'critical' : 'normal');
        $this->save();
    }

    // Image handling
    public function storeImageAsBlob(string $imagePath): bool
    {
        $this->image_blob = base64_encode(file_get_contents($imagePath));
        $this->image_mime_type = mime_content_type($imagePath);
        $this->image_filename = basename($imagePath);
        return $this->save();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_blob ?
               "data:{$this->image_mime_type};base64,{$this->image_blob}" :
               asset('images/no-image.png');
    }

    // Relationships
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
```

#### 3.3 Client Model
```php
class Client extends Model
{
    protected $fillable = [
        'name', 'branch', 'start_date', 'end_date',
        'job_type', 'status', 'image_blob',
        'image_mime_type', 'image_filename'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Auto-uppercase formatting
    protected static function booted(): void
    {
        static::saving(function ($client) {
            $client->name = strtoupper($client->name);
            $client->branch = strtoupper($client->branch);
        });
    }

    // Status helpers
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'settled';
    }

    // Image handling (same as Inventory)
    public function storeImageAsBlob(string $imagePath): bool
    {
        $this->image_blob = base64_encode(file_get_contents($imagePath));
        $this->image_mime_type = mime_content_type($imagePath);
        $this->image_filename = basename($imagePath);
        return $this->save();
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->image_blob ?
               "data:{$this->image_mime_type};base64,{$this->image_blob}" :
               asset('images/default-logo.png');
    }

    // Relationships
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
```

#### 3.4 Expense Model (Business Logic Core)
```php
class Expense extends Model
{
    protected $fillable = [
        'client_id', 'inventory_id', 'quantity_used',
        'cost_per_unit', 'total_cost', 'released_at'
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'released_at' => 'datetime',
    ];

    // Auto-calculate total cost
    protected static function booted(): void
    {
        static::saving(function ($expense) {
            $expense->total_cost = $expense->quantity_used * $expense->cost_per_unit;
        });
    }

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
```

#### 3.5 History Model (Audit System)
```php
class History extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model', 'model_id', 'changes'
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    // Helper methods
    public function getActionDescriptionAttribute(): string
    {
        return ucfirst($this->action) . ' ' . ucfirst($this->model);
    }

    public function getModelNameAttribute(): string
    {
        return class_basename($this->model);
    }

    // Scoping
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Phase 4: Middleware & Security

#### 4.1 Role-Based Access Control Middleware
```php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasRole($role)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
```

#### 4.2 User Activity Tracking Middleware
```php
class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            auth()->user()->updateLastSeen();
        }

        return $next($request);
    }
}
```

### Phase 5: Livewire Components Development

#### 5.1 Masterlist Component (Core CRUD)
```php
class Masterlist extends Component
{
    public $inventories = [];
    public $search = '';
    public $selectedItems = [];
    public $showCreateModal = false;
    public $editingItem = null;

    // Form fields
    public $brand, $description, $category, $quantity, $min_stock_level;
    public $image;

    public function mount()
    {
        $this->loadInventories();
    }

    public function loadInventories()
    {
        $this->inventories = Inventory::query()
            ->when($this->search, function ($query) {
                $query->where('brand', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhere('category', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inventory) {
                $inventory->status = $inventory->isOutOfStock() ? 'out_of_stock' :
                                   ($inventory->isLowStock() ? 'critical' : 'normal');
                return $inventory;
            });
    }

    public function save()
    {
        $this->validate([
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
        ]);

        DB::transaction(function () {
            $inventory = Inventory::create([
                'brand' => strtoupper($this->brand),
                'description' => strtoupper($this->description),
                'category' => strtoupper($this->category),
                'quantity' => $this->quantity,
                'min_stock_level' => $this->min_stock_level,
            ]);

            // Handle image upload
            if ($this->image) {
                $inventory->storeImageAsBlob($this->image->getRealPath());
            }

            // Log to history
            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => ['new' => $inventory->toArray()],
            ]);
        });

        $this->resetForm();
        $this->loadInventories();
        session()->flash('message', 'Inventory item created successfully!');
    }

    public function releaseItems()
    {
        $this->validate([
            'releaseItems' => 'required|array|min:1',
            'releaseItems.*.client_id' => 'required|exists:clients,id',
            'releaseItems.*.quantity_used' => 'required|integer|min:1',
            'releaseItems.*.cost_per_unit' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            foreach ($this->releaseItems as $item) {
                $inventory = Inventory::find($item['inventory_id']);

                // Check stock availability
                if ($inventory->quantity < $item['quantity_used']) {
                    throw new \Exception("Insufficient stock for {$inventory->brand}");
                }

                // Create expense record
                Expense::create([
                    'client_id' => $item['client_id'],
                    'inventory_id' => $item['inventory_id'],
                    'quantity_used' => $item['quantity_used'],
                    'cost_per_unit' => $item['cost_per_unit'],
                    'released_at' => now(),
                ]);

                // Update inventory quantity
                $inventory->decrement('quantity', $item['quantity_used']);
                $inventory->updateStatus();

                // Log to history
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'release',
                    'model' => 'inventory',
                    'model_id' => $inventory->id,
                    'changes' => [
                        'quantity_released' => $item['quantity_used'],
                        'client_id' => $item['client_id'],
                        'cost_per_unit' => $item['cost_per_unit'],
                    ],
                ]);
            }
        });

        $this->loadInventories();
        $this->selectedItems = [];
        session()->flash('message', 'Items released successfully!');
    }

    public function deleteSelected()
    {
        $this->validate([
            'password' => 'required|current_password',
        ]);

        DB::transaction(function () {
            foreach ($this->selectedItems as $id) {
                $inventory = Inventory::find($id);

                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'delete',
                    'model' => 'inventory',
                    'model_id' => $id,
                    'changes' => ['old' => $inventory->toArray()],
                ]);

                $inventory->delete();
            }
        });

        $this->loadInventories();
        $this->selectedItems = [];
        session()->flash('message', 'Selected items deleted successfully!');
    }

    private function resetForm()
    {
        $this->brand = $this->description = $this->category = '';
        $this->quantity = $this->min_stock_level = null;
        $this->image = null;
        $this->showCreateModal = false;
        $this->editingItem = null;
    }

    public function render()
    {
        return view('livewire.masterlist');
    }
}
```

#### 5.2 Expenses Component (Client Management)
```php
class Expenses extends Component
{
    public $clients = [];
    public $selectedClient = null;
    public $showClientModal = false;
    public $filterMonth, $filterYear;

    // Client form
    public $clientName, $clientBranch, $startDate, $endDate, $jobType;

    public function mount()
    {
        $this->loadClients();
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    public function loadClients()
    {
        $this->clients = Client::with('expenses.inventory')
                              ->orderBy('created_at', 'desc')
                              ->get();
    }

    public function viewExpenses($clientId)
    {
        $this->selectedClient = Client::with(['expenses' => function ($query) {
            $query->with('inventory')
                  ->when($this->filterMonth && $this->filterYear, function ($q) {
                      $q->whereYear('released_at', $this->filterYear)
                        ->whereMonth('released_at', $this->filterMonth);
                  })
                  ->orderBy('released_at', 'desc');
        }])->find($clientId);
    }

    public function saveClient()
    {
        $this->validate([
            'clientName' => 'required|string|max:255',
            'clientBranch' => 'required|string|max:255',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'jobType' => 'nullable|in:service,installation',
        ]);

        $client = Client::create([
            'name' => strtoupper($this->clientName),
            'branch' => strtoupper($this->clientBranch),
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'job_type' => $this->jobType,
        ]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model' => 'client',
            'model_id' => $client->id,
            'changes' => ['new' => $client->toArray()],
        ]);

        $this->loadClients();
        $this->resetClientForm();
        session()->flash('message', 'Client created successfully!');
    }

    public function updateExpense($expenseId, $costPerUnit)
    {
        $expense = Expense::find($expenseId);
        $oldCost = $expense->cost_per_unit;

        $expense->update(['cost_per_unit' => $costPerUnit]);

        History::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model' => 'expense',
            'model_id' => $expenseId,
            'changes' => [
                'old' => ['cost_per_unit' => $oldCost],
                'new' => ['cost_per_unit' => $costPerUnit],
            ],
        ]);

        $this->viewExpenses($this->selectedClient->id);
    }

    private function resetClientForm()
    {
        $this->clientName = $this->clientBranch = $this->startDate = $this->endDate = $this->jobType = null;
        $this->showClientModal = false;
    }

    public function render()
    {
        return view('livewire.expenses');
    }
}
```

### Phase 6: Frontend Development

#### 6.1 Tailwind CSS Configuration
```javascript
// tailwind.config.js
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#3B82F6',
                secondary: '#6B7280',
                success: '#10B981',
                warning: '#F59E0B',
                danger: '#EF4444',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
```

#### 6.2 Main Layout Structure
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <main class="py-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
```

#### 6.3 Navigation Component
```blade
{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img class="h-8 w-auto" src="{{ asset('images/logos/alchy_logo.png') }}" alt="Alchy">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @can('view_masterlist')
                        <x-nav-link href="{{ route('masterlist') }}" :active="request()->routeIs('masterlist')">
                            {{ __('Masterlist') }}
                        </x-nav-link>
                    @endcan

                    @can('view_expenses')
                        <x-nav-link href="{{ route('expenses') }}" :active="request()->routeIs('expenses')">
                            {{ __('Expenses') }}
                        </x-nav-link>
                    @endcan

                    <x-nav-link href="{{ route('history') }}" :active="request()->routeIs('history')">
                        {{ __('History') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- User menu -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                            <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('profile.edit') }}">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="{{ route('logout') }}" method="POST">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
```

### Phase 7: Testing Implementation

#### 7.1 Feature Tests
```php
// tests/Feature/InventoryTest.php
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows system admin to create inventory', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);

    $response = $this->actingAs($admin)
                     ->post(route('inventory.store'), [
                         'brand' => 'TEST BRAND',
                         'description' => 'TEST DESCRIPTION',
                         'category' => 'TEST CATEGORY',
                         'quantity' => 10,
                         'min_stock_level' => 5,
                     ]);

    $response->assertRedirect();
    expect(Inventory::where('brand', 'TEST BRAND')->exists())->toBeTrue();
});

it('prevents regular user from accessing admin features', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)
                     ->get(route('masterlist'));

    $response->assertForbidden();
});

it('logs all inventory changes', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);

    $this->actingAs($admin);

    $inventory = Inventory::factory()->create();

    // Update inventory
    Livewire::test(Masterlist::class)
            ->call('update', $inventory->id, ['quantity' => 20]);

    expect(\App\Models\History::where('model', 'inventory')->count())->toBeGreaterThan(0);
});
```

#### 7.2 Unit Tests
```php
// tests/Unit/InventoryTest.php
test('inventory status updates correctly', function () {
    $inventory = new Inventory([
        'quantity' => 0,
        'min_stock_level' => 5,
    ]);

    expect($inventory->isOutOfStock())->toBeTrue();
    expect($inventory->isLowStock())->toBeFalse();
    expect($inventory->isInStock())->toBeFalse();

    $inventory->quantity = 3;
    expect($inventory->isLowStock())->toBeTrue();

    $inventory->quantity = 10;
    expect($inventory->isInStock())->toBeTrue();
});

test('inventory auto-formats text to uppercase', function () {
    $inventory = new Inventory();
    $inventory->brand = 'test brand';
    $inventory->description = 'test description';
    $inventory->category = 'test category';

    $inventory->save();

    expect($inventory->fresh()->brand)->toBe('TEST BRAND');
    expect($inventory->fresh()->description)->toBe('TEST DESCRIPTION');
    expect($inventory->fresh()->category)->toBe('TEST CATEGORY');
});
```

### Phase 8: Production Deployment

#### 8.1 Environment Configuration
```bash
# Production .env configuration
APP_NAME="Alchy V2"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alchy_prod
DB_USERNAME=alchy_user
DB_PASSWORD=secure_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

#### 8.2 Server Setup (Ubuntu/Nginx)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
CREATE DATABASE alchy_prod;
CREATE USER 'alchy_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON alchy_prod.* TO 'alchy_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Install Nginx
sudo apt install nginx

# Configure Nginx site
sudo nano /etc/nginx/sites-available/alchy
```

#### 8.3 Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/alchy_v2/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### 8.4 SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install snapd
sudo snap install core; sudo snap refresh core
sudo snap install --classic certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Set up auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### 8.5 Application Deployment
```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/yourusername/alchy_v2.git
cd alchy_v2

# Install dependencies
sudo composer install --optimize-autoloader --no-dev
sudo npm install && sudo npm run build

# Set up environment
sudo cp .env.example .env
sudo nano .env  # Configure production settings
sudo php artisan key:generate

# Database setup
sudo php artisan migrate --force
sudo php artisan db:seed --force

# Set permissions
sudo chown -R www-data:www-data /var/www/alchy_v2
sudo chmod -R 755 /var/www/alchy_v2
sudo chmod -R 775 /var/www/alchy_v2/storage

# Create symbolic link for storage
sudo php artisan storage:link

# Cache optimization
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache

# Enable site
sudo ln -s /etc/nginx/sites-available/alchy /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Set up queue worker (if using queues)
sudo php artisan queue:work --daemon --sleep=3 --tries=3

# Set up supervisor for queue workers
sudo apt install supervisor
sudo nano /etc/supervisor/conf.d/alchy-worker.conf
```

#### 8.6 Supervisor Configuration
```ini
[program:alchy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/alchy_v2/artisan queue:work --sleep=3 --tries=3 --max-jobs=1000
directory=/var/www/alchy_v2
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/alchy_v2/storage/logs/worker.log
```

#### 8.7 Backup Strategy
```bash
# Create backup script
sudo nano /var/www/alchy_v2/backup.sh
```

```bash
#!/bin/bash
# Database backup
mysqldump -u alchy_user -p'secure_password' alchy_prod > /var/www/backups/alchy_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf /var/www/backups/alchy_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/alchy_v2/storage

# Keep only last 30 days
find /var/www/backups -name "*.sql" -mtime +30 -delete
find /var/www/backups -name "*.tar.gz" -mtime +30 -delete
```

```bash
# Make executable and set up cron
sudo chmod +x /var/www/alchy_v2/backup.sh
sudo crontab -e
# Add: 0 2 * * * /var/www/alchy_v2/backup.sh
```

#### 8.8 Monitoring Setup
```bash
# Install monitoring tools
sudo apt install htop iotop

# Set up log rotation
sudo nano /etc/logrotate.d/alchy
```

```
/var/www/alchy_v2/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        /usr/bin/php /var/www/alchy_v2/artisan queue:restart
    endscript
}
```

### Phase 9: Real-World Usage Examples

#### Example 1: New Client Onboarding
**Scenario**: ABC Corporation needs installation services for networking equipment.

1. **System Admin creates client record**:
   - Name: ABC CORPORATION
   - Branch: MAIN BRANCH
   - Start Date: 2025-01-15
   - End Date: 2025-02-15
   - Job Type: Installation
   - Status: In Progress

2. **User releases materials**:
   - Selects 10 Cisco Switches from inventory
   - Assigns to ABC Corporation client
   - Sets cost per unit: $150.00
   - System automatically:
     - Creates expense records
     - Updates inventory quantities
     - Logs all transactions to audit trail

3. **Admin monitors progress**:
   - Views expense reports filtered by date
   - Tracks material costs and profitability
   - Updates client status when job completes

#### Example 2: Inventory Management Workflow
**Scenario**: Restocking critical networking equipment.

1. **Low stock alert triggers**:
   - System automatically updates status to "Critical" when quantity ≤ min_stock_level
   - Dashboard shows warning for items below threshold

2. **Admin adds new inventory**:
   - Brand: CISCO
   - Description: CATALYST 2960 SWITCH 24-PORT
   - Category: NETWORKING EQUIPMENT
   - Quantity: 50 units
   - Min Stock Level: 5
   - Upload product image

3. **Audit trail records**:
   - Creation timestamp
   - User who added items
   - Complete before/after data

#### Example 3: Financial Reporting
**Scenario**: Monthly expense analysis for Q1 2025.

1. **Filter expenses by date range**:
   - Month: January-March
   - Year: 2025

2. **Generate cost analysis**:
   - Total materials released: $45,230.00
   - Most used items: Cisco Switches (30%), Fiber Cable (25%)
   - Client profitability: ABC Corp (+15%), XYZ Ltd (-5%)

3. **Export data for accounting**:
   - CSV/Excel format with all transaction details
   - Includes client information and job types

#### Example 4: User Permission Management
**Scenario**: New employee joins the operations team.

1. **Developer creates user account**:
   - Role: User (limited permissions)
   - Can release materials but cannot view expenses
   - Cannot access administrative functions

2. **System enforces permissions**:
   - Dashboard shows only relevant information
   - Navigation hides restricted sections
   - API calls return 403 for unauthorized actions

#### Example 5: Audit Compliance
**Scenario**: Internal audit requires transaction history.

1. **Access complete audit trail**:
   - All CRUD operations logged
   - User attribution for every action
   - Before/after values stored as JSON

2. **Advanced filtering**:
   - By user, action type, model, date range
   - Export capabilities for compliance reporting

3. **Data integrity verification**:
   - Database transactions ensure consistency
   - Rollback on failures prevents partial updates

### Phase 10: Real-World Usage Examples

#### 📋 Complete Workflow: Client Project Management

**Business Scenario**: TechCorp Solutions requires network infrastructure setup for their new office building.

##### Step 1: Client Setup (System Admin)
```
Login as: admin@example.com
Navigate: Expenses → Create New Client

Form Data:
- Client Name: TECHCORP SOLUTIONS
- Branch: DOWNTOWN OFFICE
- Start Date: 2025-02-01
- End Date: 2025-03-15
- Job Type: Installation
- Upload Logo: techcorp_logo.png

Result: Client record created with ID #1001
```

##### Step 2: Inventory Preparation (System Admin)
```
Navigate: Masterlist → Add New Item

Item Details:
- Brand: CISCO
- Description: CATALYST 9200 SWITCH 48-PORT POE+
- Category: NETWORK SWITCHES
- Quantity: 25 units
- Min Stock Level: 3
- Upload Image: cisco_switch.jpg

Result: Inventory item created, status = "Normal"
```

##### Step 3: Material Release (Operations User)
```
Login as: user@example.com
Navigate: Masterlist → Select Items → Release to Client

Release Transaction:
- Selected Items: 5x Cisco Catalyst 9200 Switches
- Client: TechCorp Solutions (ID #1001)
- Cost per Unit: $850.00
- Password Confirmation: [user_password]

System Actions:
✓ Creates 5 expense records ($4,250 total)
✓ Updates inventory: 25 → 20 units
✓ Updates status: "Normal" (20 > 3)
✓ Logs to audit trail: "user@example.com released 5 Cisco switches to TechCorp"
```

##### Step 4: Expense Monitoring (System Admin)
```
Navigate: Expenses → Select Client → TechCorp Solutions

View Details:
- Total Released Items: 5 switches
- Total Cost: $4,250.00
- Release Date: 2025-02-05 14:30:00
- Filter by Month: February 2025

Edit Cost (if needed):
- Change cost per unit: $875.00
- System recalculates: $4,375.00 total
- Audit log: "Cost updated from $850 to $875"
```

##### Step 5: Project Completion (System Admin)
```
Navigate: Expenses → Edit Client

Update Status:
- Status: Settled
- End Date: 2025-02-28 (actual completion)

Result: Project marked complete, available for reporting
```

#### 📊 Reporting & Analytics Examples

##### Monthly Financial Report
```sql
-- Generated Report Query
SELECT
    c.name as client_name,
    c.job_type,
    COUNT(e.id) as items_released,
    SUM(e.total_cost) as total_expenses,
    DATE_FORMAT(e.released_at, '%Y-%m') as month
FROM expenses e
JOIN clients c ON e.client_id = c.id
WHERE YEAR(e.released_at) = 2025 AND MONTH(e.released_at) = 2
GROUP BY c.id, c.name, c.job_type, DATE_FORMAT(e.released_at, '%Y-%m')
ORDER BY total_expenses DESC;
```

**Sample Output:**
| Client Name | Job Type | Items | Total Cost | Month |
|-------------|----------|-------|------------|-------|
| TECHCORP SOLUTIONS | Installation | 5 | $4,375.00 | 2025-02 |
| GLOBAL SYSTEMS | Service | 12 | $8,540.00 | 2025-02 |
| STARTUP INC | Installation | 3 | $1,650.00 | 2025-02 |

##### Inventory Status Dashboard
```sql
-- Low Stock Alert Query
SELECT
    brand,
    description,
    quantity,
    min_stock_level,
    CASE
        WHEN quantity = 0 THEN 'Out of Stock'
        WHEN quantity <= min_stock_level THEN 'Critical'
        ELSE 'Normal'
    END as status
FROM inventories
WHERE quantity <= min_stock_level
ORDER BY quantity ASC;
```

**Alert Results:**
- ⚠️  CISCO ROUTER 2900 (Quantity: 2, Min: 5) - Critical
- ❌ TP-LINK SWITCH 8-PORT (Quantity: 0, Min: 3) - Out of Stock
- ⚠️  UBIQUITI AP (Quantity: 1, Min: 2) - Critical

#### 🔐 Security & Permission Examples

##### Role-Based Access Control Demonstration

**Developer User (developer@example.com)**:
```
✅ Full System Access:
   - View all users, clients, expenses
   - Create/edit/delete all records
   - Access audit logs
   - User management
   - System configuration

Dashboard Shows:
- Total Users: 15
- Recent Activities: All system events
- System Statistics: Complete overview
```

**System Admin (admin@example.com)**:
```
✅ Administrative Access:
   - Full inventory management
   - Client lifecycle management
   - Expense editing with password confirmation
   - Bulk operations
   - Reporting access

❌ Restricted:
   - Cannot manage users
   - Cannot view developer-only logs

Dashboard Shows:
- Inventory Count: 1,247 items
- Low Stock Alerts: 8 items critical
- Recent Expenses: $12,450 this month
```

**Regular User (user@example.com)**:
```
✅ Operational Access:
   - Release materials to clients
   - View assigned tasks
   - Personal activity history

❌ Restricted:
   - Cannot view expense details/costs
   - Cannot create/edit inventory
   - Cannot manage clients
   - No administrative functions

Dashboard Shows:
- Items Released Today: 15
- Active Projects: 3
- Recent Activity: Material releases only
```

#### 📱 API Integration Examples

##### Mobile App Synchronization
```php
// API Endpoint for Mobile App
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/inventory', function () {
        return Inventory::select('id', 'brand', 'description', 'quantity')
                        ->where('quantity', '>', 0)
                        ->orderBy('brand')
                        ->get();
    });

    Route::post('/api/release', function (Request $request) {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'client_id' => 'required|exists:clients,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Business logic for material release
        DB::transaction(function () use ($request) {
            $inventory = Inventory::find($request->inventory_id);

            if ($inventory->quantity < $request->quantity) {
                return response()->json(['error' => 'Insufficient stock'], 400);
            }

            Expense::create([
                'client_id' => $request->client_id,
                'inventory_id' => $request->inventory_id,
                'quantity_used' => $request->quantity,
                'cost_per_unit' => $inventory->estimated_cost ?? 0,
                'released_at' => now(),
            ]);

            $inventory->decrement('quantity', $request->quantity);
            $inventory->updateStatus();

            History::create([
                'user_id' => auth()->id(),
                'action' => 'release',
                'model' => 'inventory',
                'model_id' => $inventory->id,
                'changes' => ['released' => $request->quantity],
            ]);
        });

        return response()->json(['message' => 'Release successful']);
    });
});
```

##### Webhook Integration for External Systems
```php
// Webhook for Inventory Updates
class InventoryWebhook
{
    public static function sendLowStockAlert(Inventory $inventory)
    {
        $payload = [
            'event' => 'low_stock_alert',
            'inventory_id' => $inventory->id,
            'brand' => $inventory->brand,
            'description' => $inventory->description,
            'current_quantity' => $inventory->quantity,
            'min_stock_level' => $inventory->min_stock_level,
            'timestamp' => now()->toISOString(),
        ];

        Http::post('https://external-system.com/webhooks/inventory', $payload);
    }

    public static function sendStockUpdate(Inventory $inventory, $oldQuantity, $newQuantity)
    {
        $payload = [
            'event' => 'stock_updated',
            'inventory_id' => $inventory->id,
            'change' => $newQuantity - $oldQuantity,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'timestamp' => now()->toISOString(),
        ];

        Http::post('https://external-system.com/webhooks/inventory', $payload);
    }
}
```

#### 🧪 Testing Scenarios

##### End-to-End User Journey Test
```php
// tests/Feature/CompleteWorkflowTest.php
use App\Models\User;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('completes full client project workflow', function () {
    // Setup users
    $admin = User::factory()->create(['role' => 'system_admin']);
    $user = User::factory()->create(['role' => 'user']);

    // Admin creates client
    Livewire::actingAs($admin)
            ->test(Expenses::class)
            ->set('clientName', 'TEST CLIENT')
            ->set('clientBranch', 'MAIN BRANCH')
            ->call('saveClient');

    $client = Client::where('name', 'TEST CLIENT')->first();
    expect($client)->not->toBeNull();

    // Admin creates inventory
    Livewire::actingAs($admin)
            ->test(Masterlist::class)
            ->set('brand', 'TEST BRAND')
            ->set('description', 'TEST ITEM')
            ->set('category', 'TEST CATEGORY')
            ->set('quantity', 10)
            ->set('min_stock_level', 2)
            ->call('save');

    $inventory = Inventory::where('brand', 'TEST BRAND')->first();
    expect($inventory)->not->toBeNull();
    expect($inventory->quantity)->toBe(10);

    // User releases materials
    Livewire::actingAs($user)
            ->test(Masterlist::class)
            ->set('selectedItems', [$inventory->id])
            ->set('releaseItems.0.inventory_id', $inventory->id)
            ->set('releaseItems.0.client_id', $client->id)
            ->set('releaseItems.0.quantity_used', 3)
            ->set('releaseItems.0.cost_per_unit', 100.00)
            ->call('saveRelease');

    // Verify results
    $inventory->refresh();
    expect($inventory->quantity)->toBe(7);

    $expense = Expense::where('client_id', $client->id)
                     ->where('inventory_id', $inventory->id)
                     ->first();
    expect($expense)->not->toBeNull();
    expect($expense->quantity_used)->toBe(3);
    expect($expense->total_cost)->toBe(300.00);

    // Verify audit trail
    $history = \App\Models\History::where('model', 'inventory')
                                  ->where('model_id', $inventory->id)
                                  ->first();
    expect($history)->not->toBeNull();
    expect($history->action)->toBe('release');
});
```

##### Performance Load Testing
```php
// tests/Feature/PerformanceTest.php
it('handles bulk inventory operations efficiently', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);

    // Create 100 inventory items
    $startTime = microtime(true);
    Inventory::factory()->count(100)->create();
    $endTime = microtime(true);

    expect($endTime - $startTime)->toBeLessThan(2.0); // Should complete within 2 seconds

    // Test bulk release
    $inventories = Inventory::take(10)->get();
    $client = Client::factory()->create();

    $startTime = microtime(true);
    Livewire::actingAs($admin)
            ->test(Masterlist::class)
            ->set('selectedItems', $inventories->pluck('id')->toArray())
            ->set('releaseItems', $inventories->map(function ($inv) use ($client) {
                return [
                    'inventory_id' => $inv->id,
                    'client_id' => $client->id,
                    'quantity_used' => 1,
                    'cost_per_unit' => 50.00,
                ];
            })->toArray())
            ->call('saveRelease');
    $endTime = microtime(true);

    expect($endTime - $startTime)->toBeLessThan(5.0); // Should complete within 5 seconds
});
```

## 🚀 Production Deployment Guide

### Complete Production Setup Example

#### Scenario: Deploying to DigitalOcean Ubuntu Server

**Server Specifications:**
- Ubuntu 22.04 LTS
- 2GB RAM, 1 CPU, 50GB SSD
- Domain: alchy-inventory.com

#### Step 1: Server Preparation
```bash
# Connect to server
ssh root@your-server-ip

# Update system
apt update && apt upgrade -y

# Install required packages
apt install -y curl wget git unzip software-properties-common

# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.2 and extensions
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-sqlite3

# Install MySQL
apt install -y mysql-server
mysql_secure_installation

# Install Nginx
apt install -y nginx

# Install Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Certbot for SSL
apt install -y certbot python3-certbot-nginx
```

#### Step 2: Database Setup
```bash
# Create database and user
mysql -u root -p
```

```sql
-- Run in MySQL console
CREATE DATABASE alchy_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alchy_user'@'localhost' IDENTIFIED BY 'YourSecurePassword123!';
GRANT ALL PRIVILEGES ON alchy_prod.* TO 'alchy_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 3: Application Deployment
```bash
# Create web directory
mkdir -p /var/www/alchy
cd /var/www/alchy

# Clone repository (replace with your repo)
git clone https://github.com/yourusername/alchy_v2.git .
# OR upload via FTP/sftp

# Set proper permissions
chown -R www-data:www-data /var/www/alchy
chmod -R 755 /var/www/alchy
chmod -R 775 /var/www/alchy/storage
chmod -R 775 /var/www/alchy/bootstrap/cache

# Install PHP dependencies
composer install --optimize-autoloader --no-dev --no-interaction

# Install Node dependencies and build assets
npm install --production=false
npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 4: Environment Configuration
```bash
# Edit .env file
nano .env
```

```env
APP_NAME="Alchy V2 - Production"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://alchy-inventory.com

LOG_CHANNEL=stack
LOG_DEBIAN_HANDLER=errorlog
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alchy_prod
DB_USERNAME=alchy_user
DB_PASSWORD=YourSecurePassword123!

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@alchy-inventory.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### Step 5: Database Migration and Seeding
```bash
# Run migrations
php artisan migrate --force

# Seed with special users
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 6: Nginx Configuration
```bash
# Create Nginx site configuration
nano /etc/nginx/sites-available/alchy
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name alchy-inventory.com www.alchy-inventory.com;
    root /var/www/alchy/public;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    # Handle PHP files
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Handle static files with caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Handle Livewire uploads
    location ~ /livewire/upload-file {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

```bash
# Enable site
ln -s /etc/nginx/sites-available/alchy /etc/nginx/sites-enabled/

# Test configuration
nginx -t

# Restart Nginx
systemctl restart nginx
```

#### Step 7: SSL Certificate Setup
```bash
# Obtain SSL certificate
certbot --nginx -d alchy-inventory.com -d www.alchy-inventory.com

# Set up auto-renewal
crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### Step 8: Firewall Configuration
```bash
# Enable UFW firewall
ufw enable

# Allow SSH, HTTP, HTTPS
ufw allow ssh
ufw allow 'Nginx Full'

# Check status
ufw status
```

#### Step 9: Process Management with Supervisor
```bash
# Install Supervisor
apt install -y supervisor

# Create queue worker configuration
nano /etc/supervisor/conf.d/alchy-queue.conf
```

```ini
[program:alchy-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/alchy/artisan queue:work --sleep=3 --tries=3 --max-jobs=1000
directory=/var/www/alchy
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/alchy/storage/logs/queue.log
stopwaitsecs=30
```

```bash
# Reload Supervisor
supervisorctl reread
supervisorctl update
supervisorctl start alchy-queue:*
```

#### Step 10: Backup Strategy Implementation
```bash
# Create backup directory
mkdir -p /var/www/backups
chown www-data:www-data /var/www/backups

# Create backup script
nano /var/www/alchy/backup.sh
```

```bash
#!/bin/bash
# Alchy V2 Backup Script
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/www/backups"

# Database backup
mysqldump -u alchy_user -p'YourSecurePassword123!' alchy_prod > $BACKUP_DIR/alchy_db_$DATE.sql

# Files backup (excluding vendor and node_modules for size)
cd /var/www/alchy
tar -czf $BACKUP_DIR/alchy_files_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*.log' \
    --exclude='.git' \
    .

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

# Log backup completion
echo "Backup completed: $DATE" >> $BACKUP_DIR/backup.log
```

```bash
# Make executable
chmod +x /var/www/alchy/backup.sh

# Test backup
/var/www/alchy/backup.sh

# Set up daily cron job
crontab -e
# Add: 0 2 * * * /var/www/alchy/backup.sh
```

#### Step 11: Monitoring and Logging Setup
```bash
# Install monitoring tools
apt install -y htop iotop ncdu

# Configure log rotation
nano /etc/logrotate.d/alchy
```

```
/var/www/alchy/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        /usr/bin/supervisorctl restart alchy-queue:*
    endscript
}
```

#### Step 12: Performance Optimization
```bash
# PHP-FPM optimization
nano /etc/php/8.2/fpm/pool.d/www.conf
```

```
# Update these values based on your server resources:
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
```

```bash
# MySQL optimization
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```
# Add these optimizations:
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
query_cache_size = 64M
max_connections = 100
```

```bash
# Restart services
systemctl restart php8.2-fpm
systemctl restart mysql
systemctl restart nginx
supervisorctl restart all
```

#### Step 13: Security Hardening
```bash
# Disable root SSH login
nano /etc/ssh/sshd_config
# Set: PermitRootLogin no

# Install fail2ban
apt install -y fail2ban

# Configure fail2ban for SSH
nano /etc/fail2ban/jail.local
```

```ini
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
```

```bash
# Restart services
systemctl restart sshd
systemctl restart fail2ban
```

#### Step 14: Final Testing and Go-Live
```bash
# Test application
curl -I https://alchy-inventory.com

# Check logs for errors
tail -f /var/www/alchy/storage/logs/laravel.log

# Test user login
# Visit: https://alchy-inventory.com/login
# Use: developer@example.com / password

# Verify all features work:
# - User registration and login
# - Inventory CRUD operations
# - Client management
# - Material release workflow
# - Dashboard functionality
# - File uploads
```

### Post-Deployment Checklist

- [ ] Domain DNS configured correctly
- [ ] SSL certificate active and auto-renewing
- [ ] Database connection working
- [ ] File permissions correct
- [ ] Backups running daily
- [ ] Queue workers processing jobs
- [ ] Log rotation configured
- [ ] Firewall active
- [ ] Monitoring tools installed
- [ ] Performance optimized
- [ ] Security hardening applied
- [ ] All features tested in production

### Troubleshooting Common Issues

#### Database Connection Issues
```bash
# Check MySQL service
systemctl status mysql

# Test database connection
php artisan tinker
# Then run: DB::connection()->getPdo();
```

#### Permission Issues
```bash
# Fix storage permissions
chown -R www-data:www-data /var/www/alchy/storage
chmod -R 775 /var/www/alchy/storage

# Check PHP-FPM user
grep user /etc/php/8.2/fpm/pool.d/www.conf
```

#### Queue Worker Issues
```bash
# Check supervisor status
supervisorctl status

# View queue logs
tail -f /var/www/alchy/storage/logs/queue.log

# Restart workers
supervisorctl restart alchy-queue:*
```

#### SSL Issues
```bash
# Check certificate
certbot certificates

# Renew certificate
certbot renew

# Check Nginx configuration
nginx -t && systemctl reload nginx
```

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

## 📊 Models Architecture

### 👤 User Model
**Core authentication and authorization model with RBAC**

**Relationships:**
```php
- histories(): HasMany // Audit trail of user actions
```

**Key Methods:**
```php
- Role Checks: isDeveloper(), isSystemAdmin(), isUser()
- Permissions: hasRole($role), hasPermission($permission)
- Status: updateLastSeen(), isOnline(), getStatusAttribute()
- Media: getAvatarUrlAttribute(), registerMediaCollections()
```

**Security Features:**
- Password hashing with bcrypt
- Role-based permission matrix
- Online status tracking
- Avatar management with Spatie Media Library

### 🏢 Client Model
**Business client management with image storage**

**Relationships:**
```php
- expenses(): HasMany // All expenses for this client
```

**Key Methods:**
```php
- Image Management: storeImageAsBlob(), getLogoUrlAttribute()
- Status Checks: isActive(), isCompleted()
- Business Logic: Auto-uppercase formatting, enum validation
```

**Data Integrity:**
- Automatic uppercase conversion for names
- Job type validation (service/installation)
- Status tracking (in_progress/settled)

### 📦 Inventory Model
**Stock management with automated status updates**

**Relationships:**
```php
- expenses(): HasMany // Usage history
```

**Key Methods:**
```php
- Stock Management: isInStock(), isLowStock(), isOutOfStock()
- Status Updates: updateStatus(), getAvailableQuantity()
- Image Storage: Base64 blob storage for portability
```

**Business Rules:**
- Automatic status calculation based on quantity
- Minimum stock level monitoring
- Category-based organization

### 💰 Expense Model
**Financial transaction tracking**

**Relationships:**
```php
- client(): BelongsTo // Client relationship
- inventory(): BelongsTo // Inventory item used
```

**Key Methods:**
```php
- Cost Calculation: calculateTotalCost()
- Time-based Filtering: wasReleasedRecently($days)
```

**Data Validation:**
- Decimal precision for financial calculations
- Foreign key constraints
- Timestamp tracking

### 📋 History Model
**Complete audit trail system**

**Relationships:**
```php
- user(): BelongsTo // User who performed action
```

**Key Methods:**
```php
- Formatting: getActionDescriptionAttribute(), getModelNameAttribute()
- Scoping: byAction($action), byModel($model), byUser($userId)
```

**Audit Features:**
- JSON change storage
- Action categorization
- User attribution
- Model-specific logging

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

### 📋 Masterlist Component
**Advanced inventory management with real-time operations**

**Core Features:**
```php
- CRUD Operations: Create, read, update, delete inventory items
- Advanced Search: Real-time filtering by brand, description, category
- Bulk Operations: Multi-select with mass deletion capabilities
- Image Management: Base64 blob storage for inventory photos
- Material Release: Complex transaction system for client supplies
- Password Security: Confirmation required for edits and deletions
```

**Business Logic:**
```php
public function saveRelease(): void
{
    // Database transaction for data integrity
    DB::beginTransaction();
    try {
        // Create expense records
        // Update inventory quantities
        // Log all changes to history
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Release failed: ' . $e->getMessage());
    }
}
```

**Security Implementation:**
- Role-based access control (Admin/User permissions)
- Password confirmation for sensitive operations
- Transaction rollback on failures
- Comprehensive audit logging

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

## 👥 User Roles & Permissions

### 🛠️ Developer
**Highest privilege level with complete system access**
- **System Administration**: Full access to all features and data
- **User Management**: Create, edit, delete, and manage all user accounts
- **Audit Oversight**: View complete system logs and audit trails
- **Configuration**: Modify system settings and configurations
- **Debug Access**: View detailed error logs and system diagnostics

### 👨‍💼 System Admin
**Administrative control over business operations**
- **Inventory Management**: Full CRUD operations on inventory items
- **Bulk Operations**: Mass delete, bulk updates with password confirmation
- **Client Management**: Complete client lifecycle management
- **Expense Control**: Create, edit, and manage all expenses
- **Release Authorization**: Approve and process material releases
- **Reporting**: Access to comprehensive system statistics

### 👤 Standard User
**Operational role focused on material release**
- **Release Operations**: Record and process material releases to clients
- **Limited Access**: Cannot view expense details or administrative data
- **Profile Management**: Update personal information and avatar
- **Dashboard**: View operational statistics (no sensitive financial data)
- **Audit Compliance**: All actions are logged for accountability

### 🔐 Permission Matrix

| Feature | Developer | System Admin | User |
|---------|-----------|--------------|------|
| View Users | ✅ | ❌ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| View All Expenses | ✅ | ✅ | ❌ |
| Edit Expenses | ✅ | ✅ | ❌ |
| Manage Inventory | ✅ | ✅ | ❌ |
| Release Materials | ✅ | ✅ | ✅ |
| View Audit Logs | ✅ | ✅ | ❌ |
| System Config | ✅ | ❌ | ❌ |
| Password Confirmation Required | ❌ | ✅ (for edits/deletes) | ❌ |

## 🚀 Extending the System

### Adding New Features

#### 1. **New User Role**
```php
// In User.php
public function isManager(): bool
{
    return $this->role === 'manager';
}

// Permission logic
public function hasPermission(string $permission): bool
{
    return match ($permission) {
        'approve_expenses' => $this->isManager() || $this->isSystemAdmin(),
        // ... other permissions
    };
}
```

#### 2. **Additional Inventory Categories**
```php
// In Inventory.php
public const CATEGORIES = [
    'Bodega Room',
    'Alchy Room',
    'Warehouse A',
    'Warehouse B',
];
```

#### 3. **Email Notifications**
```php
// Add to Expense model
protected static function booted(): void
{
    static::created(function ($expense) {
        // Send notification to relevant users
        Notification::send(
            User::whereIn('role', ['system_admin', 'manager'])->get(),
            new ExpenseCreated($expense)
        );
    });
}
```

### Performance Optimizations

#### Database Indexing
```sql
-- Add to migration
$table->index(['client_id', 'released_at']);
$table->index(['inventory_id', 'status']);
```

#### Query Optimization
```php
// Eager loading for better performance
$clients = Client::with(['expenses.inventory'])->get();

// Selective column loading
$expenses = Expense::select('id', 'total_cost', 'released_at')
    ->where('client_id', $clientId)
    ->get();
```

### API Development
```php
// Add API routes for mobile app integration
Route::apiResource('inventories', InventoryController::class)
    ->middleware('auth:sanctum');

Route::get('/dashboard/stats', [ApiController::class, 'stats'])
    ->middleware('auth:sanctum');
```

## 📈 Production Deployment Checklist

- [ ] Environment variables configured for production
- [ ] Database migrations run on production
- [ ] File permissions set correctly
- [ ] SSL certificate installed
- [ ] Backup strategy implemented
- [ ] Monitoring and logging configured
- [ ] CDN setup for static assets (optional)
- [ ] Queue workers configured for background jobs

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

## 🔒 Security Features

### Authentication & Authorization
- **Multi-level RBAC**: Developer, System Admin, User roles with granular permissions
- **Password Confirmation**: Required for sensitive operations (delete, edit inventory)
- **Session Management**: Secure session handling with configurable lifetime
- **Email Verification**: Account verification for new registrations

### Data Protection
- **CSRF Protection**: Laravel's built-in CSRF tokens on all forms
- **Input Validation**: Comprehensive server-side validation with custom rules
- **SQL Injection Prevention**: Eloquent ORM with parameterized queries
- **XSS Protection**: Blade templating engine sanitizes output
- **Password Hashing**: Bcrypt hashing with configurable rounds

### Audit & Monitoring
- **Complete Audit Trail**: All CRUD operations logged with user context
- **Change Tracking**: Before/after values stored in JSON format
- **User Activity Monitoring**: Login/logout tracking and online status

## 📚 Learning Guide

### 🏗️ Architecture Patterns Demonstrated

#### 1. **Model-View-Controller (MVC) with Livewire**
```php
// Traditional Controller
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
}

// Livewire Component (Reactive MVC)
class Expenses extends Component
{
    public function viewExpenses($clientId): void
    {
        $this->selectedClient = Client::with('expenses')->find($clientId);
        // Reactive UI updates automatically
    }
}
```

#### 2. **Repository Pattern with Eloquent**
```php
// Models serve as repositories with business logic
class Inventory extends Model
{
    public function updateStatus(): void
    {
        $this->status = $this->isOutOfStock() ? 'out_of_stock' :
                       ($this->isLowStock() ? 'critical' : 'normal');
        $this->save();
    }
}
```

#### 3. **Observer Pattern for Audit Logging**
```php
// Automatic logging on model events
class History extends Model
{
    protected static function booted(): void
    {
        static::creating(function ($history) {
            // Log creation with context
        });
    }
}
```

### 🎯 Key Learning Concepts

#### **Livewire Component Lifecycle**
1. **Mount**: Initialize component data
2. **Render**: Return view with data
3. **Actions**: Handle user interactions
4. **Validation**: Server-side form validation
5. **Real-time Updates**: Automatic UI synchronization

#### **Database Relationships & Constraints**
```php
// One-to-Many with Foreign Keys
class Client extends Model
{
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}

// Polymorphic Relationships (Extensible)
class History extends Model
{
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

#### **Role-Based Permissions System**
```php
class User extends Model
{
    public function hasPermission(string $permission): bool
    {
        return match ($permission) {
            'view_expenses' => false,  // User can't view
            'release_inventory' => true, // User can release
            default => false,
        };
    }
}
```

### 🚀 Advanced Features to Study

#### **Real-time Form Validation**
```php
// Livewire enables real-time validation
public function updatedReleaseItems($value, $key): void
{
    $this->validateOnly($key, [
        'releaseItems.*.quantity_used' => 'required|integer|min:1',
    ]);
}
```

#### **Database Transactions for Data Integrity**
```php
public function saveRelease(): void
{
    DB::beginTransaction();
    try {
        // Multiple related database operations
        $expense = Expense::create([...]);
        $inventory->decrement('quantity', $quantity);

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### **Image Blob Storage Strategy**
```php
// Base64 encoding for portability
public function storeImageAsBlob(string $imagePath): bool
{
    $imageData = file_get_contents($imagePath);
    $this->image_blob = base64_encode($imageData);
    $this->image_mime_type = mime_content_type($imagePath);
    return $this->save();
}
```

### 📖 Study Path for Learners

1. **Beginner**: Start with basic CRUD operations in `Masterlist.php`
2. **Intermediate**: Study the permission system and role-based access
3. **Advanced**: Analyze the audit logging and transaction management
4. **Expert**: Extend the system with new features using established patterns

### 🔧 Development Best Practices Demonstrated

- **SOLID Principles**: Single responsibility, open/closed, etc.
- **DRY (Don't Repeat Yourself)**: Reusable components and methods
- **Error Handling**: Try-catch blocks with proper logging
- **Input Sanitization**: Automatic uppercase conversion for consistency
- **Type Hinting**: Full PHP 8.2 type declarations
- **Documentation**: Comprehensive PHPDoc comments

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes with proper testing
4. Submit a pull request

## 🎯 Project Summary

Alchy V2 represents a **production-ready, enterprise-level inventory management system** that demonstrates advanced Laravel development practices. This codebase serves as both a functional business application and a comprehensive learning resource for developers seeking to master modern PHP development.

### 🏆 Key Achievements

- **Complete RBAC System**: Multi-level role-based access control with granular permissions
- **Real-time UI**: Livewire-powered reactive interfaces without complex JavaScript
- **Audit Trail**: Comprehensive logging of all system activities
- **Data Integrity**: Database transactions and validation for business-critical operations
- **Security First**: Password confirmations, CSRF protection, and input sanitization
- **Scalable Architecture**: Clean separation of concerns with SOLID principles
- **Production Ready**: Error handling, logging, and performance optimizations

### 📚 Learning Outcomes

By studying this codebase, developers will learn:
- Advanced Eloquent ORM usage and relationships
- Livewire component development patterns
- Role-based permission systems
- Database transaction management
- Audit logging and compliance
- Image storage strategies (blob vs file system)
- Real-time form validation
- Clean code principles and documentation

### 🚀 Next Steps for Learners

1. **Clone and Setup**: Follow the installation guide
2. **Study Core Models**: Understand the business logic in each model
3. **Analyze Livewire Components**: Learn reactive UI patterns
4. **Extend Features**: Add new roles, permissions, or modules
5. **API Development**: Add REST API endpoints for mobile apps
6. **Testing**: Implement comprehensive test suites
7. **Deployment**: Learn production deployment strategies

### 🤝 Contributing

This project welcomes contributions that enhance functionality, improve code quality, or add educational value. Please ensure all changes maintain the established architectural patterns and include appropriate documentation.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

