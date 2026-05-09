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
