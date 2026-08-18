<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'fallback_models' => array_values(array_filter(array_map('trim', explode(',', env('GEMINI_FALLBACK_MODELS', 'gemini-2.5-flash,gemini-2.0-flash,gemini-2.0-flash-lite'))))),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'auto'),
    ],

    'botpress' => [
        'client_id' => env('BOTPRESS_CLIENT_ID'),
    ],

    'local_login' => [
        'enabled' => filter_var(env('LOCAL_LOGIN_ENABLED', false), FILTER_VALIDATE_BOOL),
        'allowed_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('LOCAL_LOGIN_ALLOWED_HOSTS', 'localhost,127.0.0.1,::1'))
        ))),
    ],

    'idp' => [
        'enabled' => filter_var(env('IDP_ENABLED', false), FILTER_VALIDATE_BOOL),
        'base_url' => rtrim((string) env('IDP_BASE_URL', ''), '/'),
        'client_id' => env('IDP_CLIENT_ID'),
        'client_secret' => env('IDP_CLIENT_SECRET'),
        'redirect_uri' => env('IDP_REDIRECT_URI'),
        'authorize_path' => env('IDP_AUTHORIZE_PATH', '/api/v1/auth/authorize'),
        'authorize_include_redirect_uri' => filter_var(env('IDP_AUTHORIZE_INCLUDE_REDIRECT_URI', false), FILTER_VALIDATE_BOOL),
        'authorize_response_type' => env('IDP_AUTHORIZE_RESPONSE_TYPE', 'code'),
        'authorize_scope' => env('IDP_AUTHORIZE_SCOPE', ''),
        'token_path' => env('IDP_TOKEN_PATH', '/api/v1/auth/token'),
        'token_auth_method' => env('IDP_TOKEN_AUTH_METHOD', 'client_secret_post'),
        'token_include_redirect_uri' => filter_var(env('IDP_TOKEN_INCLUDE_REDIRECT_URI', false), FILTER_VALIDATE_BOOL),
        'token_grant_type' => env('IDP_TOKEN_GRANT_TYPE', 'authorization_code'),
        'use_pkce' => filter_var(env('IDP_USE_PKCE', true), FILTER_VALIDATE_BOOL),
        'pkce_challenge_method' => env('IDP_PKCE_CHALLENGE_METHOD', 'S256'),
        'profile_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('IDP_PROFILE_PATHS', '/me,/auth/me,/userinfo'))))),
        'validate_token_path' => env('IDP_VALIDATE_TOKEN_PATH', '/api/validate-token'),
        'role_prefix' => env('IDP_ROLE_PREFIX', ''),
        'local_superadmin_identifiers' => array_values(array_filter(array_map('trim', explode(',', (string) env('IDP_LOCAL_SUPERADMIN_IDENTIFIERS', 'pupocms,pupocms2027@gmail.com'))))),
        'access_cookie_name' => env('IDP_ACCESS_COOKIE_NAME', 'access_token'),
        'refresh_cookie_name' => env('IDP_REFRESH_COOKIE_NAME', 'refresh_token'),
        'access_cookie_minutes' => (int) env('IDP_ACCESS_COOKIE_MINUTES', 60),
        'refresh_cookie_minutes' => (int) env('IDP_REFRESH_COOKIE_MINUTES', 10080),
        'session_validation_cache_seconds' => (int) env('IDP_SESSION_VALIDATION_CACHE_SECONDS', 300),
        'cookie_secure' => filter_var(env('IDP_COOKIE_SECURE', true), FILTER_VALIDATE_BOOL),
        'cookie_same_site' => env('IDP_COOKIE_SAME_SITE', 'Lax'),
        'logout_path' => env('IDP_LOGOUT_PATH', '/logout'),
        'logout_url' => env('IDP_LOGOUT_URL'),
    ],

    'emergency' => [
        'enabled' => filter_var(env('EMERGENCY_ACCESS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'email' => env('EMERGENCY_ADMIN_EMAIL'),
        'password' => env('EMERGENCY_ADMIN_PASSWORD'),
        'password_hash' => env('EMERGENCY_ADMIN_PASSWORD_HASH'),
        'password_reset_key' => env('EMERGENCY_PASSWORD_RESET_KEY'),
        'role' => env('EMERGENCY_ADMIN_ROLE', 'admin'),
        'totp_issuer' => env('EMERGENCY_TOTP_ISSUER', 'PUP Taguig Clinic'),
        'email_otp_recipient' => env('EMERGENCY_EMAIL_OTP_RECIPIENT'),
    ],

    'student_notifications' => [
        'enabled' => filter_var(env('STUDENT_NOTIFICATION_EMAILS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'local_test_recipient' => env('STUDENT_NOTIFICATION_EMAIL_TEST_RECIPIENT'),
        'appointment_reminder_minutes' => (int) env('APPOINTMENT_EMAIL_REMINDER_MINUTES', 15),
    ],

    'integration_pin' => [
        'reset_key' => env('INTEGRATION_PIN_RESET_KEY'),
    ],

    'external_admin_profile' => [
        'api_key' => env('EXTERNAL_ADMIN_PROFILE_API_KEY'),
        'header' => env('EXTERNAL_ADMIN_PROFILE_HEADER', 'X-External-Api-Key'),
        'system_header' => env('EXTERNAL_ADMIN_PROFILE_SYSTEM_HEADER', 'X-External-System'),
        'system_keys' => array_filter(
            json_decode((string) env('EXTERNAL_ADMIN_PROFILE_SYSTEM_KEYS', '{}'), true) ?: [],
            fn ($value) => is_string($value) && trim($value) !== ''
        ),
    ],

    'temp_api_testing' => [
        'url' => env('TEMP_API_TESTING_URL'),
        'api_key' => env('TEMP_API_TESTING_API_KEY'),
        'header' => env('TEMP_API_TESTING_HEADER', 'X-External-Api-Key'),
        'timeout' => (int) env('TEMP_API_TESTING_TIMEOUT', 20),
    ],

    'api_testing' => [
        'allowed_emails' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('API_TESTING_ALLOWED_EMAILS', 'pupocms2027@gmail.com'))
        ))),
        'allowed_roles' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('API_TESTING_ALLOWED_ROLES', 'superadmin'))
        ))),
    ],

    'pupt_flss' => [
        'faculty_profiles_url' => env('PUPT_FLSS_FACULTY_PROFILES_URL', 'https://flss.alquatrilixbsit2027.com/api/v1/faculty-profiles'),
        'system_id' => env('PUPT_FLSS_SYSTEM_ID', 'ocms'),
        'secret_key' => env('PUPT_FLSS_SECRET_KEY'),
        'timeout' => (int) env('PUPT_FLSS_TIMEOUT', 30),
    ],

    'guisis' => [
        'base_url' => env('GUISIS_API_BASE_URL', 'https://api.guisis.dllbsit2027.com/api/v1'),
        'client_id' => env('GUISIS_M2M_CLIENT_ID'),
        'client_secret' => env('GUISIS_M2M_CLIENT_SECRET'),
        'timeout' => (int) env('GUISIS_TIMEOUT', 20),
    ],

    'puptas' => [
        'api_url' => env('PUPTAS_API_URL'),
        'client_id' => env('PUPTAS_CLIENT_ID'),
        'client_secret' => env('PUPTAS_CLIENT_SECRET'),
        'webhook_secret' => env('PUPTAS_WEBHOOK_SECRET', env('MEDICAL_WEBHOOK_SECRET')),
        'signature_header' => env('PUPTAS_SIGNATURE_HEADER', 'X-Medical-Signature'),
        'timestamp_header' => env('PUPTAS_TIMESTAMP_HEADER', 'X-Timestamp'),
        'nonce_header' => env('PUPTAS_NONCE_HEADER', 'X-Nonce'),
        'timeout' => (int) env('PUPTAS_TIMEOUT', 20),
        'scope' => env('PUPTAS_SCOPE', 'medical-read medical-write'),
        'token_url' => env('PUPTAS_TOKEN_URL'),
    ],

];
