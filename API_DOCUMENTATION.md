# Digital Voters List API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All protected endpoints require Bearer token authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your-token}
```

### Getting a Token

1. Login using the `/api/auth/login` endpoint
2. Copy the `token` from the response
3. Include it in subsequent requests

---

## Endpoints

### Authentication

#### Login
```
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "admin@voterslist.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@voterslist.com",
    "role": "superadmin",
    "ward_id": null,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|abc123..."
}
```

**Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

#### Get Authenticated User
```
GET /api/auth/user
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@voterslist.com",
    "role": "superadmin",
    "ward": null
  }
}
```

---

#### Logout
```
POST /api/auth/logout
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### Wards (Superadmin Only)

#### List Wards
```
GET /api/wards
```

**Query Parameters:**
- `search` (optional) - Search by name or ward number
- `per_page` (optional, default: 15) - Items per page

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Ward 1",
      "ward_number": "WARD001",
      "panchayat": "Panchayat A",
      "description": "First ward area",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```

---

#### Create Ward
```
POST /api/wards
```

**Request Body:**
```json
{
  "name": "Ward 3",
  "ward_number": "WARD003",
  "panchayat": "Panchayat C",
  "description": "Third ward area"
}
```

**Validation:**
- `name`: required|string|max:255
- `ward_number`: required|string|unique:wards,ward_number
- `panchayat`: required|string|max:255
- `description`: nullable|string

**Response (201):**
```json
{
  "message": "Ward created successfully",
  "ward": {
    "id": 3,
    "name": "Ward 3",
    "ward_number": "WARD003",
    "panchayat": "Panchayat C",
    "description": "Third ward area",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

#### Get Ward
```
GET /api/wards/{id}
```

**Response (200):**
```json
{
  "id": 1,
  "name": "Ward 1",
  "ward_number": "WARD001",
  "panchayat": "Panchayat A",
  "description": "First ward area",
  "users": [ ... ],
  "voters": [ ... ],
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

---

#### Update Ward
```
PUT /api/wards/{id}
```

**Request Body:** (all fields optional)
```json
{
  "name": "Updated Ward Name",
  "ward_number": "WARD001UPDATED",
  "panchayat": "Updated Panchayat",
  "description": "Updated description"
}
```

**Response (200):**
```json
{
  "message": "Ward updated successfully",
  "ward": { ... }
}
```

---

#### Delete Ward
```
DELETE /api/wards/{id}
```

**Response (200):**
```json
{
  "message": "Ward deleted successfully"
}
```

---

### Users (Superadmin Only)

#### List Users
```
GET /api/users
```

**Query Parameters:**
- `role` (optional) - Filter by role
- `ward_id` (optional) - Filter by ward
- `search` (optional) - Search by name or email
- `per_page` (optional, default: 15)

**Response (200):** Paginated list of users

---

#### Create User
```
POST /api/users
```

**Request Body:**
```json
{
  "name": "New Worker",
  "email": "newworker@voterslist.com",
  "password": "password123",
  "role": "worker",
  "ward_id": 1,
  "phone": "1234567890"
}
```

**Validation:**
- `name`: required|string|max:255
- `email`: required|email|unique:users,email
- `password`: required|string|min:8
- `phone`: nullable|string|max:20
- `role`: required|in:superadmin,team_lead,booth_agent,worker
- `ward_id`: nullable|exists:wards,id (required for team_lead, booth_agent, worker)

**Response (201):**
```json
{
  "message": "User created successfully",
  "user": { ... }
}
```

---

### Voters

#### List Voters
```
GET /api/voters
```

**Query Parameters:**
- `serial_number` (optional) - Filter by serial number
- `ward_id` (optional) - Filter by ward
- `panchayat` (optional) - Filter by panchayat
- `status` (optional, boolean) - Filter by voted status
- `per_page` (optional, default: 15)

**Access Control:**
- Superadmin: All voters
- Team Lead/Booth Agent: Voters in their ward
- Worker: Only assigned voters

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "serial_number": "VOTER001",
      "ward_id": 1,
      "panchayat": "Panchayat A",
      "image_path": "voters/1234567890_abc123.jpg",
      "status": false,
      "created_by": 1,
      "ward": { ... },
      "creator": { ... },
      "assignment": {
        "worker": { ... },
        "remark": "Some remark"
      },
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```

---

#### Create Voter (Superadmin Only)
```
POST /api/voters
Content-Type: multipart/form-data
```

**Request Body:**
- `serial_number` (required, string, unique)
- `ward_id` (required, exists:wards,id)
- `panchayat` (required, string, max:255)
- `image` (optional, file, max:2048KB, mimes:jpg,jpeg,png)

**Example (cURL):**
```bash
curl -X POST http://localhost:8000/api/voters \
  -H "Authorization: Bearer {token}" \
  -F "serial_number=VOTER001" \
  -F "ward_id=1" \
  -F "panchayat=Panchayat A" \
  -F "image=@/path/to/image.jpg"
```

**Response (201):**
```json
{
  "message": "Voter created successfully",
  "voter": { ... }
}
```

---

#### Get Voter
```
GET /api/voters/{id}
```

**Access Control:** Based on VoterPolicy

**Response (200):** Voter details

---

#### Update Voter (Superadmin Only)
```
PUT /api/voters/{id}
Content-Type: multipart/form-data
```

**Request Body:** (all fields optional)
- `serial_number` (string, unique, except current)
- `ward_id` (exists:wards,id)
- `panchayat` (string, max:255)
- `image` (file, max:2048KB, mimes:jpg,jpeg,png)

---

#### Delete Voter (Superadmin Only)
```
DELETE /api/voters/{id}
```

**Response (200):**
```json
{
  "message": "Voter deleted successfully"
}
```

---

#### Update Voter Status (Team Lead / Booth Agent)
```
PATCH /api/voters/{id}/status
```

**Request Body:**
```json
{
  "status": true
}
```

**Validation:**
- `status`: required|boolean

**Response (200):**
```json
{
  "message": "Voter status updated successfully",
  "voter": { ... }
}
```

---

#### Update Remark (Worker)
```
PATCH /api/voters/{id}/remark
```

**Request Body:**
```json
{
  "remark": "Voter has some issue with ID proof"
}
```

**Validation:**
- `remark`: required|string|max:500

**Response (200):**
```json
{
  "message": "Remark updated successfully",
  "voter": { ... }
}
```

**Note:** Worker can only update remark for assigned voters.

---

#### Assign Voter to Worker (Team Lead)
```
POST /api/voters/{id}/assign
```

**Request Body:**
```json
{
  "worker_id": 5
}
```

**Validation:**
- `worker_id`: required|exists:users,id

**Response (200):**
```json
{
  "message": "Voter assigned to worker successfully",
  "voter": { ... }
}
```

**Note:** Only team lead of the voter's ward can assign.

---

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

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Voter] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "serial_number": [
      "The serial number has already been taken."
    ],
    "ward_id": [
      "The selected ward id is invalid."
    ]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server Error"
}
```

---

## Role-Based Access Summary

| Action | Superadmin | Team Lead | Booth Agent | Worker |
|--------|-----------|-----------|-------------|--------|
| Create Ward | ✅ | ❌ | ❌ | ❌ |
| Create User | ✅ | ❌ | ❌ | ❌ |
| Create Voter | ✅ | ❌ | ❌ | ❌ |
| View All Voters | ✅ | ❌ | ❌ | ❌ |
| View Ward Voters | ✅ | ✅ | ✅ | ❌ |
| View Assigned Voters | N/A | N/A | N/A | ✅ |
| Update Voter | ✅ | ❌ | ❌ | ❌ |
| Update Voter Status | ✅ | ✅ | ✅ | ❌ |
| Update Remark | ❌ | ❌ | ❌ | ✅ (assigned only) |
| Assign to Worker | ✅ | ✅ | ❌ | ❌ |

---

## Image Upload

### Specifications
- **Max Size:** 2MB
- **Formats:** JPG, JPEG, PNG
- **Auto Resize:** Max 800x800px (maintains aspect ratio)
- **Storage:** Configurable (Local or S3)
  - **Local:** `storage/app/public/voters/` (default)
  - **S3:** Direct upload to S3 bucket
- **URL:** Automatically generated based on storage type
  - **Local:** `http://localhost:8000/storage/voters/{filename}`
  - **S3:** `https://bucket-name.s3.amazonaws.com/voters/{filename}`
- **Configuration:** Set `VOTER_IMAGE_DISK=s3` in `.env` to use S3

### Example Request
```bash
curl -X POST http://localhost:8000/api/voters \
  -H "Authorization: Bearer {token}" \
  -F "serial_number=VOTER001" \
  -F "ward_id=1" \
  -F "panchayat=Panchayat A" \
  -F "image=@/path/to/voter-photo.jpg"
```

---

## Pagination

All list endpoints support pagination using Laravel's paginator.

### Default
- **Per Page:** 15 items

### Query Parameters
- `per_page`: Number of items per page (1-100)

### Response Structure
```json
{
  "data": [ ... ],
  "links": {
    "first": "http://localhost:8000/api/voters?page=1",
    "last": "http://localhost:8000/api/voters?page=10",
    "prev": null,
    "next": "http://localhost:8000/api/voters?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "http://localhost:8000/api/voters",
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

---

## Testing

### Import Postman Collection
1. Import `Voters_List_API.postman_collection.json` into Postman
2. Set `base_url` variable to your API URL
3. Login to get token (auto-saved to collection variable)
4. Test all endpoints

### Run Tests
```bash
php artisan test
```

---

## Support

For issues and questions, please refer to the main README.md file.
