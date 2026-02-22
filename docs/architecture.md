# LMS Architecture (Laravel)

## 1. Stack
- **Backend:** Laravel (PHP), Eloquent ORM, queue workers for background AI tasks.
- **Frontend:** Blade templates + HTML/CSS/JS (progressively enhanced with Alpine.js or Vue if needed).
- **Database:** MySQL/MariaDB.
- **Storage:** Laravel filesystem for uploaded files.
- **AI Providers:** OpenAI API or Google Gemini through a provider interface.

## 2. High-Level Modules
1. **Auth & RBAC**
   - Laravel authentication with role middleware.
   - Roles: `admin`, `instructor`, `student`.
2. **Course Management**
   - Courses, modules, learning materials.
3. **Assessment Engine**
   - Quizzes, objective questions, attempts, auto-grading.
4. **Assignment Workflow**
   - Assignment publishing, submissions, manual grading.
5. **Analytics**
   - Progress reports, grade trends, participation insights.
6. **AI Tutor**
   - Contextual learning assistant per enrolled course.

## 3. Suggested Laravel Layering
- `app/Models/*` for domain entities.
- `app/Http/Controllers/{Admin,Instructor,Student}/*` for role-specific UIs.
- `app/Services/*` for business logic (grading, AI orchestration, analytics).
- `app/Policies/*` for authorization checks.
- `app/Jobs/*` for async AI calls/report generation.
- `routes/web.php` for dashboard and form interactions.
- `routes/api.php` for async frontend interactions.

## 4. Security and Compliance
- Enforce authorization via policies + middleware.
- Validate all uploads (mime/size), sanitize filenames, store privately when needed.
- Rate-limit AI endpoints and log prompts/responses for auditability.
- Keep API keys in `.env`; never expose provider keys to frontend.

## 5. AI Design Pattern
Use a provider abstraction:
- `AiProviderInterface`
- `OpenAiProvider`
- `GeminiProvider`
- `AiTutorService` (course context + prompt orchestration)

This allows switching providers without rewriting the chatbot feature.
