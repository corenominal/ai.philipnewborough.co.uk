<?php

namespace App\Controllers\Api;

class Images extends BaseController
{
    public function alttext(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body   = $this->request->getJSON(true) ?? [];
        $url    = trim($body['url'] ?? '');
        $image  = trim($body['image'] ?? '');
        $model  = $body['model'] ?? 'llama3.2-vision';

        if (empty($url) && empty($image)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Provide either a url or a base64-encoded image.']);
        }

        if (!empty($url)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (!in_array($scheme, ['http', 'https'], true)) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Image URL must use http or https.']);
            }

            $imageData = @file_get_contents($url);
            if ($imageData === false) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Could not fetch image from URL.']);
            }

            $base64 = base64_encode($imageData);
        } else {
            // Strip data URI prefix if present (e.g. "data:image/png;base64,...")
            $base64 = preg_replace('/^data:[^;]+;base64,/', '', $image);

            if (base64_decode($base64, true) === false) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid base64 image data.']);
            }
        }

        $prompt = 'Generate concise, descriptive alt text for this image suitable for screen readers. Be specific but brief (under 125 characters). Do not begin with "Image of" or "Photo of". Return only the alt text, nothing else.';

        $ollamaIp = config('Ollama')->ip;
        $payload  = json_encode([
            'model'  => $model,
            'prompt' => $prompt,
            'images' => [$base64],
            'stream' => false,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 60,
            ],
        ]);

        $result = @file_get_contents("http://{$ollamaIp}:11434/api/generate", false, $context);

        if ($result === false) {
            return $this->response->setStatusCode(502)->setJSON(['error' => 'Failed to connect to Ollama.']);
        }

        $data    = json_decode($result, true);
        $altText = trim($data['response'] ?? '');

        if (empty($altText)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unexpected response from Ollama.']);
        }

        return $this->response->setJSON(['alt_text' => $altText]);
    }
}
