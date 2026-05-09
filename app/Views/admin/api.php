<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-3">

    <div class="border-bottom border-1 mb-4 pb-3">
        <h2 class="mb-0">API Reference</h2>
        <p class="text-secondary mb-0 mt-1">Available API endpoints for external integrations.</p>
    </div>

    <!-- Table of contents -->
    <div class="card mb-4">
        <div class="card-header fw-semibold">
            <i class="bi bi-list-ul me-2"></i>Contents
        </div>
        <div class="card-body py-2">
            <ul class="list-unstyled mb-0">
                <li class="py-1"><a href="#authentication" class="text-decoration-none">Authentication</a></li>
                <li class="py-1">
                    <span class="text-secondary">Blog</span>
                    <ul class="list-unstyled ms-3 mt-1">
                        <li class="py-1"><a href="#blog-analyse" class="text-decoration-none font-monospace small">POST /api/blog/analyse</a></li>
                        <li class="py-1"><a href="#blog-rewrite" class="text-decoration-none font-monospace small">POST /api/blog/rewrite</a></li>
                        <li class="py-1"><a href="#blog-excerpt" class="text-decoration-none font-monospace small">POST /api/blog/excerpt</a></li>
                    </ul>
                </li>
                <li class="py-1">
                    <span class="text-secondary">Images</span>
                    <ul class="list-unstyled ms-3 mt-1">
                        <li class="py-1"><a href="#images-alttext" class="text-decoration-none font-monospace small">POST /api/images/alttext</a></li>
                        <li class="py-1"><a href="#images-describe" class="text-decoration-none font-monospace small">POST /api/images/describe</a></li>
                    </ul>
                </li>
                <li class="py-1">
                    <span class="text-secondary">Status</span>
                    <ul class="list-unstyled ms-3 mt-1">
                        <li class="py-1"><a href="#status-rewrite" class="text-decoration-none font-monospace small">POST /api/status/rewrite</a></li>
                    </ul>
                </li>
                <li class="py-1">
                    <span class="text-secondary">Tags</span>
                    <ul class="list-unstyled ms-3 mt-1">
                        <li class="py-1"><a href="#tags-generate" class="text-decoration-none font-monospace small">POST /api/tags/generate</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- Authentication -->
    <div id="authentication" class="card mb-4">
        <div class="card-header fw-semibold">
            <i class="bi bi-shield-lock-fill me-2"></i>Authentication
        </div>
        <div class="card-body">
            <p class="mb-2">All API endpoints require an admin API key passed as a request header.</p>
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:180px">Header</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">apikey</td>
                        <td class="text-secondary">Your admin API key</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Blog endpoints -->
    <h5 id="blog" class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Blog</h5>

    <!-- POST /api/blog/analyse -->
    <div id="blog-analyse" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/blog/analyse</code>
        </div>
        <div class="card-body pb-0">
            <p>Analyses a blog post using Ollama and returns a concise summary of the content along with actionable suggestions for improvement across areas such as structure, clarity, tone, SEO, and accessibility.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">content</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-danger">Yes</span></td>
                        <td>The full body text of the blog post. Plain text or HTML are both accepted.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">title</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>The title of the blog post. Including it improves the quality of the analysis.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama model to use. Defaults to <code>llama3.2</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">summary</td>
                        <td class="text-secondary">string</td>
                        <td>A concise 2–3 sentence description of the blog post and its main points.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">suggestions</td>
                        <td class="text-secondary">object[]</td>
                        <td>An array of improvement suggestions. Each object has an <code>area</code> field (e.g. <code>Clarity</code>, <code>SEO</code>, <code>Structure</code>) and a <code>suggestion</code> field with an actionable recommendation.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/blog/analyse \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"title": "Getting started with CodeIgniter 4", "content": "CodeIgniter 4 is a lightweight PHP framework..."}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "summary": "This post introduces CodeIgniter 4 as a lightweight PHP framework suited to developers who want speed without complexity. It covers installation, routing basics, and connecting to a database. The tone is beginner-friendly but assumes some prior PHP knowledge.",
  "suggestions": [
    {
      "area": "Structure",
      "suggestion": "Add a table of contents at the top — the post covers several distinct topics and readers would benefit from being able to jump to the section most relevant to them."
    },
    {
      "area": "SEO",
      "suggestion": "The title is generic. Consider something more specific such as 'Getting started with CodeIgniter 4: routing, controllers, and your first database query' to improve search visibility."
    },
    {
      "area": "Clarity",
      "suggestion": "The database section jumps straight into code without explaining what the example is trying to achieve. A one-sentence introduction before each code block would help readers follow along."
    },
    {
      "area": "Tone",
      "suggestion": "Some paragraphs switch between second and third person. Pick one and apply it consistently throughout."
    }
  ]
}</code></pre>
        </div>
    </div>

    <!-- POST /api/blog/rewrite -->
    <div id="blog-rewrite" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/blog/rewrite</code>
        </div>
        <div class="card-body pb-0">
            <p>Rewrites a blog post to fix typos, spelling mistakes, grammar errors, and punctuation issues, and to improve sentence flow and readability. The author's original style, voice, and tone are preserved throughout. No new information is added and the structure is kept intact.</p>
            <p>If a <code>title</code> is supplied, the model will also rewrite the title if it can be improved and return it alongside the content.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">content</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-danger">Yes</span></td>
                        <td>The full body text of the blog post to rewrite. Plain text or HTML are both accepted.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">title</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>The title of the blog post. If provided, the model will also return a rewritten title in the response.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama model to use. Defaults to <code>llama3.2</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">content</td>
                        <td class="text-secondary">string</td>
                        <td>The rewritten blog post body.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">title</td>
                        <td class="text-secondary">string</td>
                        <td>The rewritten title. Only present in the response when a <code>title</code> was included in the request.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/blog/rewrite \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"title": "My thorts on PHP", "content": "PHP is somtimes underrated. I been using it for years and find it realy versitile..."}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "title": "My thoughts on PHP",
  "content": "PHP is sometimes underrated. I've been using it for years and find it really versatile..."
}</code></pre>
        </div>
    </div>

    <!-- POST /api/blog/excerpt -->
    <div id="blog-excerpt" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/blog/excerpt</code>
        </div>
        <div class="card-body pb-0">
            <p>Generates an excerpt for a blog post using Ollama. The excerpt matches the author's original style and voice and is written as a natural teaser rather than a direct copy of opening sentences. Useful for post listing pages, RSS feeds, and social previews.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">content</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-danger">Yes</span></td>
                        <td>The full body text of the blog post. Plain text or HTML are both accepted.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">title</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>The title of the blog post. Including it helps the model produce a more focused excerpt.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">length</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Controls the length of the excerpt. Accepted values: <code>short</code> (one sentence), <code>medium</code> (2–3 sentences, default), <code>long</code> (4–5 sentences).</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama model to use. Defaults to <code>llama3.2</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">excerpt</td>
                        <td class="text-secondary">string</td>
                        <td>The generated excerpt.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/blog/excerpt \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"title": "Getting started with CodeIgniter 4", "content": "CodeIgniter 4 is a lightweight PHP framework...", "length": "short"}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "excerpt": "If you've been looking for a PHP framework that gets out of your way, CodeIgniter 4 might be exactly what you need."
}</code></pre>
        </div>
    </div>

    <!-- Images endpoints -->
    <h5 id="images" class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Images</h5>

    <!-- POST /api/images/alttext -->
    <div id="images-alttext" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/images/alttext</code>
        </div>
        <div class="card-body pb-0">
            <p>Generates alt text for an image using a vision model via Ollama. Accepts either a publicly accessible image URL or a base64-encoded image. Returns a concise, accessibility-focused description suitable for use as an HTML <code>alt</code> attribute.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <p class="text-secondary small mb-2">Provide either <code>url</code> or <code>image</code> — at least one is required.</p>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">url</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-warning text-dark">Either</span></td>
                        <td>A publicly accessible URL of the image to describe. Must use <code>http</code> or <code>https</code>.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">image</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-warning text-dark">Either</span></td>
                        <td>A base64-encoded image. Data URI prefix (e.g. <code>data:image/png;base64,</code>) is accepted and stripped automatically.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama vision model to use. Defaults to <code>llama3.2-vision</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">alt_text</td>
                        <td class="text-secondary">string</td>
                        <td>A concise description of the image, suitable for an HTML <code>alt</code> attribute.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request — URL</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/images/alttext \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"url": "https://example.com/photo.jpg"}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example request — base64</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/images/alttext \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d "{\"image\": \"$(base64 -i photo.jpg)\"}" \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "alt_text": "A tabby cat sitting on a wooden windowsill looking out at a rain-covered street."
}</code></pre>
        </div>
    </div>

    <!-- POST /api/images/describe -->
    <div id="images-describe" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/images/describe</code>
        </div>
        <div class="card-body pb-0">
            <p>Returns a detailed description of an image using a vision model via Ollama. Accepts either a publicly accessible image URL or a base64-encoded image. Unlike the alt text endpoint, no length or formatting constraints are applied — the model describes the image freely.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <p class="text-secondary small mb-2">Provide either <code>url</code> or <code>image</code> — at least one is required.</p>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">url</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-warning text-dark">Either</span></td>
                        <td>A publicly accessible URL of the image to describe. Must use <code>http</code> or <code>https</code>.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">image</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-warning text-dark">Either</span></td>
                        <td>A base64-encoded image. Data URI prefix (e.g. <code>data:image/png;base64,</code>) is accepted and stripped automatically.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama vision model to use. Defaults to <code>llama3.2-vision</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">description</td>
                        <td class="text-secondary">string</td>
                        <td>A detailed description of the image.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request — URL</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/images/describe \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"url": "https://example.com/photo.jpg"}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "description": "The image shows a tabby cat with orange and grey striped fur sitting on a wide wooden windowsill. The cat is facing away from the camera, gazing out through a rain-speckled window at a quiet residential street below. The scene is overcast, giving the image a calm, contemplative mood."
}</code></pre>
        </div>
    </div>

    <!-- Status endpoints -->
    <h5 id="status" class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Status</h5>

    <!-- POST /api/status/rewrite -->
    <div id="status-rewrite" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/status/rewrite</code>
        </div>
        <div class="card-body pb-0">
            <p>Generates five alternative rewrites of a given status update text using Ollama. Keeps the meaning intact while improving clarity and tone.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">text</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-danger">Yes</span></td>
                        <td>The status update text to rewrite.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama model to use. Defaults to <code>llama3.2</code>.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">expand</td>
                        <td class="text-secondary">boolean</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>If <code>true</code>, the model may expand on the text where it adds clarity. Defaults to <code>false</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">suggestions</td>
                        <td class="text-secondary">string[]</td>
                        <td>Array of five alternative rewrites.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/status/rewrite \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"text": "The server is currently under high load and some requests may be slow."}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "suggestions": [
    "The server is under heavy load right now, so some requests might take a bit longer than usual.",
    "We're currently seeing high server load — expect slightly slower response times.",
    "Server load is elevated at the moment, which may cause some requests to be slower than normal.",
    "Things are a bit busy on the server side, so you may notice some requests taking longer.",
    "High server load is affecting response times — some requests may be slower than expected."
  ]
}</code></pre>
        </div>
    </div>

    <!-- Tags endpoints -->
    <h5 id="tags" class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Tags</h5>

    <!-- POST /api/tags/generate -->
    <div id="tags-generate" class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="badge text-bg-primary font-monospace fs-6">POST</span>
            <code class="fs-6">/api/tags/generate</code>
        </div>
        <div class="card-body pb-0">
            <p>Reads the given text and suggests a list of relevant tags suitable for use on a blog or social media platform. Each tag is lowercase and contains no spaces. Between 5 and 15 tags are returned depending on the content.</p>

            <h6 class="fw-semibold mt-3 mb-2">Request body <span class="badge text-bg-secondary fw-normal ms-1">application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:120px">Field</th>
                        <th style="width:100px">Type</th>
                        <th style="width:100px">Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">text</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-danger">Yes</span></td>
                        <td>The text to generate hashtags for. Can be a blog post, social media update, or any other body of text.</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">model</td>
                        <td class="text-secondary">string</td>
                        <td><span class="badge text-bg-secondary">No</span></td>
                        <td>Ollama model to use. Defaults to <code>llama3.2</code>.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Response <span class="badge text-bg-secondary fw-normal ms-1">200 application/json</span></h6>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-dark">
                    <tr>
                        <th style="width:140px">Field</th>
                        <th style="width:100px">Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-monospace">tags</td>
                        <td class="text-secondary">string[]</td>
                        <td>An array of suggested tags.</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="fw-semibold mb-2">Example request</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>curl -s -X POST <?= rtrim(base_url(), '/') ?>/api/tags/generate \
  -H "Content-Type: application/json" \
  -H "apikey: &lt;your-api-key&gt;" \
  -d '{"text": "CodeIgniter 4 is a lightweight PHP framework that makes building web applications fast and straightforward. In this post we cover routing, controllers, and connecting to a MySQL database."}' \
  | jq</code></pre>

            <h6 class="fw-semibold mb-2">Example response</h6>
            <pre class="rounded p-3 mb-4 text-wrap"><code>{
  "tags": [
    "php",
    "codeigniter",
    "development",
    "framework",
    "mysql",
    "backend",
    "routing",
    "tutorial"
  ]
}</code></pre>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
