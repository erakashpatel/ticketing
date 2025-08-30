# Smart Ticket & Dashboard
**By: Akash**

A production-style single-page application that lets a help-desk team submit support tickets, queue AI classification jobs, view/filter tickets, and see analytics dashboard.

## Features

### Backend (Laravel 11, PHP 8.2)
- ✅ **Models & Database**: Tickets table with id, subject/title, body/description, status enum (A/C/H/X), category, confidence, explanation, notes
- ✅ **API Endpoints**: Complete REST API with filtering, search, and pagination
- ✅ **AI Integration**: OpenAI GPT-3.5 classification with fallback to random categories
- ✅ **Queue System**: Asynchronous ticket classification using Laravel Jobs
- ✅ **Rate Limiting**: 10 classification requests per minute per user
- ✅ **Bulk Classification**: Artisan command for bulk ticket processing
- ✅ **Analytics**: Stats endpoint with ticket counts by status and category

### Frontend (Vue 3 SPA, Options API)
- ✅ **Ticket Management**: Create, view, edit, delete tickets with modals
- ✅ **Filtering & Search**: Real-time filtering by status and text search
- ✅ **AI Classification**: One-click ticket classification with loading states
- ✅ **Dashboard**: Analytics cards and charts (pie chart for status, bar chart for categories)
- ✅ **CSV Export**: Export filtered ticket lists to CSV
- ✅ **Pagination**: Client-side pagination for ticket lists
- ✅ **Responsive Design**: BEM CSS methodology without frameworks

## Setup Instructions

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 16+ and npm
- MySQL (XAMPP default) or SQLite
- OpenAI API key (optional, for real AI classification)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ticketing
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure environment variables**
   Edit `.env` file:
   ```env
   # Database (MySQL with XAMPP)
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ticketSystem
   DB_USERNAME=root
   DB_PASSWORD=

   # OpenAI Configuration (optional)
   OPENAI_API_KEY=your_openai_api_key_here
   OPENAI_CLASSIFY_ENABLED=true
   
   # Queue Configuration
   QUEUE_CONNECTION=database
   ```

6. **Database setup**
   ```bash
   php artisan migrate --seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the application**
   ```bash
   # Option 1: Development with hot reload
   npm run dev &
   php artisan serve &
   php artisan queue:work &

   # Option 2: Production build
   php artisan serve
   php artisan queue:work --daemon
   ```

9. **Access the application**
   - Frontend: http://localhost:8000
   - Login credentials: admin@example.com / password

## Demo Access

### Quick Start
Once the application is running, you can immediately test all features:

**Login Credentials:**
- **Email:** `admin@example.com`
- **Password:** `password`

**Available Features:**
- ✅ **Create Tickets** - Submit new support tickets
- ✅ **AI Classification** - Automatic ticket categorization
- ✅ **View/Edit Tickets** - Full CRUD operations
- ✅ **Dashboard Analytics** - Charts and statistics
- ✅ **CSV Export** - Download filtered ticket data
- ✅ **Real-time Filtering** - Search and status filters

**Demo Data:**
- 100 sample tickets with realistic content
- 75 tickets with internal notes
- AI-classified categories with confidence scores
- Mixed ticket statuses (Active, Completed, On Hold, Cancelled)

**Demo User Permissions:**
- **Full Access** - Can create, edit, delete, and classify any ticket
- **No Restrictions** - Bypasses authorization checks for demo purposes
- **AI Classification** - Can run classification on any ticket (rate limited: 10 per minute)
- **All Operations** - Create, read, update, delete tickets and notes

**Note:** This is a demo setup with simplified permissions. In production, proper role-based access control would be implemented.

10. **Test AI classification**
    ```bash
    # Classify tickets in bulk (uses random classification if OpenAI is disabled)
    php artisan tickets:bulk-classify --limit=10
    ```

## API Documentation

### Authentication
All API endpoints require bearer token authentication via Laravel Sanctum.

### Main Endpoints

#### Tickets
- `GET /api/v1/tickets` - List tickets with filtering and pagination
- `POST /api/v1/tickets` - Create new ticket
- `GET /api/v1/tickets/{id}` - Get ticket details
- `PATCH /api/v1/tickets/{id}` - Update ticket
- `DELETE /api/v1/tickets/{id}` - Delete ticket
- `POST /api/v1/tickets/{id}/classify` - Queue AI classification (rate limited)

#### Analytics
- `GET /api/v1/tickets/stats` - Get ticket statistics for dashboard

### Query Parameters
- `?title=*search*` - Filter by title (supports wildcards)
- `?status=A,C` - Filter by status codes
- `?page=2` - Pagination
- `?sort=title,-created_at` - Sorting (prefix with - for descending)

## Architecture Decisions

### Backend Design
- **Laravel 11**: Modern PHP framework with kernel-less structure
- **Sanctum**: API authentication for SPA
- **MySQL**: Robust database with XAMPP for easy local development
- **Queue Jobs**: Asynchronous AI processing to avoid blocking requests
- **Rate Limiting**: Prevents API abuse and controls OpenAI costs
- **Resource Classes**: Consistent API response formatting

### Frontend Design
- **Vue 3 Options API**: Explicit and familiar syntax for team development
- **Vue Router**: Client-side routing for SPA experience
- **Chart.js**: Simple charting library for analytics
- **BEM CSS**: Maintainable styling methodology
- **Component Architecture**: Reusable ticket components

### AI Integration
- **OpenAI GPT-3.5**: Cost-effective model for text classification
- **Fallback System**: Random classification when OpenAI is disabled
- **Confidence Scores**: Help users understand AI predictions
- **Human Override**: Users can manually change categories

## Development Commands

### Laravel
```bash
# Run tests
php artisan test

