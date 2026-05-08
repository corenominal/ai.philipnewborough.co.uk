# ai.philipnewborough.co.uk

A self-hosted ChatGPT-style chat interface built on [CodeIgniter 4](https://codeigniter.com/), backed by a local [Ollama](https://ollama.com/) instance.

## Features

- **Chat UI** — conversational interface modelled on ChatGPT, with streaming responses via Server-Sent Events
- **Model selection** — available models are pulled live from the Ollama API
- **Chat history** — sessions are persisted to a database; each session can be pinned, renamed, or deleted
- **Full-text search** — conjunctive modal search across session titles and message content
- **System prompt management** — admin page to edit the default system prompt prepended to every session, with full revision history and one-click revert
- **Response metadata** — each assistant message shows the model used and timestamp
- **Copy to clipboard** — copy a response as rendered HTML or raw Markdown
- **Syntax highlighting** — code blocks rendered with [highlight.js](https://highlightjs.org/) (Dracula theme)
- **Admin dashboard** — stats overview (sessions, messages, pinned sessions, active models)

## Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4 |
| Frontend | Bootstrap 5, Bootstrap Icons |
| Markdown | marked.js |
| Syntax highlighting | highlight.js |
| LLM backend | Ollama (configurable IP via `.env`) |
| Database | MySQL / MariaDB |

## Requirements

- PHP 8.1+
- Composer
- A running Ollama instance accessible on your network
- MySQL or MariaDB

## Setup

```bash
composer install
cp env .env
```

Edit `.env` and set your Ollama instance IP, database credentials, and `app.baseURL`.

Run migrations:

```bash
php spark migrate
```

## Configuration

`OLLAMA_IP` in `.env` controls which host the app connects to for model listing and chat streaming.

The default system prompt (prepended to every new session) is managed at `/admin/prompt`. Each save creates a new revision; previous versions can be restored at any time.

## License

Application code is released under the [MIT License](LICENSE). CodeIgniter 4 is included under its own [license](LICENSE-CODEIGNITER).
