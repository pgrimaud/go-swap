<?php

declare(strict_types=1);

namespace App\Service;

use OpenAI as OpenAIClient;

class OpenAI
{
    public function __construct(private readonly string $apiKey)
    {
    }

    public function getTextFromPicture(string $picturePath, $useMock = false): array
    {
        if ($useMock === true) {
            return json_decode(file_get_contents(__DIR__ . '/../../data/open-ai-mock.json'), true);
        }

        $client = OpenAIClient::client($this->apiKey);

        $imageData = file_get_contents($picturePath);
        $base64EncodedImage = base64_encode((string) $imageData);
        $dataUri = 'data:image/png;base64,' . $base64EncodedImage;

        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => [
                'type' => 'json_object'
            ],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Read text on the picture, ignore hours on the top right. Give me a json array, mapped by text with numbers.',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $dataUri
                            ],
                        ],
                    ],
                ]
            ],
        ]);

        /** @var array $data */
        $data = json_decode((string) $response->choices[0]->message->content, true);

        return $data['data'];
    }
}