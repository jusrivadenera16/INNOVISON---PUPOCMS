# Integration Tokens Manager - Complete Setup Guide

## 📋 Overview
This guide explains how to use the Integration Tokens Manager in the INNOVISON PUPOCMS to securely generate and manage API tokens for external system integrations (RIS, IMS, PUPT Website, ACCRE, Dental).

---

## 🔐 Admin Access - Integration Tokens Manager

### URL Endpoint
```
https://your-clinic-domain.com/admin/integration-tokens
```

### Alternative Access Path
1. Log in to Admin Panel
2. Go to **API Dashboard** (or click the settings icon)
3. Click the **🔐 Integration Tokens** button in the navigation

### Required Permissions
- Admin access with API Testing capability
- Superadmin role recommended for full functionality

---

## 🛠️ How to Generate Tokens (Admin Instructions)

### Step 1: Access the Integration Tokens Manager
Navigate to: `https://your-clinic-domain.com/admin/integration-tokens`

### Step 2: Find the System You Need
The page displays cards for each available integration system:
- ✅ ACCRE
- ✅ Dental
- ✅ PUPT Website
- ✅ IMS
- ✅ RIS

### Step 3: Generate a New Token

For each system card:

1. Click the **🔄 Generate New** button
2. Confirm the action in the dialog
3. The new token will be generated and displayed
4. **Copy** button will appear to copy the full token

### Step 4: Share Token with External System

Click **📋 Copy** to copy the complete token and share it securely with the external system team.

### Step 5: Revoke Old Tokens (Optional)

When you generate a new token, the old one can be revoked by clicking **❌ Revoke** to invalidate it for security.

---

## 📤 For External Systems (RIS, IMS, PUPT Website, etc.)

### How to Use the API Token

Once you receive the API token from the admin, use it in your API requests as follows:

### Request Format

**Base API URL:**
```
https://your-clinic-domain.com/api/external
```

**Required Headers:**
```
X-External-Api-Key: <YOUR_TOKEN_HERE>
X-External-System: <SYSTEM_KEY>
```

### Complete Example Request

```bash
# Example: Fetching admin profile data
curl -X GET "https://your-clinic-domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: YOUR_TOKEN_HERE" \
  -H "X-External-System: ris" \
  -H "Content-Type: application/json"
```

### Available System Keys

Use these exact keys in the `X-External-System` header:

| System | System Key | Token Holder |
|--------|-----------|--------------|
| ACCRE | `accre` | ACCRE System Admin |
| Dental | `dental` | Dental Clinic Admin |
| PUPT Website | `pupt_website` | PUPT Web Team |
| IMS | `ims` | IMS Administrator |
| RIS | `ris` | RIS Department |

### Example Requests by System

**RIS System Request:**
```bash
curl -X GET "https://clinic.domain.com/api/external/admin/profile" \
  -H "X-External-Api-Key: abc123xyz789..." \
  -H "X-External-System: ris"
```

**IMS System Request:**
```bash
curl -X POST "https://clinic.domain.com/api/external/admin/create" \
  -H "X-External-Api-Key: def456uvw012..." \
  -H "X-External-System: ims" \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```

**PUPT Website Request:**
```bash
curl -X GET "https://clinic.domain.com/api/external/admin/list" \
  -H "X-External-Api-Key: ghi789rst345..." \
  -H "X-External-System: pupt_website"
```

---

## 🔄 Token Lifecycle

### Token Creation
- **Automatic:** Tokens are created instantly when you click "Generate New"
- **Format:** Long random string (50+ characters)
- **Validity:** Tokens are immediately active and usable

### Token Usage Tracking
- **Last Used:** Admin panel shows when each token was last used
- **Created Date:** Shows exact date/time token was generated
- **Access Count:** Track which systems are actively using tokens

### Token Revocation
- **Manual Revocation:** Click "Revoke" to immediately invalidate all tokens
- **Effect:** Revoked tokens will return `401 Unauthorized` errors
- **No Recovery:** Revoked tokens cannot be restored
- **New Token Required:** External systems must request a new token

### Best Practices
1. **Generate** a new token for each external system
2. **Track** token creation dates and usage
3. **Revoke** tokens when:
   - System no longer needs access
   - Token is compromised
   - Team member who has token leaves
   - Changing security policies
4. **Rotate** tokens periodically (every 6-12 months)

---

## 🔒 Security Information

### Token Storage
- Tokens are stored securely in the database using Laravel Sanctum
- Only hashed versions are stored (plain tokens shown only on creation)
- Tokens are never logged or cached

### Best Security Practices
- ✅ **Use HTTPS only** - Never send tokens over HTTP
- ✅ **Treat as passwords** - Keep tokens confidential
- ✅ **Use environment variables** - Store in .env, not in code
- ✅ **Rotate regularly** - Generate new tokens every 6-12 months
- ✅ **Revoke immediately** - If token is exposed
- ✅ **Use IP whitelisting** - If available in your network setup
- ❌ **Don't commit tokens** - Keep out of version control
- ❌ **Don't share via email** - Use secure password managers
- ❌ **Don't hardcode** - Always use environment variables

### Example: Secure Storage in .env

For the RIS system, store the token like this:

**File: `.env`**
```
RIS_API_TOKEN=your_token_here_abc123xyz789...
RIS_API_URL=https://your-clinic-domain.com/api/external
RIS_SYSTEM_KEY=ris
```

**File: `config.php` or similar**
```php
$token = env('RIS_API_TOKEN');
$headers = [
    'X-External-Api-Key' => $token,
    'X-External-System' => 'ris',
];
```

---

## 🧪 Testing the Integration

### Test Your Token Setup

Use this simple test request:

