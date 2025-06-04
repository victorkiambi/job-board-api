# Job Board API

A RESTful API for a simple job board built with Laravel 12. The system allows companies to post jobs and users (job seekers) to apply. The project follows clean code, scalable architecture, and Laravel best practices.

## Features

- **Authentication**: Laravel Sanctum-based, with separate registration/login for job seekers and companies.
- **Job Posting**: Companies can create, update, delete, and list their own job posts.
- **Job Listing**: Job seekers can view and filter available jobs (by location, job type, salary range).
- **Job Applications**: Job seekers can apply to jobs (one application per job per user enforced).
- **Dashboards**:
  - Companies: View all applications for their job posts.
  - Job seekers: View jobs they've applied to.
- **Rate Limiting**: Prevents abuse of job application endpoint.
- **Logging**: Logs who applied to what job and when.
- **Validation & Authorization**: Uses Form Requests and Policies for security and data integrity.

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- SQLite/MySQL/PostgreSQL (default: SQLite)
- Node.js & npm (for asset compilation, optional)

### Installation
1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd job-board-api
   ```
2. **Install PHP dependencies:**
   ```bash
   composer install
   ```
3. **Copy and configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env as needed (DB, mail, etc.)
   ```
4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```
5. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```

### Seed Data
After running the seeders, your database will include realistic, interconnected data for immediate testing:

- **Company User**
  - Name: Alice Johnson
  - Email: alice@acmetech.com
  - Password: password123
  - User type: company
  - Associated with: Acme Tech Solutions

- **Company**
  - Name: Acme Tech Solutions
  - Description: A leading provider of innovative tech solutions.
  - Website: https://acmetech.com
  - Location: New York, NY
  - Associated user: Alice Johnson

- **Job Posting**
  - Title: Backend Developer
  - Company: Acme Tech Solutions
  - Description: Join our team to build scalable backend APIs and services.
  - Location: Remote
  - Salary: $70,000 - $120,000
  - Type: full_time
  - Status: active

- **Job Seeker**
  - Name: Frank Miller
  - Email: frank.miller@email.com
  - Password: password123
  - User type: job_seeker
  - Profile: PHP, Laravel, REST APIs

- **Sample Application**
  - Frank Miller has already applied to the Backend Developer job posting with a cover letter and resume.

You can use these accounts to log in and test company/job seeker flows immediately.

6. **(Optional) Install JS dependencies and build assets:**
   ```bash
   npm install
   npm run dev
   ```

### Running the API
```bash
php artisan serve
```
The API will be available at `http://localhost:8000`.

## Production Deployment (Fly.io)

The application is deployed on Fly.io and can be accessed here:

- [https://job-board-api-still-shape-9561.fly.dev/](https://job-board-api-still-shape-9561.fly.dev/)

**Note:** If the site does not load on the first attempt, try again. This is a known issue with cold starts on Fly.io—sometimes the app needs a moment to start up if it has been idle.

## Docker Support

A `Dockerfile` is present in the project root for containerized development or deployment.

### Basic Usage

Build the image:
```bash
docker build -t job-board-api .
```

Run the container:
```bash
docker run --env-file .env -p 8000:8000 job-board-api
```

You may need to adjust volumes, ports, or environment variables for your setup.

## Testing
- Run all tests:
  ```bash
  php artisan test
  ```
- Feature and unit tests are included for core flows and policies.

## API Documentation (Swagger UI)

Interactive API documentation is available via Swagger UI:

- **URL:** `http://localhost:8000/swagger`
- The documentation is generated from the OpenAPI spec (`openapi.yaml`/`openapi.json`).
- You can use this UI to explore endpoints, see request/response formats, and try out the API interactively (with authentication).

If you update the OpenAPI spec, refresh the Swagger UI page to see the latest docs.

## License
MIT 
