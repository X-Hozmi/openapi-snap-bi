# SNAP BI Virtual Account Payment Gateway

![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)
![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg)
![Redis Supported](https://img.shields.io/badge/Redis-Supported-dc382d.svg)
![SNAP BI Compliant](https://img.shields.io/badge/SNAP_BI-Compliant-brightgreen.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## What the project does

This project is a high-performance, **B2B Virtual Account (VA) Payment Gateway** backend built with the **Laravel** framework. It is designed to be fully compliant with Bank Indonesia's **Standar Nasional Open API Pembayaran (SNAP)**. 

The system acts as a secure bridge between enterprise billing systems and banking networks, facilitating standardized Virtual Account inquiries and payment notifications. It natively handles SNAP's complex cryptographic requirements, API logging, and transactional idempotency.

## Why the project is useful

Integrating with Bank Indonesia's SNAP specifications can be technically challenging due to strict security and structural requirements. This project simplifies that process by providing a ready-to-use, scalable architecture:

* **SNAP BI Compliance:** Out-of-the-box adherence to SNAP data structures, JSON formats, and standardized HTTP error codes (e.g., `4017300`, `2002400`).
* **Multi-Layer Security Integration:**
  * **OAuth 2.0 Client Credentials Grant** for secure B2B access tokens.
  * **Asymmetric Signature (SHA256withRSA)** validation for authentication endpoints.
  * **Symmetric Signature (HMAC-SHA512)** validation for transactional endpoints (Inquiry & Payment).
* **High Performance & Scalability:** Implements an asynchronous Redis-backed Job Queue (`PelunasanOtomatis`) to handle high-concurrency payment settlements without blocking the main API thread.
* **Idempotency Support:** Built-in `X-EXTERNAL-ID` header processing to prevent duplicate transactions and handle network timeouts safely.
* **Privacy & Audit Logging:** Features a custom middleware (`UnifiedAPILoggerMiddleware`) paired with a `SanitizerService` to automatically redact sensitive headers and payloads (like private keys and tokens) before logging them to the database.

## How users can get started

### Prerequisites

Ensure your environment meets the following requirements:
* PHP 8.3 or higher
* Composer
* MySQL or PostgreSQL
* Redis (for caching and job queues)
* OpenSSL PHP Extension

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-org/openapi-snap-bi.git
   cd openapi-snap-bi
   ```

2. **Install dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Configure your environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up SNAP credentials and Database in `.env`:**
   Update your `.env` file with your database credentials, Redis configuration, and partner keys required by SNAP BI:
   ```env
   # Database Configuration
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=openapi_snap_bi
   DB_USERNAME=root
   DB_PASSWORD=secret

   # Redis Configuration (Required for Queues)
   REDIS_CLIENT=predis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   QUEUE_CONNECTION=redis

   # SNAP BI Partner Configuration
   OAUTH_OPENAPI_CHANNEL_ID=
   OAUTH_OPENAPI_PARTNER_ID=
   OAUTH_OPENAPI_CLIENT_KEY="your-client-id-here"
   OAUTH_OPENAPI_CLIENT_SECRET="your-client-secret-here"
   OAUTH_OPENAPI_PUBLIC_KEY_FILE="storage/keys/public.key"
   ```

5. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

6. **Start the Redis Queue Worker:**
   ```bash
   php artisan queue:work redis --queue=pelunasan
   ```

7. **Serve the application:**
   ```bash
   php artisan serve
   ```

### Usage Examples

The API exposes endpoints under the `/openapi/v1.0/` prefix. Below is a high-level overview of the request flow.

**1. Get Access Token (B2B)**
```http
POST /openapi/v1.0/access-token/b2b
Content-Type: application/json
X-TIMESTAMP: 2025-03-29T21:06:32+07:00
X-CLIENT-KEY: your-client-id
X-SIGNATURE: <base64_encoded_rsa_signature>

{
  "grantType": "client_credentials"
}
```

**2. Virtual Account Inquiry**
```http
POST /openapi/v1.0/transfer-va/inquiry
Authorization: Bearer <your_access_token>
X-TIMESTAMP: 2025-03-29T21:06:32+07:00
X-SIGNATURE: <base64_encoded_hmac_sha512_signature>
X-PARTNER-ID: 51112
X-EXTERNAL-ID: 1234567890

{
  "partnerServiceId": " 51112",
  "customerNo": "1234567890",
  "virtualAccountNo": " 511121234567890",
  "trxDateInit": "2025-03-29T21:06:32+07:00"
}
```

**3. Virtual Account Payment**
```http
POST /openapi/v1.0/transfer-va/payment
Authorization: Bearer <your_access_token>
X-TIMESTAMP: 2025-03-29T21:06:32+07:00
X-SIGNATURE: <base64_encoded_hmac_sha512_signature>
X-PARTNER-ID: 51112
X-EXTERNAL-ID: 1234567890

{
  "partnerServiceId": " 51112",
  "customerNo": "1234567890",
  "virtualAccountNo": " 511121234567890",
  "trxDateTime": "2025-03-29T21:06:32+07:00"
}
```

## Where users can get help

* **Bank Indonesia SNAP Documentation:** Familiarize yourself with the official [SNAP specifications](https://developer.bi.go.id/) for detailed parameter and signature generation rules.
* **Internal Documentation:** Check our `docs/` folder for system-specific guides:
  * [Architecture & Flowcharts](docs/ARCHITECTURE.md)
  * [Generating Signatures locally](docs/SIGNATURES.md)
* **Issues:** If you encounter a bug or have a feature request, please [open an issue](../../issues) on GitHub.

<!-- ## Contributing

We welcome contributions to make this gateway more robust! If you'd like to contribute, please read our [Contribution Guidelines](docs/CONTRIBUTING.md) first. 

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Ensure all PHPUnit tests pass (`php artisan test`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request -->