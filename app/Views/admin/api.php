<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-3">

    <div class="border-bottom border-1 mb-4 pb-3">
        <h2 class="mb-0">API Reference</h2>
        <p class="text-secondary mb-0 mt-1">Available API endpoints for external integrations.</p>
    </div>

    <!-- Authentication -->
    <div class="card mb-4">
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

    <!-- Images endpoints -->
    <h5 class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Images</h5>

    <!-- POST /api/images/alttext -->
    <div class="card mb-4">
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
    <div class="card mb-4">
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
    <h5 class="text-secondary text-uppercase fw-semibold mb-3" style="font-size:0.75rem;letter-spacing:.08em">Status</h5>

    <!-- POST /api/status/rewrite -->
    <div class="card mb-4">
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

</div>
<?= $this->endSection() ?>
