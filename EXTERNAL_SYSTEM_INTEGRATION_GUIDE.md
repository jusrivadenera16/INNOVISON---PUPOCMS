# External System Integration Guide
## INNOVISON PUPOCMS - API Token Authentication

---

## 📋 Table of Contents
1. [Admin Setup (For Clinic)](#admin-setup)
2. [API Integration (For External Systems)](#api-integration)
3. [Code Examples](#code-examples)
4. [Environment Setup](#environment-setup)
5. [Testing & Troubleshooting](#testing--troubleshooting)

---

## 🔑 Admin Setup

### Step 1: Access Integration Tokens Manager
```
URL: https://your-clinic-domain.com/admin/integration-tokens
```

**Alternative Path:**
1. Log in to Admin Panel → `https://your-clinic-domain.com/admin`
2. Click **API Dashboard** 
3. Click **🔐 Integration Tokens** button

### Step 2: Create an Integration Client

1. Click the **"Add Client"** button
2. Fill in the form:
   - **System Key:** Unique identifier (lowercase, no spaces)
     - Example: `ris`, `ims`, `pupt_website`, `accre`, `dental`
   - **System Name:** Display name for the system
     - Example: `RIS`, `IMS`, `PUPT Website`
3. Click **"Create Client"** button
4. The client will appear in the Integrations list

### Step 3: Generate API Token

1. Click on the system in the Integrations list
2. Click **"Generate Token"** button
3. Confirm the action
4. Copy the token using **"Copy"** button
5. Share the token securely with the external system administrator

### Step 4: Track Token Usage

- **View Status:** System shows "Connected" when token is generated
- **Last Used:** Timestamp updates automatically when token is used
- **Token ID:** Unique identifier for audit trail
- **Revoke Old:** Click "Revoke" to invalidate old tokens

---

## 📡 API Integration

### Base URL
```
https://your-clinic-domain.com/api/external
```

### Required Headers (EVERY Request)

All API requests must include these exact headers:

```
X-External-Api-Key: YOUR_TOKEN_HERE
X-External-System: SYSTEM_KEY
Content-Type: application/json
```

### Example Request Pattern

```
METHOD: GET | POST | PUT | DELETE
URL: https://clinic-domain.com/api/external/[endpoint]

Headers:
  X-External-Api-Key: your_actual_token_here
  X-External-System: ris
  Content-Type: application/json
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  }
}
```

**Error Response (401, 403, 400, etc):**
```json
{
  "success": false,
  "message": "Authentication failed",
  "error_code": 401
}
```

---

## 💻 Code Examples

### cURL (Bash)

```bash
#!/bin/bash

# Set variables
API_TOKEN="your_token_here_abc123xyz..."
SYSTEM_KEY="ris"
API_URL="https://clinic.domain.com/api/external"

# GET Request Example
curl -X GET "${API_URL}/admin/profile" \
  -H "X-External-Api-Key: ${API_TOKEN}" \
  -H "X-External-System: ${SYSTEM_KEY}" \
  -H "Content-Type: application/json"

# POST Request Example
curl -X POST "${API_URL}/admin/create" \
  -H "X-External-Api-Key: ${API_TOKEN}" \
  -H "X-External-System: ${SYSTEM_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com"
  }'
```

### JavaScript / Node.js

```javascript
// Using Fetch API
const API_TOKEN = process.env.RIS_API_TOKEN;
const SYSTEM_KEY = 'ris';
const API_URL = 'https://clinic.domain.com/api/external';

// GET Request
async function getAdminProfile() {
  const response = await fetch(`${API_URL}/admin/profile`, {
    method: 'GET',
    headers: {
      'X-External-Api-Key': API_TOKEN,
      'X-External-System': SYSTEM_KEY,
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

// POST Request
async function createAdmin(data) {
  const response = await fetch(`${API_URL}/admin/create`, {
    method: 'POST',
    headers: {
      'X-External-Api-Key': API_TOKEN,
      'X-External-System': SYSTEM_KEY,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });

  return await response.json();
}

// Usage
try {
  const profile = await getAdminProfile();
  console.log('Admin Profile:', profile);
} catch (error) {
  console.error('Error:', error.message);
}
```

### Python

```python
import requests
import os
from dotenv import load_dotenv

load_dotenv()

# Configuration
API_TOKEN = os.getenv('RIS_API_TOKEN')
SYSTEM_KEY = 'ris'
API_URL = 'https://clinic.domain.com/api/external'

# Headers
headers = {
    'X-External-Api-Key': API_TOKEN,
    'X-External-System': SYSTEM_KEY,
    'Content-Type': 'application/json'
}

# GET Request
def get_admin_profile():
    response = requests.get(
        f'{API_URL}/admin/profile',
        headers=headers
    )
    
    if response.status_code != 200:
        raise Exception(f"HTTP {response.status_code}: {response.text}")
    
    return response.json()

# POST Request
def create_admin(data):
    response = requests.post(
        f'{API_URL}/admin/create',
        headers=headers,
        json=data
    )
    
    return response.json()

# Usage
try:
    profile = get_admin_profile()
    print("Admin Profile:", profile)
except Exception as e:
    print(f"Error: {e}")
```

### PHP

```php
<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

$apiToken = env('RIS_API_TOKEN');
$systemKey = 'ris';
$apiUrl = 'https://clinic.domain.com/api/external';

$headers = [
    'X-External-Api-Key' => $apiToken,
    'X-External-System' => $systemKey,
    'Content-Type' => 'application/json'
];

// GET Request
function getAdminProfile($headers, $apiUrl) {
    $response = Http::withHeaders($headers)
        ->get("{$apiUrl}/admin/profile");
    
    return $response->json();
}

// POST Request
function createAdmin($headers, $apiUrl, $data) {
    $response = Http::withHeaders($headers)
        ->post("{$apiUrl}/admin/create", $data);
    
    return $response->json();
}

// Usage
try {
    $profile = getAdminProfile($headers, $apiUrl);
    echo json_encode($profile);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### Java

```java
import okhttp3.*;
import java.io.IOException;

public class IntegrationClient {
    private static final String API_TOKEN = System.getenv("RIS_API_TOKEN");
    private static final String SYSTEM_KEY = "ris";
    private static final String API_URL = "https://clinic.domain.com/api/external";
    private static final OkHttpClient client = new OkHttpClient();

    public static void getAdminProfile() throws IOException {
        Request request = new Request.Builder()
            .url(API_URL + "/admin/profile")
            .addHeader("X-External-Api-Key", API_TOKEN)
            .addHeader("X-External-System", SYSTEM_KEY)
            .addHeader("Content-Type", "application/json")
            .build();

        try (Response response = client.newCall(request).execute()) {
            if (!response.isSuccessful()) {
                throw new IOException("Unexpected code " + response);
            }
            System.out.println(response.body().string());
        }
    }

    public static void createAdmin(String json) throws IOException {
        RequestBody body = RequestBody.create(json, MediaType.parse("application/json"));
        Request request = new Request.Builder()
            .url(API_URL + "/admin/create")
            .post(body)
            .addHeader("X-External-Api-Key", API_TOKEN)
            .addHeader("X-External-System", SYSTEM_KEY)
            .addHeader("Content-Type", "application/json")
            .build();

        try (Response response = client.newCall(request).execute()) {
            System.out.println(response.body().string());
        }
    }
}
```

### C# / .NET

```csharp
using System;
using System.Net.Http;
using System.Threading.Tasks;

class IntegrationClient {
    private static readonly string API_TOKEN = Environment.GetEnvironmentVariable("RIS_API_TOKEN");
    private static readonly string SYSTEM_KEY = "ris";
    private static readonly string API_URL = "https://clinic.domain.com/api/external";

    static async Task Main() {
        using (var client = new HttpClient()) {
            // Configure headers
            client.DefaultRequestHeaders.Add("X-External-Api-Key", API_TOKEN);
            client.DefaultRequestHeaders.Add("X-External-System", SYSTEM_KEY);

            // GET Request
            try {
                var response = await client.GetAsync($"{API_URL}/admin/profile");
                var content = await response.Content.ReadAsStringAsync();
                Console.WriteLine(content);
            } catch (Exception ex) {
                Console.WriteLine($"Error: {ex.Message}");
            }

            // POST Request
            var data = new { firstName = "John", lastName = "Doe" };
            var json = System.Text.Json.JsonSerializer.Serialize(data);
            var content_post = new StringContent(json, System.Text.Encoding.UTF8, "application/json");
            var response_post = await client.PostAsync($"{API_URL}/admin/create", content_post);
            Console.WriteLine(await response_post.Content.ReadAsStringAsync());
        }
    }
}
```

---

## ⚙️ Environment Setup

### Store Token in .env File

**For Node.js / JavaScript:**
```env
RIS_API_TOKEN=your_actual_token_here_abc123xyz...
RIS_API_URL=https://clinic.domain.com/api/external
RIS_SYSTEM_KEY=ris

IMS_API_TOKEN=your_actual_token_here_def456uvw...
IMS_API_URL=https://clinic.domain.com/api/external
IMS_SYSTEM_KEY=ims

PUPT_API_TOKEN=your_actual_token_here_ghi789rst...
PUPT_API_URL=https://clinic.domain.com/api/external
PUPT_SYSTEM_KEY=pupt_website
```

**For Python:**
```env
RIS_API_TOKEN=your_actual_token_here_abc123xyz...
RIS_API_URL=https://clinic.domain.com/api/external
RIS_SYSTEM_KEY=ris
```

**For PHP / Laravel:**
```env
RIS_API_TOKEN=your_actual_token_here_abc123xyz...
RIS_API_URL=https://clinic.domain.com/api/external
RIS_SYSTEM_KEY=ris
```

### Load Environment Variables

**Node.js:**
```javascript
require('dotenv').config();
const apiToken = process.env.RIS_API_TOKEN;
```

**Python:**
```python
from dotenv import load_dotenv
import os
load_dotenv()
api_token = os.getenv('RIS_API_TOKEN')
```

**PHP:**
```php
$apiToken = env('RIS_API_TOKEN');
```

---

## 🧪 Testing & Troubleshooting

### Test Your Integration

Use this command to verify the setup:

```bash
curl -i -X GET "https://clinic.domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: YOUR_ACTUAL_TOKEN" \
  -H "X-External-System: ris" \
  -H "Content-Type: application/json"
```

### Expected Response

**Success (200 OK):**
```
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true,
  "data": { ... }
}
```

### Common Errors & Solutions

| Error | Status | Cause | Solution |
|-------|--------|-------|----------|
| `Unauthorized` | 401 | Invalid or revoked token | Request new token from admin |
| `Forbidden` | 403 | Wrong system key | Verify X-External-System header |
| `Bad Request` | 400 | Missing headers | Include both required headers |
| `Not Found` | 404 | Wrong endpoint | Check API documentation |
| `Server Error` | 500 | Server issue | Contact admin support |
| `Token Expired` | 401 | Token revoked | Get new token from admin |
| `Malformed JSON` | 400 | Invalid JSON body | Check JSON syntax |

### Debug Checklist

- [ ] Token copied correctly (no extra spaces)
- [ ] System key spelling is exact (case-sensitive)
- [ ] Both headers are present in request
- [ ] Using HTTPS (not HTTP)
- [ ] Token stored in environment variable
- [ ] Token not hardcoded in source code
- [ ] Token not shared in version control
- [ ] API endpoint URL is correct
- [ ] Request body JSON is valid (if POST/PUT)
- [ ] Content-Type header is application/json

### Getting Help

1. Check token hasn't been revoked
2. Verify endpoint is correct
3. Test with curl command first
4. Check headers in network tab (browser DevTools)
5. Contact clinic admin with:
   - Error message
   - HTTP status code
   - Request URL
   - System key used

---

## 📊 Token Management

### Viewing Tokens (Admin)
- Dashboard: `https://clinic.domain.com/admin/integration-tokens`
- Shows all systems and tokens
- Displays last used timestamp
- View creation date

### Generating Tokens (Admin)
1. Click on system in Integrations list
2. Click **"Generate Token"** button
3. Confirm action
4. Copy token to clipboard
5. Share securely with external system

### Revoking Tokens (Admin)
1. Click on system with token to revoke
2. Click **"Revoke"** button
3. Confirm action
4. Old token becomes invalid immediately
5. External system must request new token

### Token Security
- ✅ Tokens stored securely in database
- ✅ Only plaintext shown at creation
- ✅ Use environment variables for storage
- ✅ Rotate every 6-12 months
- ✅ Revoke when team member leaves
- ✅ Never commit to version control

---

## 🔐 Security Best Practices

### For External Systems

**DO:**
- ✅ Use HTTPS only (never HTTP)
- ✅ Store token in environment variables
- ✅ Use .env files with .gitignore
- ✅ Implement request timeout (30 seconds)
- ✅ Log errors but not tokens
- ✅ Rotate tokens annually
- ✅ Use separate token per system
- ✅ Implement retry logic with backoff

**DON'T:**
- ❌ Hardcode tokens in source code
- ❌ Commit tokens to Git/GitHub
- ❌ Share tokens via email
- ❌ Log token values
- ❌ Use same token for multiple systems
- ❌ Forget to revoke old tokens
- ❌ Ignore security warnings
- ❌ Reuse tokens across environments

### Network Security
- Use VPN for sensitive environments
- Implement IP whitelisting if available
- Use SSL certificate verification
- Monitor unusual API access patterns
- Set up alerts for failed auth attempts

---

## 📞 Support & Reference

### Quick Links
- **Admin Dashboard:** `https://clinic.domain.com/admin/integration-tokens`
- **API Base URL:** `https://clinic.domain.com/api/external`
- **Login Page:** `https://clinic.domain.com/login`

### Contact Information
- **Clinic IT Admin:** [contact email/phone]
- **Technical Support:** [support details]
- **Documentation:** See INTEGRATION_TOKENS_GUIDE.md

### Version Information
- **System Version:** INNOVISON PUPOCMS v1.0.0
- **API Version:** v1
- **Last Updated:** July 2026
- **Status:** Production Ready ✅

---

**Document Version:** 1.0.0  
**Last Updated:** July 9, 2026  
**Status:** ✅ Production Ready
