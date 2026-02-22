# Implementation Roadmap

## Phase 1 - Foundation
1. Initialize Laravel project and environment.
2. Configure authentication and role middleware.
3. Implement core migrations (users, courses, enrollments, modules).
4. Seed sample admin/instructor/student users.

## Phase 2 - Learning Content
1. Build instructor course CRUD.
2. Add module/material upload workflows.
3. Build student course catalog and enrollment.
4. Build student material viewer.

## Phase 3 - Assessments
1. Build quiz creation UI and question bank.
2. Implement quiz attempt flow.
3. Add objective auto-grading service.
4. Build assignment publish/submit/grade workflow.

## Phase 4 - Analytics
1. Add admin and instructor dashboards.
2. Compute performance KPIs (completion, average score, at-risk students).
3. Add downloadable grade reports (CSV/PDF).

## Phase 5 - AI Tutor
1. Implement provider abstraction (OpenAI/Gemini).
2. Build course-aware chatbot endpoint and UI.
3. Add moderation/safety filters and usage limits.
4. Track AI conversation logs for analytics/audit.

## Phase 6 - Hardening
1. Add tests (feature + policy + service tests).
2. Add queue workers and retries for AI/report jobs.
3. Optimize queries and caching.
4. Prepare deployment checklist and backups.
