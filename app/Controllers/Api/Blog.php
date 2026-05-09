<?php

namespace App\Controllers\Api;

class Blog extends BaseController
{
    public function analyse(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body    = $this->request->getJSON(true) ?? [];
        $content = trim($body['content'] ?? '');
        $title   = trim($body['title'] ?? '');
        $model   = $body['model'] ?? 'llama3.2';

        if (empty($content)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No content provided.']);
        }

        $titleLine = $title ? "\nTitle: {$title}" : '';

        $prompt = <<<PROMPT
Analyse the following blog post and return a JSON response with two fields:
- "summary": a concise 2–3 sentence description of what the post is about and its main points.
- "suggestions": an array of improvement suggestions, each as an object with "area" (the aspect being addressed, e.g. "Structure", "Clarity", "SEO", "Tone", "Accessibility") and "suggestion" (a clear, actionable recommendation).

Provide between 3 and 8 suggestions covering a mix of areas.
Do not use markdown in any field values.
Respond only with valid JSON using this exact format:
{"summary": "...", "suggestions": [{"area": "...", "suggestion": "..."}, ...]}{$titleLine}

Content:
{$content}
PROMPT;

        $ollamaIp = config('Ollama')->ip;
        $payload  = json_encode([
            'model'  => $model,
            'prompt' => $prompt,
            'format' => 'json',
            'stream' => false,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 90,
            ],
        ]);

        $result = @file_get_contents("http://{$ollamaIp}:11434/api/generate", false, $context);

        if ($result === false) {
            return $this->response->setStatusCode(502)->setJSON(['error' => 'Failed to connect to Ollama.']);
        }

        $data     = json_decode($result, true);
        $response = json_decode($data['response'] ?? '{}', true);

        if (empty($response['summary']) || empty($response['suggestions']) || !is_array($response['suggestions'])) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unexpected response from Ollama.']);
        }

        return $this->response->setJSON([
            'summary'     => $response['summary'],
            'suggestions' => array_values($response['suggestions']),
        ]);
    }

    public function rewrite(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body    = $this->request->getJSON(true) ?? [];
        $content = trim($body['content'] ?? '');
        $title   = trim($body['title'] ?? '');
        $model   = $body['model'] ?? 'llama3.2';

        if (empty($content)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No content provided.']);
        }

        $titleInstruction = $title
            ? "Also rewrite the title if it can be improved, and include it in the response as \"title\".\nTitle: {$title}\n"
            : "Do not include a \"title\" field in the response.\n";

        $responseFormat = $title
            ? '{"title": "...", "content": "..."}'
            : '{"content": "..."}';

        $prompt = <<<PROMPT
Rewrite the following blog post to fix any typos, spelling mistakes, grammar errors, and punctuation issues. Improve sentence flow and readability where needed, but preserve the author's original style, voice, and tone throughout. Do not add new information, change the meaning, or significantly alter the structure.

{$titleInstruction}
Return only valid JSON in this exact format: {$responseFormat}
Do not include any markdown in field values.

Content:
{$content}
PROMPT;

        $ollamaIp = config('Ollama')->ip;
        $payload  = json_encode([
            'model'  => $model,
            'prompt' => $prompt,
            'format' => 'json',
            'stream' => false,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 120,
            ],
        ]);

        $result = @file_get_contents("http://{$ollamaIp}:11434/api/generate", false, $context);

        if ($result === false) {
            return $this->response->setStatusCode(502)->setJSON(['error' => 'Failed to connect to Ollama.']);
        }

        $data     = json_decode($result, true);
        $response = json_decode($data['response'] ?? '{}', true);

        if (empty($response['content'])) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unexpected response from Ollama.']);
        }

        $output = ['content' => $response['content']];
        if ($title && !empty($response['title'])) {
            $output['title'] = $response['title'];
        }

        return $this->response->setJSON($output);
    }
}
