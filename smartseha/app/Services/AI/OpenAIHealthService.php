<?php

namespace App\Services\AI;

use App\Services\SiteSettingService;

class OpenAIHealthService
{
    public function __construct(private readonly SiteSettingService $settings)
    {
    }

    public function isEnabled(): bool
    {
        return $this->settings->getBool('ai.enabled', false) && !empty($this->settings->get('ai.api_key'));
    }

    public function model(): string
    {
        return (string) $this->settings->get('ai.model', 'gpt-4o-mini');
    }

    public function generateRecommendations(array $payload): array
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('AI is not enabled');
        }

        $prompt = [
            'role' => 'user',
            'content' => sprintf(
                "Create health recommendations in Arabic as JSON array with objects: {category, icon, title, description}. User data: %s",
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            ),
        ];

        $content = $this->chat([$prompt]);
        $decoded = json_decode($this->extractJson($content), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid AI recommendations response');
        }

        return $decoded;
    }

    public function analyzeFoodImage(string $absolutePath): array
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('AI is not enabled');
        }

        if (!is_file($absolutePath)) {
            throw new \RuntimeException('Image file not found');
        }

        $binary = file_get_contents($absolutePath);

        if ($binary === false) {
            throw new \RuntimeException('Unable to read image');
        }

        $mime = mime_content_type($absolutePath) ?: 'image/jpeg';
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);

        $content = $this->chat([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Analyze image and return only JSON with this exact shape: {isFood:boolean, rejectReason:string|null, foodName:string|null, calories:number|null, protein:number|null, carbs:number|null, fat:number|null, minerals:array, allergens:array, evaluation:string|null, rating:string|null}. Return all text fields in Arabic (foodName, rejectReason, evaluation, allergens/minerals terms). If image is not food set isFood=false.'],
                    ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                ],
            ],
        ]);

        $decoded = json_decode($this->extractJson($content), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid AI food analysis response');
        }

        return $decoded;
    }

    private function chat(array $messages): string
    {
        if (!class_exists(\OpenAI::class)) {
            throw new \RuntimeException('openai-php/client is not installed');
        }

        $apiKey = (string) $this->settings->get('ai.api_key');
        $client = \OpenAI::client($apiKey);

        $response = $client->chat()->create([
            'model' => $this->model(),
            'temperature' => 0.2,
            'messages' => array_merge([
                [
                    'role' => 'system',
                    'content' => 'You are a healthcare assistant. Return compact valid JSON only and no markdown.',
                ],
            ], $messages),
        ]);

        return (string) ($response->choices[0]->message->content ?? '');
    }

    private function extractJson(string $content): string
    {
        $trimmed = trim($content);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?|```$/m', '', $trimmed) ?? $trimmed;
            $trimmed = trim($trimmed);
        }

        $firstArr = strpos($trimmed, '[');
        $firstObj = strpos($trimmed, '{');
        $start = false;

        if ($firstArr !== false && $firstObj !== false) {
            $start = min($firstArr, $firstObj);
        } elseif ($firstArr !== false) {
            $start = $firstArr;
        } elseif ($firstObj !== false) {
            $start = $firstObj;
        }

        if ($start !== false && $start > 0) {
            $trimmed = substr($trimmed, $start);
        }

        $lastArr = strrpos($trimmed, ']');
        $lastObj = strrpos($trimmed, '}');
        $end = max($lastArr !== false ? $lastArr : -1, $lastObj !== false ? $lastObj : -1);
        if ($end > 0) {
            $trimmed = substr($trimmed, 0, $end + 1);
        }

        return $trimmed;
    }
}