# Clear caches
php artisan cache:clear
php artisan config:clear

# Database operations
php artisan migrate:fresh --seed
php artisan tinker

# Queue operations
php artisan queue:work
php artisan queue:failed
php artisan queue:retry all
```

### Frontend
```bash
# Development with hot reload
npm run dev

# Production build
npm run build

# Linting (if configured)
npm run lint
```

### Bulk Operations
```bash
# Classify all unclassified tickets
php artisan tickets:bulk-classify

# Force re-classify all tickets
php artisan tickets:bulk-classify --force

# Limit classification batch size
php artisan tickets:bulk-classify --limit=50
```

## Assumptions & Trade-offs

### Assumptions Made
1. **User Authentication**: Simplified login system for demo purposes
2. **Single Tenant**: No multi-organization support
3. **MySQL with XAMPP**: Easy setup with familiar local development environment
4. **English Only**: AI classification optimized for English text
5. **Basic Permissions**: Simple user-based access control

### Trade-offs
1. **ULID vs Auto-increment**: Kept auto-increment IDs for simplicity
2. **OpenAI vs Local AI**: Chose OpenAI for quality, but added fallback for cost control
3. **Client-side vs Server-side Pagination**: Client-side for better UX with small datasets
4. **Inline Editing vs Modal**: Used modals for consistency but could add inline editing
5. **Real-time vs Polling**: Used manual refresh instead of WebSocket for simplicity

## What I'd Do With More Time

### Features
1. **Real-time Updates**: WebSocket integration for live ticket updates
2. **Advanced Search**: Full-text search with Elasticsearch
3. **File Attachments**: Support for ticket attachments and images
4. **Email Integration**: Email notifications and email-to-ticket creation
5. **Advanced Analytics**: Time-series data, SLA tracking, agent performance

### Technical Improvements
1. **Testing**: Comprehensive test suite (unit, integration, E2E)
2. **CI/CD**: Automated testing and deployment pipeline
3. **Performance**: Database indexing, query optimization, caching
4. **Security**: Rate limiting, input validation, audit logging
5. **Monitoring**: Application performance monitoring and error tracking

### UX Enhancements
1. **Dark Mode**: User preference for dark/light themes
2. **Keyboard Shortcuts**: Power user navigation shortcuts
3. **Bulk Operations**: Multi-select and bulk actions on tickets
4. **Saved Filters**: User-defined filter presets
5. **Customizable Dashboard**: Drag-and-drop dashboard widgets
6. **Advanced Charts**: More detailed analytics and reporting

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

**Demo Data:**
- 100 sample tickets with realistic content
- 75 tickets with internal notes
- AI-classified categories with confidence scores
- Mixed ticket statuses (Active, Completed, On Hold, Cancelled)

**Demo User Permissions:**
- **Full Access** - Can create, edit, delete, and classify any ticket
- **No Restrictions** - Bypasses authorization checks for demo purposes
- **AI Classification** - Can run classification on any ticket (rate limited: 10 per minute)
- **All Operations** - Create, read, update, delete tickets and notes

**Note:** This is a demo setup with simplified permissions. In production, proper role-based access control would be implemented.
