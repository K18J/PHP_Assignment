# Comment Management System (CMS)

A robust and modular comment management system built with Laravel 12. This application provides a complete solution for managing comments on web pages with features like spam detection, admin approval workflows, and a RESTful API.

## 🚀 Features

### Core Functionality
- **Page Management**: Create, read, update, and delete pages
- **Comment Management**: Full CRUD operations for comments
- **Nested Comments**: Support for threaded comment discussions
- **Comment Moderation**: Approve/reject comments with admin privileges
- **Spam Detection**: Automatic spam filtering using middleware
- **Admin Dashboard**: Web-based interface for managing comments
- **RESTful API**: Complete API for integration with other systems

### Technical Features
- **Modular Architecture**: CMS module organized separately from core application
- **Repository Pattern**: Clean separation of data access logic
- **Middleware Protection**: Spam detection and admin authentication
- **Database Seeding**: Sample data for testing and development
- **API Documentation**: Postman collection included

## Project Structure

Comment_Management_System/
├── Modules/
│   └── Cms/
│       ├── Entities/          # Eloquent Models
│       ├── Http/
│       │   ├── Controllers/   # API Controllers
│       │   ├── Middleware/    # Custom Middleware
│       │   └── Requests/      # Form Requests
│       ├── Repositories/      # Data Access Layer
│       ├── Resources/         # API Resources
│       └── Routes/            # Module Routes
├── routes/
│   ├── api.php               # API route registration
│   └── web.php               # Web routes
└── resources/
    └── views/                # Blade templates


<img width="1311" height="442" alt="image" src="https://github.com/user-attachments/assets/7b10e0eb-2744-49fe-8259-2ffa2d43a076" />
<img width="1608" height="896" alt="image" src="https://github.com/user-attachments/assets/e5c48c3d-4aa6-4e06-a28e-0a5eda177761" />
