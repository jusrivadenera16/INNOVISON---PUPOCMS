# INNOVISON PUPOCMS - API Integration Guide
## For RIS, IMS, PUPT Website, ACCRE, and Dental Systems

---

## 📌 Quick Start

### You Will Receive:
- **API Token:** A secure token for authentication
- **System Key:** Your unique system identifier
- **API Base URL:** `https://your-clinic-domain.com/api/external`

### Required Headers (Add to ALL Requests):
```
X-External-Api-Key: YOUR_TOKEN_HERE
X-External-System: YOUR_SYSTEM_KEY
Content-Type: application/json
```

### Test Your Connection:
```bash
curl -X GET "https://clinic.domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: YOUR_TOKEN" \
  -H "X-External-System: YOUR_SYSTEM_KEY" \
  -H "Content-Type: application/json"
```

---

## 🔑 Setup Instructions

### Step 1: Store Token Securely

**Create a `.env` file in your project:**
```env
# API Configuration
API_TOKEN=your_token_here_do_not_commit
API_URL=https://clinic.domain.com/api/external
SYSTEM_KEY=your_system_key
```

**Add `.env` to `.gitignore`:**
```gitignore
.env
.env.local
.env.*.local
```

### Step 2: Load Variables in Your Code

**Node.js / JavaScript:**
```javascript
require('dotenv').config();
const token = process.env.API_TOKEN;
const apiUrl = process.env.API_URL;
const systemKey = process.env.SYSTEM_KEY;
```

**Python:**
```python
from dotenv import load_dotenv
import os
load_dotenv()
token = os.getenv('API_TOKEN')
api_url = os.getenv('API_URL')
system_key = os.getenv('SYSTEM_KEY')
```

**PHP:**
```php
require 'vendor/autoload.php';
$token = env('API_TOKEN');
$apiUrl = env('API_URL');
$systemKey = env('SYSTEM_KEY');
```

### Step 3: Make API Requests

See code examples below for your language.

---

## 💻 Code Examples

### JavaScript / Node.js

```javascript
const fetch = require('node-fetch');

async function callClinicAPI(endpoint, method = 'GET', data = null) {
  const headers = {
    'X-External-Api-Key': process.env.API_TOKEN,
    'X-External-System': process.env.SYSTEM_KEY,
    'Content-Type': 'application/json'
  };

  const options = {
    method,
    headers,
    timeout: 30000 // 30 second timeout
  };

  if (data && (method === 'POST' || method === 'PUT')) {
    options.body = JSON.stringify(data);
  }

  try {
    const response = await fetch(`${process.env.API_URL}${endpoint}`, options);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    console.error('API Error:', error.message);
    throw error;
  }
}

// Usage Examples
async function main() {
  try {
    // GET request
    const profile = await callClinicAPI('/admin/profile');
    console.log('Profile:', profile);

    // POST request
    const newAdmin = await callClinicAPI('/admin/create', 'POST', {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com'
    });
    console.log('Created:', newAdmin);
  } catch (error) {
    console.error('Failed:', error);
  }
}

main();
```

### Python

```python
import requests
import os
from dotenv import load_dotenv

load_dotenv()

def call_clinic_api(endpoint, method='GET', data=None):
    """
    Call clinic API with proper authentication
    """
    headers = {
        'X-External-Api-Key': os.getenv('API_TOKEN'),
        'X-External-System': os.getenv('SYSTEM_KEY'),
        'Content-Type': 'application/json'
    }

    url = f"{os.getenv('API_URL')}{endpoint}"
    timeout = 30

    try:
        if method == 'GET':
            response = requests.get(url, headers=headers, timeout=timeout)
        elif method == 'POST':
            response = requests.post(url, headers=headers, json=data, timeout=timeout)
        elif method == 'PUT':
            response = requests.put(url, headers=headers, json=data, timeout=timeout)
        elif method == 'DELETE':
            response = requests.delete(url, headers=headers, timeout=timeout)
        else:
            raise ValueError(f"Unsupported method: {method}")

        if response.status_code >= 400:
            raise Exception(f"HTTP {response.status_code}: {response.text}")

        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"API Error: {e}")
        raise

# Usage Examples
if __name__ == '__main__':
    try:
        # GET request
        profile = call_clinic_api('/admin/profile')
        print('Profile:', profile)

        # POST request
        new_admin = call_clinic_api('/admin/create', 'POST', {
            'first_name': 'John',
            'last_name': 'Doe',
            'email': 'john@example.com'
        })
        print('Created:', new_admin)
    except Exception as e:
        print(f"Error: {e}")
```

### PHP / Laravel

