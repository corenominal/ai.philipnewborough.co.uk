<?php

namespace App\Controllers;

use App\Models\ChatSessionModel;
use App\Models\ChatMessageModel;
use App\Models\SystemPromptModel;

class ChatApi extends BaseController
{
    public function models(): \CodeIgniter\HTTP\ResponseInterface
    {
        $ollamaIp = config('Ollama')->ip;

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 10,
            ],
        ]);

        $result = @file_get_contents("http://{$ollamaIp}:11434/api/tags", false, $context);

        if ($result === false) {
            return $this->response->setStatusCode(502)->setJSON(['error' => 'Failed to connect to Ollama']);
        }

        $data   = json_decode($result, true);
        $models = array_column($data['models'] ?? [], 'name');
        sort($models);

        return $this->response->setJSON(['models' => $models]);
    }

    public function search(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q = trim($this->request->getGet('q') ?? '');

        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['results' => []]);
        }

        $terms = array_values(array_filter(
            array_unique(preg_split('/\s+/', $q)),
            fn($t) => mb_strlen($t) >= 2
        ));

        if (empty($terms)) {
            return $this->response->setJSON(['results' => []]);
        }

        $db = \Config\Database::connect();

        $whereClauses    = [];
        $whereBindings   = [];
        $snippetClauses  = [];
        $snippetBindings = [];

        foreach ($terms as $term) {
            $like = '%' . $term . '%';
            $whereClauses[]    = "(cs.title LIKE ? OR EXISTS (SELECT 1 FROM chat_messages cm WHERE cm.session_id = cs.id AND cm.content LIKE ?))";
            $whereBindings[]   = $like;
            $whereBindings[]   = $like;
            $snippetClauses[]  = "cm2.content LIKE ?";
            $snippetBindings[] = $like;
        }

        $whereSQL   = implode(' AND ', $whereClauses);
        $snippetSQL = implode(' OR ', $snippetClauses);

        $sql = "SELECT cs.uuid, cs.title, cs.updated_at, cs.pinned,
            (SELECT cm2.content FROM chat_messages cm2
             WHERE cm2.session_id = cs.id AND ({$snippetSQL})
             ORDER BY cm2.id ASC LIMIT 1) AS snippet
        FROM chat_sessions cs
        WHERE cs.deleted_at IS NULL
          AND {$whereSQL}
        ORDER BY cs.pinned DESC, cs.updated_at DESC
        LIMIT 25";

        $rows = $db->query($sql, array_merge($snippetBindings, $whereBindings))->getResultArray();

        foreach ($rows as &$row) {
            if ($row['snippet'] !== null) {
                $pos = mb_strlen($row['snippet']);
                foreach ($terms as $term) {
                    $p = mb_stripos($row['snippet'], $term);
                    if ($p !== false && $p < $pos) {
                        $pos = $p;
                    }
                }
                $start   = max(0, $pos - 60);
                $excerpt = mb_substr($row['snippet'], $start, 180);
                if ($start > 0)                                 $excerpt = '…' . ltrim($excerpt);
                if ($start + 180 < mb_strlen($row['snippet'])) $excerpt .= '…';
                $row['snippet'] = $excerpt;
            }
        }
        unset($row);

        return $this->response->setJSON(['results' => $rows]);
    }

    public function sessions(): \CodeIgniter\HTTP\ResponseInterface
    {
        $model    = new ChatSessionModel();
        $sessions = $model->orderBy('pinned', 'DESC')
                          ->orderBy('updated_at', 'DESC')
                          ->findAll();

        return $this->response->setJSON(['sessions' => $sessions]);
    }

    public function createSession(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body        = $this->request->getJSON(true) ?? [];
        $ollamaModel = $body['model'] ?? 'llama3.2';

        $sessionModel = new ChatSessionModel();
        $uuid         = $this->generateUuid();

        $sessionModel->insert([
            'uuid'   => $uuid,
            'title'  => 'New Chat',
            'model'  => $ollamaModel,
            'pinned' => 0,
        ]);

        $session = $sessionModel->where('uuid', $uuid)->first();

        return $this->response->setStatusCode(201)->setJSON(['session' => $session]);
    }

    public function updateSession(string $uuid): \CodeIgniter\HTTP\ResponseInterface
    {
        $sessionModel = new ChatSessionModel();
        $session      = $sessionModel->where('uuid', $uuid)->first();

        if (!$session) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Session not found']);
        }

        $body    = $this->request->getJSON(true) ?? [];
        $allowed = [];

        if (isset($body['title']))  $allowed['title']  = trim($body['title']);
        if (isset($body['pinned'])) $allowed['pinned'] = (int) $body['pinned'];
        if (isset($body['model']))  $allowed['model']  = $body['model'];

        if (!empty($allowed)) {
            $sessionModel->update($session['id'], $allowed);
        }

        $updated = $sessionModel->find($session['id']);

        return $this->response->setJSON(['session' => $updated]);
    }

    public function deleteSession(string $uuid): \CodeIgniter\HTTP\ResponseInterface
    {
        $sessionModel = new ChatSessionModel();
        $session      = $sessionModel->where('uuid', $uuid)->first();

        if (!$session) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Session not found']);
        }

        $messageModel = new ChatMessageModel();
        $messageModel->where('session_id', $session['id'])->delete();
        $sessionModel->delete($session['id']);

        return $this->response->setJSON(['success' => true]);
    }

    public function messages(string $uuid): \CodeIgniter\HTTP\ResponseInterface
    {
        $sessionModel = new ChatSessionModel();
        $session      = $sessionModel->where('uuid', $uuid)->first();

        if (!$session) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Session not found']);
        }

        $messageModel = new ChatMessageModel();
        $messages     = $messageModel->where('session_id', $session['id'])
                                     ->orderBy('id', 'ASC')
                                     ->findAll();

        return $this->response->setJSON(['session' => $session, 'messages' => $messages]);
    }

    public function stream(): void
    {
        set_time_limit(300);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        $body        = $this->request->getJSON(true) ?? [];
        $sessionUuid = $body['session_uuid'] ?? null;
        $userMessage = trim($body['message'] ?? '');
        $model       = $body['model'] ?? 'llama3.2';

        if (empty($userMessage)) {
            echo "event: error\ndata: " . json_encode(['error' => 'Empty message']) . "\n\n";
            flush();
            exit;
        }

        $sessionModel = new ChatSessionModel();
        $messageModel = new ChatMessageModel();

        $session = $sessionUuid ? $sessionModel->where('uuid', $sessionUuid)->first() : null;

        if (!$session) {
            $sessionUuid = $this->generateUuid();
            $title       = mb_substr($userMessage, 0, 60) . (mb_strlen($userMessage) > 60 ? '…' : '');

            $sessionModel->insert([
                'uuid'   => $sessionUuid,
                'title'  => $title,
                'model'  => $model,
                'pinned' => 0,
            ]);

            $session = $sessionModel->where('uuid', $sessionUuid)->first();
        }

        if (!$session) {
            echo "event: error\ndata: " . json_encode(['error' => 'Failed to create session']) . "\n\n";
            flush();
            exit;
        }

        $messageModel->insert([
            'session_id' => $session['id'],
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        $history  = $messageModel->where('session_id', $session['id'])
                                  ->orderBy('id', 'ASC')
                                  ->findAll();
        $messages = array_map(fn($m) => ['role' => $m['role'], 'content' => $m['content']], $history);

        echo "event: session\ndata: " . json_encode(['uuid' => $sessionUuid, 'title' => $session['title']]) . "\n\n";
        flush();

        $ollamaIp     = config('Ollama')->ip;
        $activePrompt = (new SystemPromptModel())->getActive();
        if ($activePrompt && $activePrompt['content'] !== '') {
            array_unshift($messages, ['role' => 'system', 'content' => $activePrompt['content']]);
        }
        $payload = json_encode([
            'model'    => $session['model'],
            'messages' => $messages,
            'stream'   => true,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nConnection: close\r\n",
                'content' => $payload,
                'timeout' => 300,
            ],
        ]);

        $stream = @fopen("http://{$ollamaIp}:11434/api/chat", 'r', false, $context);

        if (!$stream) {
            echo "event: error\ndata: " . json_encode(['error' => 'Failed to connect to Ollama']) . "\n\n";
            flush();
            exit;
        }

        $fullResponse = '';

        while (!feof($stream)) {
            $line = fgets($stream);
            if (!$line || trim($line) === '') continue;

            $data = json_decode($line, true);
            if (!$data) continue;

            if (isset($data['message']['content'])) {
                $chunk         = $data['message']['content'];
                $fullResponse .= $chunk;
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                flush();
            }

            if (!empty($data['done'])) {
                $messageModel->insert([
                    'session_id' => $session['id'],
                    'role'       => 'assistant',
                    'model'      => $session['model'],
                    'content'    => $fullResponse,
                ]);

                $saved = $messageModel->find($messageModel->getInsertID());

                echo "event: done\ndata: " . json_encode([
                    'done'       => true,
                    'model'      => $saved['model'],
                    'created_at' => $saved['created_at'],
                ]) . "\n\n";
                flush();
                break;
            }
        }

        fclose($stream);
        exit;
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
