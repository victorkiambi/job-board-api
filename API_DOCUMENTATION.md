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
  "title": "Backend Developer",
  "description": "Develop APIs...",
  "location": "Nairobi",
  "salary_min": 50000,
  "salary_max": 80000,
  "job_type": "full_time"
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
  "cover_letter": "I am interested in this job."
}
```

---

### 4. Dashboards
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | /api/v1/dashboard/company/applications           | Company: view all applications     |
| GET    | /api/v1/dashboard/job-seeker/applications        | Job seeker: view own applications  |

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