```php
<?php

use Illuminate\Support\Facades\Http;

class ClinicAPI {
    protected $token;
    protected $systemKey;
    protected $apiUrl;

    public function __construct() {
        $this->token = env('API_TOKEN');
        $this->systemKey = env('SYSTEM_KEY');
        $this->apiUrl = env('API_URL');
    }

    public function call($endpoint, $method = 'GET', $data = []) {
        $headers = [
            'X-External-Api-Key' => $this->token,
            'X-External-System' => $this->systemKey,
            'Content-Type' => 'application/json'
        ];

        $url = $this->apiUrl . $endpoint;

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30);

            if ($method === 'GET') {
                $response = $response->get($url);
            } elseif ($method === 'POST') {
                $response = $response->post($url, $data);
            } elseif ($method === 'PUT') {
                $response = $response->put($url, $data);
            } elseif ($method === 'DELETE') {
                $response = $response->delete($url);
            }

            if ($response->failed()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            return $response->json();
        } catch (Exception $e) {
            \Log::error("Clinic API Error: " . $e->getMessage());
            throw $e;
        }
    }
}

// Usage Example
$api = new ClinicAPI();

try {
    // GET request
    $profile = $api->call('/admin/profile');
    echo json_encode($profile);

    // POST request
    $newAdmin = $api->call('/admin/create', 'POST', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com'
    ]);
    echo json_encode($newAdmin);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### cURL (Command Line)

```bash
#!/bin/bash

# Configuration
API_TOKEN="your_token_here"
SYSTEM_KEY="your_system_key"
API_URL="https://clinic.domain.com/api/external"

# Function to call API
call_api() {
    local endpoint=$1
    local method=${2:-GET}
    local data=$3

    curl -X "$method" \
        -H "X-External-Api-Key: $API_TOKEN" \
        -H "X-External-System: $SYSTEM_KEY" \
        -H "Content-Type: application/json" \
        ${data:+-d "$data"} \
        "$API_URL$endpoint"
}

# Examples
echo "=== GET Request ==="
call_api "/admin/profile"

echo ""
echo "=== POST Request ==="
call_api "/admin/create" "POST" '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
}'
```

### Java

```java
import okhttp3.*;
import com.google.gson.Gson;
import java.io.IOException;

public class ClinicAPIClient {
    private String apiToken;
    private String systemKey;
    private String apiUrl;
    private OkHttpClient client = new OkHttpClient();
    private Gson gson = new Gson();

    public ClinicAPIClient(String token, String key, String url) {
        this.apiToken = token;
        this.systemKey = key;
        this.apiUrl = url;
    }

    public String callAPI(String endpoint, String method, String body) throws IOException {
        Headers headers = new Headers.Builder()
            .add("X-External-Api-Key", apiToken)
            .add("X-External-System", systemKey)
            .add("Content-Type", "application/json")
            .build();

        Request.Builder requestBuilder = new Request.Builder()
            .url(apiUrl + endpoint)
            .headers(headers);

        if ("POST".equals(method)) {
            requestBuilder.post(RequestBody.create(body, MediaType.parse("application/json")));
        } else if ("PUT".equals(method)) {
            requestBuilder.put(RequestBody.create(body, MediaType.parse("application/json")));
        } else if ("DELETE".equals(method)) {
            requestBuilder.delete();
        } else {
            requestBuilder.get();
        }

        try (Response response = client.newCall(requestBuilder.build()).execute()) {
            if (!response.isSuccessful()) {
                throw new IOException("Unexpected code " + response);
            }
            return response.body().string();
        }
    }

    // Usage
    public static void main(String[] args) throws IOException {
        ClinicAPIClient client = new ClinicAPIClient(
            "YOUR_TOKEN",
            "YOUR_SYSTEM_KEY",
            "https://clinic.domain.com/api/external"
        );

        String profile = client.callAPI("/admin/profile", "GET", null);
        System.out.println("Profile: " + profile);
    }
}
```

### C# / .NET

```csharp
using System;
using System.Net.Http;
using System.Threading.Tasks;
using Newtonsoft.Json;

class ClinicAPIClient {
    private string apiToken;
    private string systemKey;
    private string apiUrl;
    private HttpClient httpClient;

    public ClinicAPIClient(string token, string key, string url) {
        apiToken = token;
        systemKey = key;
        apiUrl = url;
        httpClient = new HttpClient();
    }

    public async Task<string> CallAPIAsync(string endpoint, string method = "GET", object data = null) {
        var request = new HttpRequestMessage(new HttpMethod(method), apiUrl + endpoint);
        
        request.Headers.Add("X-External-Api-Key", apiToken);
        request.Headers.Add("X-External-System", systemKey);

        if (data != null && (method == "POST" || method == "PUT")) {
            request.Content = new StringContent(
                JsonConvert.SerializeObject(data),
                System.Text.Encoding.UTF8,
                "application/json"
            );
        }

        try {
            using (var response = await httpClient.SendAsync(request)) {
                response.EnsureSuccessStatusCode();
                return await response.Content.ReadAsStringAsync();
            }
        } catch (Exception ex) {
            Console.WriteLine($"Error: {ex.Message}");
            throw;
        }
    }

