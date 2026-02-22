# Learning Management System (LMS) - Laravel Project Blueprint

This repository contains a **Laravel-first implementation blueprint** for a web-based Learning Management System (LMS) with AI-assisted learning support.

## Project Goal
Build an LMS in **PHP (Laravel)** with:
- Role-based modules for **Admin**, **Instructor**, and **Student**.
- Course management, quizzes, assignments, grading, and analytics.
- AI-powered tutoring via **OpenAI API** or **Google Gemini**.

## Included in this starter
- Proposed application architecture and module boundaries.
- Initial route map for all major role flows.
- Sample controller/service stubs to kickstart Laravel implementation.
- Normalized database migration plan.
- Step-by-step implementation roadmap.

## Core Feature Coverage

### Admin
- Dashboard statistics
- User management (CRUD)
- Course oversight
- Role assignments
- System settings
- Reports and activity logs

### Instructor
- Course creation and management
- Upload content (PDF/video/modules)
- Quiz and assignment authoring
- Submission grading
- Student progress tracking
- Grade report export

### Student
- Registration/login
- Course enrollment
- Material consumption
- Quiz attempts
- Assignment submissions
- Grades and progress tracking

## AI Integration Scope
- AI Tutor chatbot per course context.
- Prompt grounding from modules and uploaded content metadata.
- Configurable provider abstraction for OpenAI/Gemini.
- Interaction logging and safety controls.

## Next Steps
1. Create a fresh Laravel app (`laravel/laravel`) in this repository.
2. Copy these stubs into the generated Laravel structure (or keep this structure and run `composer install` after adding framework files).
3. Implement migrations and policies first, then role dashboards, then instructional workflows, then AI features.

See:
- `docs/architecture.md`
- `docs/database-schema.md`
- `docs/implementation-roadmap.md`
