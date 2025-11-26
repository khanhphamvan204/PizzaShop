<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    /**
     * Check if the given text contains profanity or inappropriate content
     * 
     * @param string $text The text to check
     * @return array Returns ['is_profane' => bool, 'reason' => string]
     */
    public function checkProfanity(string $text): array
    {
        if (empty($text)) {
            return [
                'is_profane' => false,
                'reason' => 'No text provided'
            ];
        }

        try {
            $prompt = "Bạn là một hệ thống kiểm duyệt nội dung. Phân tích văn bản sau và xác định xem có chứa ngôn từ thô tục, tục tĩu, xúc phạm, hoặc không phù hợp không (bao gồm cả tiếng Việt và tiếng Anh).\n\nVăn bản cần kiểm tra: \"{$text}\"\n\nChỉ trả lời theo định dạng JSON chính xác như sau (không có text thêm):\n{\"is_profane\": true/false, \"reason\": \"lý do ngắn gọn nếu có từ ngữ không phù hợp\"}";

            $response = Http::timeout(10)->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // If API fails, we allow the review to go through
                return [
                    'is_profane' => false,
                    'reason' => 'Unable to verify content safety'
                ];
            }

            $result = $response->json();

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $generatedText = $result['candidates'][0]['content']['parts'][0]['text'];

                // Try to extract JSON from the response
                if (preg_match('/\{.*\}/s', $generatedText, $matches)) {
                    $jsonResult = json_decode($matches[0], true);

                    if ($jsonResult && isset($jsonResult['is_profane'])) {
                        return [
                            'is_profane' => (bool) $jsonResult['is_profane'],
                            'reason' => $jsonResult['reason'] ?? 'Content flagged as inappropriate'
                        ];
                    }
                }
            }

            // If we can't parse the response, allow the review
            return [
                'is_profane' => false,
                'reason' => 'Unable to parse safety check result'
            ];
        } catch (\Exception $e) {
            Log::error('Gemini profanity check failed', [
                'error' => $e->getMessage(),
                'text' => $text
            ]);

            // On error, we allow the review to go through rather than blocking legitimate reviews
            return [
                'is_profane' => false,
                'reason' => 'Safety check unavailable'
            ];
        }
    }
}
