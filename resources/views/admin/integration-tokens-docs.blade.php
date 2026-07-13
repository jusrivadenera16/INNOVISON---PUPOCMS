@extends('layouts.admin')

@section('title', 'Integration Tokens - Developer Documentation')
@section('disable_voice_inputs', 'true')

@section('content')
<style>
    .docs-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .docs-header {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid rgba(127, 29, 45, 0.1);
    }

    .docs-header h1 {
        margin: 0 0 8px;
        font-size: 2rem;
        font-weight: 900;
        color: #7f1d2d;
    }

    .docs-header p {
        margin: 0;
        color: #6b7280;
        font-size: 1.1rem;
    }

    html[data-theme="dark"] .docs-header {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    html[data-theme="dark"] .docs-header h1 {
        color: #f3d6da;
    }

    html[data-theme="dark"] .docs-header p {
        color: #cbd5e1;
    }

    .docs-content {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr);
        gap: 28px;
        align-items: start;
    }

    .docs-main {
        display: grid;
        gap: 32px;
        margin-left: 0;
    }

    @media (max-width: 1024px) {
        .docs-sidebar {
            position: relative !important;
            width: auto !important;
            top: auto !important;
            left: auto !important;
            max-height: none !important;
            margin-bottom: 32px;
        }
        .docs-main {
            margin-left: 0;
        }
    }

    .docs-sidebar {
        position: sticky;
        width: 250px;
        top: 24px;
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        z-index: 1;
    }

    .docs-nav {
        background: rgba(255, 255, 255, 0.96);
        border-radius: 12px;
        border: 1px solid rgba(127, 29, 45, 0.1);
        padding: 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    html[data-theme="dark"] .docs-nav {
        background: rgba(35, 17, 25, 0.94);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .docs-nav h3 {
        margin: 0 0 12px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 900;
        color: #6b7280;
    }

    html[data-theme="dark"] .docs-nav h3 {
        color: #cbd5e1;
    }

    .docs-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .docs-nav li {
        margin: 0;
        padding: 0;
    }

    .docs-nav a {
        display: block;
        padding: 8px 12px;
        color: #6b7280;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .docs-nav a:hover {
        background: rgba(127, 29, 45, 0.05);
        color: #7f1d2d;
        border-left-color: #7f1d2d;
    }

    html[data-theme="dark"] .docs-nav a {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .docs-nav a:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #f3d6da;
        border-left-color: #f3d6da;
    }

    .docs-section {
        background: rgba(255, 255, 255, 0.96);
        border-radius: 16px;
        border: 1px solid rgba(127, 29, 45, 0.1);
        padding: 28px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    html[data-theme="dark"] .docs-section {
        background: rgba(35, 17, 25, 0.94);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .docs-section h2 {
        margin: 0 0 16px;
        font-size: 1.5rem;
        font-weight: 900;
        color: #7f1d2d;
        padding-bottom: 12px;
        border-bottom: 2px solid rgba(127, 29, 45, 0.1);
    }

    html[data-theme="dark"] .docs-section h2 {
        color: #f3d6da;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .docs-section h3 {
        margin: 20px 0 12px;
        font-size: 1.1rem;
        font-weight: 800;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .docs-section h3 {
        color: #f3d6da;
    }

    .docs-section p, .docs-section li {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 12px;
    }

    html[data-theme="dark"] .docs-section p,
    html[data-theme="dark"] .docs-section li {
        color: #cbd5e1;
    }

    .docs-code {
        background: #1f2937;
        color: #f3f4f6;
        padding: 16px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        overflow-x: auto;
        margin: 16px 0;
        line-height: 1.5;
    }

    .docs-code-label {
        display: inline-block;
        background: #374151;
        color: #9ca3af;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        margin-bottom: 8px;
    }

    .docs-info {
        background: #dbeafe;
        border-left: 4px solid #2563eb;
        padding: 16px;
        border-radius: 6px;
        margin: 16px 0;
    }

    html[data-theme="dark"] .docs-info {
        background: rgba(37, 99, 235, 0.1);
        border-left-color: #60a5fa;
    }

    .docs-warning {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 16px;
        border-radius: 6px;
        margin: 16px 0;
    }

    html[data-theme="dark"] .docs-warning {
        background: rgba(245, 158, 11, 0.1);
        border-left-color: #fbbf24;
    }

    .docs-list {
        list-style: none;
        padding: 0;
        margin: 16px 0;
    }

    .docs-list li {
        padding: 8px 0 8px 28px;
        position: relative;
    }

    .docs-list li:before {
        content: "→";
        position: absolute;
        left: 0;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .docs-list li:before {
        color: #f3d6da;
    }

    .docs-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
    }

    .docs-table th {
        background: #7f1d2d;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 700;
    }

    .docs-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(127, 29, 45, 0.1);
    }

    .docs-table tr:nth-child(even) {
        background: rgba(127, 29, 45, 0.02);
    }

    html[data-theme="dark"] .docs-table tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.02);
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        background: linear-gradient(135deg, #8a1220, #6a0e18);
        color: white;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(138, 18, 32, 0.24);
        position: relative;
        overflow: hidden;
    }

    .back-button {
        font-size: 0;
    }

    .back-button::before {
        content: "\2190 Back to API Dashboard";
        font-size: 0.95rem;
    }

    .back-button:hover {
        animation: sweep 0.6s ease-out forwards;
    }

    @keyframes sweep {
        0% {
            background: linear-gradient(135deg, #8a1220, #6a0e18);
            color: white;
        }
        50% {
            background: linear-gradient(90deg, #fbbf24, #fbbf24);
        }
        100% {
            background: #fbbf24;
            color: #8a1220;
        }
    }

    html[data-theme="dark"] .back-button {
        background: linear-gradient(135deg, #8a1220, #6a0e18);
        color: white;
        box-shadow: 0 4px 12px rgba(138, 18, 32, 0.3);
    }

    html[data-theme="dark"] .back-button:hover {
        animation: sweep 0.6s ease-out forwards;
    }
</style>

<div class="docs-container">
    <a href="{{ route('admin.api-testing') }}" class="back-button">
        ← Back to Integration Tokens
    </a>

    <div class="docs-header">
        <h1>Bearer Token Authentication</h1>
        <p>Guide for integrating with INNOVISON PUPOCMS API using Bearer tokens</p>
    </div>

    <div class="docs-content">
        <nav class="docs-sidebar">
            <div class="docs-nav">
                <h3>Contents</h3>
                <ul>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#headers">Required Headers</a></li>
                    <li><a href="#authentication">Authentication</a></li>
                    <li><a href="#requests">Making Requests</a></li>
                    <li><a href="#responses">Response Format</a></li>
                    <li><a href="#errors">Error Handling</a></li>
                    <li><a href="#examples">Code Examples</a></li>
                    <li><a href="#security">Security</a></li>
                </ul>
            </div>
        </nav>

        <main class="docs-main">
            <!-- Overview -->
            <section class="docs-section" id="overview">
                <h2>Overview</h2>
                <p>The INNOVISON PUPOCMS API uses Bearer token authentication for external system integration. Each external system (RIS, IMS, PUPT Website, etc.) receives a unique token that must be included in all API requests.</p>
                <div class="docs-info">
                    <strong>ℹ️ Info:</strong> Tokens are long-lived and do not expire. They should be treated as secrets and stored securely.
                </div>
            </section>

            <!-- Headers -->
            <section class="docs-section" id="headers">
                <h2>Required Headers</h2>
                <p>Every API request must include these headers:</p>
                <div class="docs-code">
                    <div class="docs-code-label">REQUIRED HEADERS</div>
X-External-Api-Key: YOUR_TOKEN_HERE
X-External-System: SYSTEM_KEY
Content-Type: application/json
                </div>
                <h3>Header Details</h3>
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Header</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>X-External-Api-Key</code></td>
                            <td>Bearer token for authentication</td>
                            <td><code>abc123xyz...789</code></td>
                        </tr>
                        <tr>
                            <td><code>X-External-System</code></td>
                            <td>Unique system identifier</td>
                            <td><code>ris</code>, <code>ims</code>, <code>pupt_website</code></td>
                        </tr>
                        <tr>
                            <td><code>Content-Type</code></td>
                            <td>Request body format</td>
                            <td><code>application/json</code></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Authentication -->
            <section class="docs-section" id="authentication">
                <h2>Authentication</h2>
                <h3>Token Format</h3>
                <p>Tokens are long random strings generated using Laravel Sanctum. They are provided by the clinic administrator and should never be shared publicly.</p>
                <div class="docs-warning">
                    <strong>⚠️ Security:</strong> Never commit tokens to version control. Always use environment variables.
                </div>
                <h3>Storing Tokens</h3>
                <p>Store your token in a <code>.env</code> file:</p>
                <div class="docs-code">
                    <div class="docs-code-label">.ENV FILE</div>
API_TOKEN=your_token_here_keep_secret
API_URL=https://clinic.domain.com/api/external
SYSTEM_KEY=ris
                </div>
                <p>Add <code>.env</code> to your <code>.gitignore</code>:</p>
                <div class="docs-code">
                    <div class="docs-code-label">.GITIGNORE</div>
.env
.env.local
.env.*.local
                </div>
            </section>

            <!-- Requests -->
            <section class="docs-section" id="requests">
                <h2>Making Requests</h2>
                <h3>Request Format</h3>
                <p>All requests follow this pattern:</p>
                <div class="docs-code">
                    <div class="docs-code-label">REQUEST PATTERN</div>
METHOD: GET | POST | PUT | DELETE
URL: https://clinic-domain.com/api/external/[endpoint]

Headers:
  X-External-Api-Key: your_token_here
  X-External-System: ris
  Content-Type: application/json

Body (for POST/PUT):
  { JSON data }
                </div>
                <h3>HTTP Methods</h3>
                <ul class="docs-list">
                    <li><strong>GET</strong> - Retrieve data</li>
                    <li><strong>POST</strong> - Create new resources</li>
                    <li><strong>PUT</strong> - Update existing resources</li>
                    <li><strong>DELETE</strong> - Remove resources</li>
                </ul>
            </section>

            <!-- Responses -->
            <section class="docs-section" id="responses">
                <h2>Response Format</h2>
                <h3>Success Response (200 OK)</h3>
                <div class="docs-code">
                    <div class="docs-code-label">JSON</div>
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  }
}
                </div>
                <h3>Error Response</h3>
                <div class="docs-code">
                    <div class="docs-code-label">JSON</div>
{
  "success": false,
  "message": "Authentication failed",
  "error_code": 401
}
                </div>
            </section>

            <!-- Error Handling -->
            <section class="docs-section" id="errors">
                <h2>Error Handling</h2>
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Cause</th>
                            <th>Solution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>401</td>
                            <td>Unauthorized</td>
                            <td>Invalid or revoked token</td>
                            <td>Request new token from admin</td>
                        </tr>
                        <tr>
                            <td>403</td>
                            <td>Forbidden</td>
                            <td>Wrong system key</td>
                            <td>Verify X-External-System header</td>
                        </tr>
                        <tr>
                            <td>400</td>
                            <td>Bad Request</td>
                            <td>Missing headers or invalid data</td>
                            <td>Check all required headers</td>
                        </tr>
                        <tr>
                            <td>404</td>
                            <td>Not Found</td>
                            <td>Endpoint doesn't exist</td>
                            <td>Verify endpoint URL</td>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>Server Error</td>
                            <td>Server-side issue</td>
                            <td>Contact admin support</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Examples -->
            <section class="docs-section" id="examples">
                <h2>Code Examples</h2>

                <h3>JavaScript / Fetch</h3>
                <div class="docs-code">
                    <div class="docs-code-label">JAVASCRIPT</div>
const token = process.env.API_TOKEN;
const systemKey = process.env.SYSTEM_KEY;
const apiUrl = process.env.API_URL;

const response = await fetch(`${apiUrl}/admin/profile`, {
  method: 'GET',
  headers: {
    'X-External-Api-Key': token,
    'X-External-System': systemKey,
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
                </div>

                <h3>Python / Requests</h3>
                <div class="docs-code">
                    <div class="docs-code-label">PYTHON</div>
import requests
import os

headers = {
    'X-External-Api-Key': os.getenv('API_TOKEN'),
    'X-External-System': os.getenv('SYSTEM_KEY'),
    'Content-Type': 'application/json'
}

response = requests.get(
    f"{os.getenv('API_URL')}/admin/profile",
    headers=headers
)
data = response.json()
                </div>

                <h3>cURL</h3>
                <div class="docs-code">
                    <div class="docs-code-label">BASH</div>
curl -X GET "https://clinic.domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: your_token" \
  -H "X-External-System: ris" \
  -H "Content-Type: application/json"
                </div>
            </section>

            <!-- Security -->
            <section class="docs-section" id="security">
                <h2>Security Best Practices</h2>
                <h3>✅ DO</h3>
                <ul class="docs-list">
                    <li>Use HTTPS only (never HTTP)</li>
                    <li>Store tokens in environment variables</li>
                    <li>Add .env to .gitignore</li>
                    <li>Rotate tokens periodically</li>
                    <li>Implement request timeouts (30 seconds)</li>
                    <li>Log errors but never log tokens</li>
                </ul>
                <h3>❌ DON'T</h3>
                <ul class="docs-list">
                    <li>Hardcode tokens in source code</li>
                    <li>Commit .env files to Git</li>
                    <li>Share tokens via email or chat</li>
                    <li>Log token values</li>
                    <li>Use same token for multiple systems</li>
                    <li>Forget to revoke old tokens</li>
                </ul>
            </section>
        </main>
    </div>
</div>

<script>
    document.querySelectorAll('.docs-nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
</script>
@endsection
