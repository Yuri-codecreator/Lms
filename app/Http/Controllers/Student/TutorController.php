<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function __construct(private readonly AiTutorService $aiTutorService)
    {
    }

    public function index(): View
    {
        return view('student.tutor.index');
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'course_id' => ['nullable', 'integer'],
        ]);

        $reply = $this->aiTutorService->reply(
            userId: (int) $request->user()->id,
            message: $validated['message'],
            courseId: isset($validated['course_id']) ? (int) $validated['course_id'] : null,
        );

        return response()->json(['reply' => $reply]);
    }
}
