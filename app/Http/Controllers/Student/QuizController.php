<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class QuizController extends Controller
{
    public function show(int $quiz)
    {
        // Display quiz details/questions.
    }

    public function submit(int $quiz)
    {
        // Submit answers and trigger auto-grading.
    }
}
