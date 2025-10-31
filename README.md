# Digital Voters List API

A production-ready Laravel API-only application for managing digital voters list with role-based access control. This system supports multiple user roles (Superadmin, Team Lead, Booth Agent, Worker) with different permissions and access levels.

## Features

- **Role-Based Authentication** - Superadmin, Team Lead, Booth Agent, and Worker roles
- **Ward Management** - Create and manage electoral wards
- **Voter Management** - Full CRUD operations with image upload
- **Voter Assignment** - Team Leads can assign voters to Workers
- **Status Tracking** - Mark voters as voted/unvoted
- **Remark System** - Workers can add notes/remarks for assigned voters
- **Image Upload** - Voter photo upload with automatic resizing
- **Search & Filters** - Filter voters by serial number, ward, panchayat, and status
- **Pagination** - All list endpoints support pagination
- **Comprehensive Authorization** - Policies enforce role-based access

## Tech Stack

- Laravel 10
- Laravel Sanctum (API Authentication)
- Spatie Laravel Permission (Role & Permission Management)
- Intervention Image (Image Processing)
- MySQL Database
- PHPUnit (Testing)

## System Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- GD or Imagick extension for image processing

## Installation

### 1. Clone and Navigate
```bash
cd voters-list-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voters_list
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Seed Database
```bash
php artisan db:seed
```

This creates:
- Superadmin: `admin@voterslist.com` / `password123`
- Sample wards, team leads, booth agents, and workers

### 6. Link Storage
```bash
php artisan storage:link
```

This creates a symbolic link for public access to uploaded images.

### 7. Start Development Server
```bash
php artisan serve
```

API will be available at `http://localhost:8000`

## Role Hierarchy & Permissions

### Superadmin
- Full system access
- Create/edit/delete wards
- Create/edit/delete users (assign roles)
- Create/edit/delete voters
- View all voters across all wards

### Team Lead
- Assigned to one ward
- View all voters in assigned ward
- Mark voters as voted/unvoted
- Assign voters to workers
- View workers and their assigned voters

### Booth Agent
- Assigned to one ward
- View all voters in assigned ward
- Mark voters as voted/unvoted
- Cannot assign voters to workers

### Worker
- Assigned to one ward
- View only assigned voters
- Add/update remarks for assigned voters
- Cannot mark voters as voted/unvoted

## API Endpoints

### Authentication

#### Login
```
POST /api/auth/login
```
**Body:**
```json
{
  "email": "admin@voterslist.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": { ... },
  "token": "1|..."
}
```

#### Logout
```
POST /api/auth/logout
Headers: Authorization: Bearer {token}
```

#### Get Authenticated User
```
GET /api/auth/user
Headers: Authorization: Bearer {token}
```

### Wards (Superadmin Only)

- `GET /api/wards` - List all wards
- `POST /api/wards` - Create ward
- `GET /api/wards/{id}` - Get ward details
- `PUT /api/wards/{id}` - Update ward
- `DELETE /api/wards/{id}` - Delete ward

### Users (Superadmin Only)

- `GET /api/users` - List all users
- `POST /api/users` - Create user
- `GET /api/users/{id}` - Get user details
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Voters

#### List Voters
```
GET /api/voters
Query Parameters:
  - serial_number (optional)
  - ward_id (optional)
  - panchayat (optional)
  - status (optional, boolean)
  - per_page (optional, default: 15)
```

#### Create Voter (Superadmin Only)
```
POST /api/voters
Content-Type: multipart/form-data

Body:
  - serial_number: string (required, unique)
  - ward_id: integer (required)
  - panchayat: string (required)
  - image: file (optional, max 2MB, jpg/jpeg/png)
```

#### Get Voter
```
GET /api/voters/{id}
```

#### Update Voter (Superadmin Only)
```
PUT /api/voters/{id}
```

#### Delete Voter (Superadmin Only)
```
DELETE /api/voters/{id}
```

#### Update Voter Status (Team Lead / Booth Agent)
```
PATCH /api/voters/{id}/status
Body: { "status": true }
```

#### Update Remark (Worker)
```
PATCH /api/voters/{id}/remark
Body: { "remark": "Some remark text" }
```

#### Assign Voter to Worker (Team Lead)
```
POST /api/voters/{id}/assign
Body: { "worker_id": 1 }
```

## Example Requests

### Create Voter with Image (cURL)
```bash
curl -X POST http://localhost:8000/api/voters \
  -H "Authorization: Bearer {token}" \
  -F "serial_number=VOTER001" \
  -F "ward_id=1" \
  -F "panchayat=Panchayat A" \
  -F "image=@/path/to/image.jpg"
```

### Filter Voters
```bash
curl -X GET "http://localhost:8000/api/voters?ward_id=1&status=true&per_page=20" \
  -H "Authorization: Bearer {token}"
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test:
```bash
php artisan test --filter AuthTest
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "serial_number": ["The serial number has already been taken."]
  }
}
```

## Image Handling

- Images are automatically resized to max 800x800px (maintaining aspect ratio)
- Supports both **local storage** and **Amazon S3** (configurable via `VOTER_IMAGE_DISK` in `.env`)
- **Local Storage** (default): Stored in `storage/app/public/voters/`
- **S3 Storage**: Images uploaded directly to your S3 bucket
- Supported formats: JPG, JPEG, PNG
- Max file size: 2MB
- Image URLs are automatically generated based on storage type
- See `S3_CONFIGURATION.md` for S3 setup instructions

## Security Features

- Token-based authentication (Sanctum)
- Role-based authorization (Policies)
- Password hashing (bcrypt)
- CSRF protection
- Input validation
- SQL injection protection (Eloquent ORM)

## Deployment Checklist

- [ ] `.env` configured with production values
- [ ] Database migrated (`php artisan migrate`)
- [ ] Sanctum configured
- [ ] Admin user seeded
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Image upload tested
- [ ] Policies and role checks verified
- [ ] All API tests passing
- [ ] CORS configured if needed
- [ ] Production caching enabled

## License

MIT

## Support

For issues and questions, please create an issue in the repository.