```bash
# Replace with your actual token and system key
TOKEN="your_token_here"
SYSTEM="ris"
URL="https://clinic.domain.com/api/external/admin/profile"

curl -X GET "$URL" \
  -H "X-External-Api-Key: $TOKEN" \
  -H "X-External-System: $SYSTEM" \
  -H "Content-Type: application/json"
```

### Expected Success Response
- **Status Code:** 200 OK
- **Response:** JSON data with admin/system information

### Common Error Responses

| Error | Cause | Solution |
|-------|-------|----------|
| `401 Unauthorized` | Invalid/revoked token | Request new token from admin |
| `403 Forbidden` | Wrong system key | Verify X-External-System header |
| `400 Bad Request` | Missing headers | Include both required headers |
| `404 Not Found` | Endpoint doesn't exist | Check API documentation |
| `500 Server Error` | Server issue | Contact admin support |

---

## 📞 Support & Troubleshooting

### Common Issues

**Issue: "Invalid Token" Error**
- Solution: Ensure you copied the entire token correctly
- Action: Request a new token from admin

**Issue: "System Not Found" Error**
- Solution: Verify the system key is correct (check the table above)
- Action: Use exact spelling from System Key column

**Issue: "Missing Headers" Error**
- Solution: Ensure both required headers are included:
  - `X-External-Api-Key`
  - `X-External-System`
- Action: Check request headers in your client

**Issue: Token Was Revoked**
- Solution: The admin revoked the old token
- Action: Request a new token from admin

### Contact Admin

If you encounter issues:
1. Check the error message carefully
2. Verify all header names are spelled correctly
3. Confirm token is still valid (not revoked)
4. Contact clinic IT administrator for support

**Admin Support URL:** `https://clinic.domain.com/admin/integration-tokens`

---

## 📊 Token Management Dashboard

### Admin Dashboard Features

The Integration Tokens Manager provides:

✅ **View All Tokens**
- See all systems and their current tokens
- Status (Active/Inactive)
- Creation date and last used date

✅ **Quick Copy**
- Single-click copy of full token to clipboard
- No need to manually select/copy text

✅ **Generate New Tokens**
- One-click token generation
- Instant activation
- Automatic validity

✅ **Revoke Old Tokens**
- Safely invalidate old tokens
- Immediate effect
- Audit trail maintained

✅ **System Key Reference**
- Quick reference for all system keys
- Use these exactly in API requests

---

## 📋 Checklist for Setup

### For Admin (Clinic IT)
- [ ] Access Integration Tokens Manager
- [ ] Generate token for RIS system
- [ ] Generate token for IMS system
- [ ] Generate token for PUPT Website
- [ ] Generate token for ACCRE (if needed)
- [ ] Generate token for Dental (if needed)
- [ ] Copy tokens securely
- [ ] Provide tokens to respective team leads
- [ ] Document who received which token
- [ ] Test token access with external systems

### For External Systems (RIS, IMS, PUPT, etc.)
- [ ] Receive token from clinic admin
- [ ] Store token in environment variables
- [ ] Implement authentication headers
- [ ] Include headers in all API requests
- [ ] Test connection with sample request
- [ ] Verify `200 OK` response
- [ ] Document API endpoints you're using
- [ ] Set up error handling for `401` responses
- [ ] Schedule token rotation (every 6-12 months)
- [ ] Report issues to clinic admin

---

## 📞 Quick Reference

### URLs
- **Admin Panel:** `https://your-clinic-domain.com/admin`
- **Integration Tokens:** `https://your-clinic-domain.com/admin/integration-tokens`
- **API Base:** `https://your-clinic-domain.com/api/external`

### Required Headers (Every Request)
```
X-External-Api-Key: <token>
X-External-System: <system_key>
Content-Type: application/json
```

### System Keys
- RIS: `ris`
- IMS: `ims`
- PUPT Website: `pupt_website`
- ACCRE: `accre`
- Dental: `dental`

### HTTP Methods Supported
- GET (retrieve data)
- POST (create data)
- PUT (update data)
- DELETE (remove data)

---

## 🎓 Advanced Usage

### Using in JavaScript/Node.js

```javascript
const token = process.env.RIS_API_TOKEN;
const systemKey = 'ris';
const baseUrl = process.env.API_URL;

const response = await fetch(`${baseUrl}/admin/profile`, {
  method: 'GET',
  headers: {
    'X-External-Api-Key': token,
    'X-External-System': systemKey,
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
```

### Using in Python

```python
import requests
import os

token = os.getenv('RIS_API_TOKEN')
system_key = 'ris'
base_url = os.getenv('API_URL')

headers = {
    'X-External-Api-Key': token,
    'X-External-System': system_key,
    'Content-Type': 'application/json'
}

response = requests.get(f'{base_url}/admin/profile', headers=headers)
data = response.json()
```

### Using in PHP

```php
$token = env('RIS_API_TOKEN');
$systemKey = 'ris';
$baseUrl = env('API_URL');

$headers = [
    'X-External-Api-Key' => $token,
    'X-External-System' => $systemKey,
    'Content-Type' => 'application/json'
];

$response = Http::withHeaders($headers)
    ->get("{$baseUrl}/admin/profile");
```

---

## ✅ Verification Checklist

- [x] All syntax validated
- [x] Database schema compatible (uses Sanctum)
- [x] Routes properly registered
- [x] Controller methods functional
- [x] Blade template complete
- [x] Token generation working
- [x] Token revocation working
- [x] UI fully responsive
- [x] Dark mode supported
- [x] Error handling implemented
- [x] Security best practices included

---

**Last Updated:** 2026-07-09  
**Version:** 1.0.0  
**Status:** ✅ Ready for Production

---
