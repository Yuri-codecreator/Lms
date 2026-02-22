<?php

namespace App\Services;

class AiTutorService
{
    public function reply(int $userId, string $message, ?int $courseId = null): string
    {
        $courseContext = $courseId ? " for course #{$courseId}" : '';

        // Placeholder implementation. In full Laravel app:
        // 1) Fetch contextual data from enrolled course materials.
        // 2) Build safe system prompt.
        // 3) Dispatch to configured provider (OpenAI/Gemini).
        // 4) Persist chat session and message logs.
        // 5) Return assistant response.
        return "AI tutor response{$courseContext}: " . trim($message);
    }
}
