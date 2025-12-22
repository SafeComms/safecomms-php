<?php

namespace SafeComms;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class SafeCommsClient
{
    private GuzzleClient $client;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = 'https://api.safecomms.dev')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Moderate text content.
     *
     * @param string $content The text content to moderate.
     * @param string $language The language of the content (default: 'en').
     * @param bool $replace Whether to replace unsafe content.
     * @param bool $pii Whether to detect/replace PII.
     * @param string|null $replaceSeverity The severity level for replacement.
     * @param string|null $moderationProfileId The ID of the moderation profile to use.
     * @return array The moderation result.
     * @throws \Exception If the API request fails.
     */
    public function moderateText(
        string $content,
        string $language = 'en',
        bool $replace = false,
        bool $pii = false,
        ?string $replaceSeverity = null,
        ?string $moderationProfileId = null
    ): array {
        $payload = [
            'content' => $content,
            'language' => $language,
            'replace' => $replace,
            'pii' => $pii,
        ];

        if ($replaceSeverity !== null) {
            $payload['replaceSeverity'] = $replaceSeverity;
        }

        if ($moderationProfileId !== null) {
            $payload['moderationProfileId'] = $moderationProfileId;
        }

        try {
            $response = $this->client->post('/moderation/text', [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->handleError($e);
            throw $e; // Should not be reached if handleError throws
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Moderate image content.
     *
     * @param string $image The image URL or base64 string to moderate.
     * @param string $language The language of the content (default: 'en').
     * @param string|null $moderationProfileId The ID of the moderation profile to use.
     * @param bool $enableOcr Whether to extract text (OCR) from the image.
     * @param bool $enhancedOcr Whether to use enhanced OCR for higher accuracy.
     * @param bool $extractMetadata Whether to extract metadata (EXIF) from the image.
     * @return array The moderation result.
     * @throws \Exception If the API request fails.
     */
    public function moderateImage(
        string $image,
        string $language = 'en',
        ?string $moderationProfileId = null,
        bool $enableOcr = false,
        bool $enhancedOcr = false,
        bool $extractMetadata = false
    ): array {
        $payload = [
            'image' => $image,
            'language' => $language,
            'enableOcr' => $enableOcr,
            'enhancedOcr' => $enhancedOcr,
            'extractMetadata' => $extractMetadata,
        ];

        if ($moderationProfileId !== null) {
            $payload['moderationProfileId'] = $moderationProfileId;
        }

        try {
            $response = $this->client->post('/moderation/image', [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->handleError($e);
            throw $e;
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Moderate image file.
     *
     * @param string $filePath The path to the image file.
     * @param string $language The language of the content (default: 'en').
     * @param string|null $moderationProfileId The ID of the moderation profile to use.
     * @param bool $enableOcr Whether to extract text (OCR) from the image.
     * @param bool $enhancedOcr Whether to use enhanced OCR for higher accuracy.
     * @param bool $extractMetadata Whether to extract metadata (EXIF) from the image.
     * @return array The moderation result.
     * @throws \Exception If the API request fails.
     */
    public function moderateImageFile(
        string $filePath,
        string $language = 'en',
        ?string $moderationProfileId = null,
        bool $enableOcr = false,
        bool $enhancedOcr = false,
        bool $extractMetadata = false
    ): array {
        $multipart = [
            [
                'name' => 'image',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath)
            ],
            [
                'name' => 'language',
                'contents' => $language
            ],
            [
                'name' => 'enableOcr',
                'contents' => $enableOcr ? 'true' : 'false'
            ],
            [
                'name' => 'enhancedOcr',
                'contents' => $enhancedOcr ? 'true' : 'false'
            ],
            [
                'name' => 'extractMetadata',
                'contents' => $extractMetadata ? 'true' : 'false'
            ]
        ];

        if ($moderationProfileId !== null) {
            $multipart[] = [
                'name' => 'moderationProfileId',
                'contents' => $moderationProfileId
            ];
        }

        try {
            $response = $this->client->post('/moderation/image/upload', [
                'multipart' => $multipart,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->handleError($e);
            throw $e;
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get current usage statistics.
     *
     * @return array The usage statistics.
     * @throws \Exception If the API request fails.
     */
    public function getUsage(): array
    {
        try {
            $response = $this->client->get('/usage');
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->handleError($e);
            throw $e;
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function handleError(RequestException $e): void
    {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $message = $data['detail'] ?? $data['title'] ?? $e->getMessage();
            throw new \Exception($message, $response->getStatusCode());
        }

        throw new \Exception($e->getMessage(), $e->getCode());
    }
}
