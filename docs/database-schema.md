# Database Schema Plan

## Core Tables

### users
- id
- name
- email (unique)
- password
- role (`admin`, `instructor`, `student`)
- status
- created_at / updated_at

### courses
- id
- title
- code
- description
- instructor_id (FK users.id)
- thumbnail_path
- is_published
- created_at / updated_at

### course_enrollments
- id
- course_id (FK)
- student_id (FK users.id)
- enrolled_at
- progress_percent

### modules
- id
- course_id (FK)
- title
- description
- order_no

### materials
- id
- module_id (FK)
- type (`pdf`, `video`, `link`, `text`)
- title
- file_path / external_url
- metadata_json

### quizzes
- id
- course_id (FK)
- title
- instructions
- time_limit_minutes
- attempts_allowed
- is_published

### quiz_questions
- id
- quiz_id (FK)
- question_text
- question_type (`multiple_choice`, `true_false`)
- points

### quiz_options
- id
- question_id (FK)
- option_text
- is_correct

### quiz_attempts
- id
- quiz_id (FK)
- student_id (FK users.id)
- started_at
- submitted_at
- score
- status

### quiz_answers
- id
- attempt_id (FK)
- question_id (FK)
- selected_option_id (nullable FK)
- is_correct
- points_awarded

### assignments
- id
- course_id (FK)
- title
- instructions
- due_at
- max_points
- attachment_path

### assignment_submissions
- id
- assignment_id (FK)
- student_id (FK users.id)
- submission_text
- submission_file_path
- submitted_at
- points_awarded (nullable)
- feedback (nullable)
- graded_by (nullable FK users.id)
- graded_at (nullable)

### ai_chat_sessions
- id
- course_id (nullable FK)
- student_id (FK users.id)
- provider (`openai`, `gemini`)
- created_at

### ai_chat_messages
- id
- session_id (FK)
- role (`user`, `assistant`, `system`)
- content
- token_count
- created_at

### activity_logs
- id
- user_id (nullable FK)
- action
- entity_type
- entity_id
- meta_json
- created_at

## Indexing Notes
- Add compound index: `(course_id, student_id)` on `course_enrollments`.
- Add index on `quiz_attempts(student_id, quiz_id)`.
- Add index on `assignment_submissions(student_id, assignment_id)`.
