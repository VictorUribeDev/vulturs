# Vulturs - Sandwich Ordering System

A full-stack PHP web application for custom sandwich ordering with admin panel, built as a portfolio project.

## Features

- **Custom Sandwich Builder** - Interactive drag-and-drop interface to build sandwiches
- **Shopping Cart** - Session-based cart with real-time updates
- **Checkout System** - Order processing with validation
- **Admin Dashboard** - Sales overview, order management, inventory tracking
- **Order Management** - View, filter, and update order statuses
- **Ingredient Management** - CRUD for sandwich ingredients with categories
- **Reports** - Sales reports and analytics
- **Responsive Design** - Mobile-friendly with modern CSS

## Tech Stack

- **Backend**: PHP 8+ (PDO, Sessions)
- **Database**: MySQL/MariaDB
- **Frontend**: Vanilla JS, CSS3 (Flexbox/Grid), HTML5
- **Architecture**: MVC-like structure with API endpoints

## Project Structure

```
vulturs/
├── admin/                 # Admin panel
│   ├── dashboard.php      # Sales overview & stats
│   ├── orders.php         # Order management
│   ├── ingredientes.php   # Ingredient CRUD
│   ├── reports.php        # Sales reports
│   └── includes/          # Admin shared files
├── api/                   # REST-like endpoints
│   ├── add_to_cart.php
│   ├── add_custom_sandwich.php
│   └── get_cart.php
├── assets/
│   ├── css/styles.css     # Main stylesheet
│   └── js/                # JavaScript modules
├── includes/
│   └── db.php.example     # DB config template
├── index.php              # Homepage
├── menu.php               # Menu listing
├── sandwich_builder.php   # Custom sandwich builder
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── process_order.php      # Order processing
└── contact.php            # Contact form
```

## Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/vulturs.git
   cd vulturs
   ```

2. **Configure database**
   ```bash
   cp includes/db.php.example includes/db.php
   # Edit includes/db.php with your credentials
   ```

3. **Create database**
   ```sql
   CREATE DATABASE vultur_db;
   -- Import schema (create tables for: users, orders, order_items, ingredients, categories, etc.)
   ```

4. **Run with PHP built-in server** (for development)
   ```bash
   php -S localhost:8000
   ```

5. **Or configure with Apache/Nginx** pointing to the project root

## Admin Access

Navigate to `/admin/` - configure authentication in `admin/includes/header.php`

## Screenshots

*Add screenshots of: Homepage, Sandwich Builder, Cart, Admin Dashboard, Order Management*

## License

MIT License - feel free to use for learning or as a starting point for your own projects.

## Author

Built as a portfolio project demonstrating full-stack PHP development skills.