<?php require_once __DIR__.'/../config/bootstrap.php'; Auth::requireLogin();
$spec=file_get_contents(__DIR__.'/../docs/openapi.yaml');
?>
<!doctype html><html><head><meta charset="utf-8"><title>LedgerFlow API Documentation</title><style>body{font-family:Arial,sans-serif;background:#0b1020;color:#e9eefc;margin:0}main{max-width:1100px;margin:40px auto;padding:24px}pre{white-space:pre-wrap;background:#121a2f;padding:20px;border-radius:12px;overflow:auto}a{color:#66d9ef}</style></head><body><main><h1>LedgerFlow API</h1><p>OpenAPI specification and Postman collection are included with the project.</p><p><a href="../docs/openapi.yaml">OpenAPI YAML</a> · <a href="../docs/postman_collection.json">Postman Collection</a></p><pre><?=e($spec)?></pre></main></body></html>
