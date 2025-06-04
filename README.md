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
