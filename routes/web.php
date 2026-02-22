<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Student\TutorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function (): void {
    Route::resource('courses', InstructorCourseController::class);
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('/courses', [EnrollmentController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');

    Route::get('/quizzes/{quiz}', [StudentQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/submit', [StudentQuizController::class, 'submit'])->name('quizzes.submit');

    Route::get('/ai-tutor', [TutorController::class, 'index'])->name('tutor.index');
    Route::post('/ai-tutor/message', [TutorController::class, 'message'])->name('tutor.message');
});
