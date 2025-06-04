# Job Board API Documentation

## Authentication
All endpoints (except registration and login) require authentication via Laravel Sanctum.

**How to authenticate:**
- Register or login to receive a token.
- Include the token in the `Authorization` header:
  ```
  Authorization: Bearer <token>
  ```

---

## Endpoints

### 1. Registration & Login
| Method | Endpoint                        | Description                  |
|--------|---------------------------------|------------------------------|
| POST   | /api/v1/register/job-seeker     | Register as job seeker       |
| POST   | /api/v1/register/company        | Register as company          |
| POST   | /api/v1/login                   | Login (user or company)      |
| POST   | /api/v1/logout                  | Logout (auth required)       |

#### Example: Register Job Seeker
```json
POST /api/v1/register/job-seeker
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Example: Login
```json
POST /api/v1/login
{
  "email": "jane@example.com",
  "password": "password"
}
```

---

### 2. Job Postings
| Method | Endpoint                | Description                        |
|--------|-------------------------|------------------------------------|
| GET    | /api/v1/job-postings    | List all jobs (with filters)       |
| POST   | /api/v1/job-postings    | Create job post (company only)     |
| GET    | /api/v1/job-postings/{id}| View job post details              |
| PUT    | /api/v1/job-postings/{id}| Update job post (company only)     |
| DELETE | /api/v1/job-postings/{id}| Delete job post (company only)     |

#### Filters (as query params):
- `location`, `job_type`, `salary_min`, `salary_max`

#### Example: List Jobs with Filters
```
GET /api/v1/job-postings?location=Nairobi&job_type=full_time&salary_min=50000
```

#### Example: Create Job Post
```json
POST /api/v1/job-postings
{
  "company_id": 1,
  "title": "Backend Developer",
  "description": "Develop APIs...",
  "location": "Nairobi",
  "salary_min": 50000,
  "salary_max": 80000,
  "job_type": "full_time",
  "status": "active",
  "expires_at": "2024-12-31T23:59:59Z"
}
```

---

### 3. Job Applications
| Method | Endpoint                    | Description                        |
|--------|-----------------------------|------------------------------------|
| POST   | /api/v1/job-applications    | Apply to a job (job seeker only)   |
| GET    | /api/v1/job-applications    | List all applications (admin only) |
| GET    | /api/v1/job-applications/{id}| View application details           |
| PUT    | /api/v1/job-applications/{id}| Update application (owner only)    |
| DELETE | /api/v1/job-applications/{id}| Delete application (owner only)    |

#### Example: Apply to Job
```json
POST /api/v1/job-applications
{
  "job_posting_id": 1,
  "cover_letter": "I am interested in this job.",
  "resume_path": "https://example.com/resume.pdf"
}
```

---

### 4. Dashboards
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | /api/v1/dashboard/company/applications           | Company: view all applications for their jobs (company only) |
| GET    | /api/v1/dashboard/job-seeker/applications        | Job seeker: view all their applications (job seeker only)    |

#### 4.1 Company Dashboard: View All Applications
- **Endpoint:** `/api/v1/dashboard/company/applications`
- **Method:** GET
- **Auth Required:** Yes (company user)
- **Description:** Returns a paginated list of all job applications submitted to jobs posted by the authenticated company user.
- **Query Params:** `per_page` (optional, default: 15)

**Example Request:**
```
GET /api/v1/dashboard/company/applications?per_page=10
Authorization: Bearer <token>
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": { "id": 5, "name": "Frank Miller", ... },
      "job_posting": { "id": 2, "title": "Backend Developer", ... },
      "cover_letter": "I am passionate about backend development...",
      "status": "pending",
      "applied_at": "2024-06-10T12:34:56Z"
    }
    // ...
  ],
  "links": { ... },
  "meta": { ... }
}
```

#### 4.2 Job Seeker Dashboard: View Own Applications
- **Endpoint:** `/api/v1/dashboard/job-seeker/applications`
- **Method:** GET
- **Auth Required:** Yes (job seeker user)
- **Description:** Returns a paginated list of all job applications submitted by the authenticated job seeker.
- **Query Params:** `per_page` (optional, default: 15)

**Example Request:**
```
GET /api/v1/dashboard/job-seeker/applications?per_page=10
Authorization: Bearer <token>
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "job_posting": { "id": 2, "title": "Backend Developer", ... },
      "cover_letter": "I am passionate about backend development...",
      "status": "pending",
      "applied_at": "2024-06-10T12:34:56Z"
    }
    // ...
  ],
  "links": { ... },
  "meta": { ... }
}
```

---

### 5. Swagger UI & OpenAPI
- **Local:** http://localhost:8000/swagger
- The documentation is generated from the OpenAPI spec (`openapi.yaml`/`resources/swagger/openapi.json`).
- If you update the OpenAPI spec, refresh the Swagger UI page to see the latest docs.
- **Note:** In production, access to Swagger UI may be restricted for security. See `app/Providers/SwaggerUiServiceProvider.php` for details.

---

## Error Handling
- Validation errors: HTTP 422 with error details
- Unauthorized: HTTP 401/403
- Not found: HTTP 404
- Rate limit exceeded: HTTP 429

---

## Notes
- All request/response bodies are JSON.
- Use the `Authorization: Bearer <token>` header for all protected endpoints.
- See API Resources and Form Requests in the codebase for detailed validation rules and response structures.
- All job posting fields: `company_id`, `title`, `description`, `location`, `salary_min`, `salary_max`, `job_type`, `status`, `expires_at`, `created_at`, `updated_at`. 