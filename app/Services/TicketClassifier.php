<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class TicketClassifier
{
    public function classify(string $subject, string $body): array
    {
        // Check if OpenAI classification is enabled
        if (!config('openai.classify_enabled', true)) {
            return $this->getRandomClassification();
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => "Subject: {$subject}\n\nBody: {$body}"
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 150
            ]);

            $content = $response->choices[0]->message->content;
            $result = json_decode($content, true);

            if (!$result || !isset($result['category'])) {
                Log::warning('OpenAI returned invalid JSON for ticket classification', [
                    'subject' => $subject,
                    'response' => $content
                ]);
                return $this->getRandomClassification();
            }

            return [
                'category' => $result['category'] ?? 'General',
                'explanation' => $result['explanation'] ?? 'AI classification failed',
                'confidence' => min(1.0, max(0.0, $result['confidence'] ?? 0.5))
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI classification failed', [
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return $this->getRandomClassification();
        }
    }

    private function getSystemPrompt(): string
    {
        return "You are a help desk ticket classifier. Analyze the ticket content and return a JSON response with exactly these keys:

{
    \"category\": \"one of: Technical, Billing, Account, Feature Request, Bug Report, General\",
    \"explanation\": \"brief explanation of why this category was chosen\",
    \"confidence\": \"number between 0.0 and 1.0 indicating confidence level\"
}

Choose the most appropriate category based on the ticket content. Be concise but accurate.";
    }

    private function getRandomClassification(): array
    {
        $categories = ['Technical', 'Billing', 'Account', 'Feature Request', 'Bug Report', 'General'];
        $explanations = [
            'Random classification - AI disabled',
            'Fallback classification',
            'Default category assignment'
        ];

        return [
            'category' => $categories[array_rand($categories)],
            'explanation' => $explanations[array_rand($explanations)],
            'confidence' => round(rand(50, 90) / 100, 2)
        ];
    }
}