    // Usage
    static async Task Main() {
        var client = new ClinicAPIClient(
            "YOUR_TOKEN",
            "YOUR_SYSTEM_KEY",
            "https://clinic.domain.com/api/external"
        );

        var profile = await client.CallAPIAsync("/admin/profile");
        Console.WriteLine("Profile: " + profile);
    }
}
```

---

## 🔒 Security Guidelines

### ✅ DO:
- Store token in `.env` file only
- Use environment variables in code
- Implement request timeouts (30 seconds)
- Log errors, but never log token values
- Use HTTPS connections only
- Validate SSL certificates
- Rotate tokens annually
- Revoke tokens immediately if exposed

### ❌ DON'T:
- Hardcode token in source code
- Commit `.env` file to Git
- Share token via email
- Log token in error messages
- Use same token across environments
- Expose token in API responses
- Share token in Slack/Teams
- Forget to add `.env` to `.gitignore`

---

## 🧪 Testing

### Test Endpoint
```bash
curl -X GET "https://clinic.domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: YOUR_TOKEN" \
  -H "X-External-System: YOUR_SYSTEM_KEY" \
  -H "Content-Type: application/json"
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Clinic Name",
    "email": "admin@clinic.com"
  }
}
```

### Error Responses
| Status | Error | Meaning |
|--------|-------|---------|
| 401 | Unauthorized | Token is invalid or revoked |
| 403 | Forbidden | Wrong system key |
| 400 | Bad Request | Missing headers or invalid data |
| 404 | Not Found | Endpoint doesn't exist |
| 500 | Server Error | Server-side issue |

---

## 📞 Error Handling

### Implement Retry Logic

```javascript
async function callAPIWithRetry(endpoint, method = 'GET', data = null, maxRetries = 3) {
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await callClinicAPI(endpoint, method, data);
    } catch (error) {
      if (attempt === maxRetries) throw error;
      
      // Exponential backoff: 1s, 2s, 4s
      const delay = Math.pow(2, attempt - 1) * 1000;
      console.log(`Attempt ${attempt} failed. Retrying in ${delay}ms...`);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
}
```

### Handle Different Error Types

```javascript
async function robustAPICall(endpoint, method = 'GET', data = null) {
  try {
    return await callClinicAPI(endpoint, method, data);
  } catch (error) {
    if (error.message.includes('401')) {
      console.error('Token is invalid or revoked. Please request new token.');
    } else if (error.message.includes('403')) {
      console.error('System key is incorrect.');
    } else if (error.message.includes('timeout')) {
      console.error('Request timeout. Server may be slow.');
    } else {
      console.error('Unexpected error:', error.message);
    }
    throw error;
  }
}
```

---

## 📊 Integration Checklist

- [ ] Received API token from clinic admin
- [ ] Received system key from clinic admin
- [ ] Created `.env` file with credentials
- [ ] Added `.env` to `.gitignore`
- [ ] Implemented authentication headers
- [ ] Tested GET request with curl
- [ ] Tested with your code/language
- [ ] Implemented error handling
- [ ] Implemented retry logic
- [ ] Set request timeout (30s)
- [ ] Tested in staging environment
- [ ] Reviewed security practices
- [ ] Documented API endpoints used
- [ ] Deployed to production
- [ ] Monitored API responses

---

## 🆘 Troubleshooting

### Problem: 401 Unauthorized
**Cause:** Invalid or revoked token  
**Solution:** Request new token from clinic admin

### Problem: 403 Forbidden
**Cause:** Wrong system key  
**Solution:** Verify X-External-System header matches provided system key (case-sensitive)

### Problem: 400 Bad Request
**Cause:** Missing headers or invalid JSON  
**Solution:** Ensure both headers present and JSON is valid

### Problem: Connection refused
**Cause:** Wrong URL or network issue  
**Solution:** Verify URL is correct: `https://clinic.domain.com/api/external`

### Problem: Timeout errors
**Cause:** Server slow or network issue  
**Solution:** Implement retry logic with exponential backoff

---

## 📚 API Endpoints

Contact your clinic admin for specific endpoint documentation. Common endpoints may include:

- `GET /admin/profile` - Get admin information
- `POST /admin/create` - Create new admin
- `PUT /admin/{id}` - Update admin
- `DELETE /admin/{id}` - Delete admin
- `GET /data/...` - Retrieve various data

---

## 📞 Support

If you encounter issues:

1. **Check this guide first** - Most common issues are documented
2. **Verify your token** - Make sure it wasn't revoked
3. **Test with curl** - Use the test endpoint above
4. **Contact clinic admin** - Provide:
   - Error message
   - HTTP status code
   - Endpoint called
   - System key used
   - Approximate time of error

---

## ✅ You're Ready!

Once you've completed the integration checklist above, you're ready to integrate with INNOVISON PUPOCMS.

**Questions?** Contact your clinic administrator.

---

**Document Version:** 1.0  
**Created:** July 9, 2026  
**Status:** Ready for External Systems
