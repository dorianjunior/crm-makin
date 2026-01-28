# WhatsApp Business API Integration

## 📋 Overview

This integration allows the CRM to send and receive WhatsApp messages using the **WhatsApp Cloud API** (Meta Business Platform). Key features include:

- ✅ Send text messages and media (images, videos, documents, audio)
- ✅ Receive incoming messages with webhook support
- ✅ Track message delivery status (sent → delivered → read)
- ✅ Manage conversations with unread counts
- ✅ Auto-link conversations to CRM leads
- ✅ Download and store media files
- ✅ Create CRM activities for messages
- ✅ Multi-account support (multiple WhatsApp numbers per company)

---

## 🔧 Setup

### 1. Meta Business Account Requirements

1. Create a **Meta Business Account** at [business.facebook.com](https://business.facebook.com)
2. Create a **Meta App** in the [Meta for Developers](https://developers.facebook.com) portal
3. Add **WhatsApp** product to your app
4. Complete WhatsApp Business verification (required for production)

### 2. Get WhatsApp Business API Credentials

From the WhatsApp section in your Meta App:

1. **App ID** and **App Secret**: Found in App Settings → Basic
2. **Phone Number ID**: Navigate to WhatsApp → API Setup → Select your phone number
3. **Business Account ID**: Found in WhatsApp → Getting Started
4. **Access Token**: Generate a temporary or permanent access token

### 3. Configure Webhook

1. Go to WhatsApp → Configuration in your Meta App
2. Click "Edit" in Webhook section
3. Set **Callback URL**: `https://your-domain.com/api/webhooks/whatsapp/handle`
4. Set **Verify Token**: A random secure string (save this for `.env`)
5. Subscribe to webhook fields:
   - `messages` (incoming messages)
   - `message_status` (delivery & read receipts)

### 4. Environment Variables

Add to your `.env` file:

```env
WHATSAPP_APP_ID=your_app_id
WHATSAPP_APP_SECRET=your_app_secret
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secure_token
```

### 5. Register WhatsApp Account in CRM

Make a POST request to register your WhatsApp Business number:

```bash
POST /api/social/whatsapp/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone_number_id": "123456789012345",
  "business_account_id": "987654321098765",
  "phone_number": "+5511999999999",
  "display_name": "Company Support",
  "access_token": "your_access_token",
  "verify_token": "your_webhook_verify_token",
  "account_type": "OFFICIAL",
  "quality_rating": "GREEN"
}
```

---

## 📡 API Endpoints

### List WhatsApp Accounts

```http
GET /api/social/whatsapp/accounts
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "phone_number": "+5511999999999",
      "display_name": "Company Support",
      "account_type": "OFFICIAL",
      "quality_rating": "GREEN",
      "is_active": true,
      "created_at": "2025-01-15T10:00:00Z"
    }
  ]
}
```

---

### Get Conversations

```http
GET /api/social/whatsapp/accounts/{accountId}/conversations
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): `active`, `archived`, `blocked`
- `unread_only` (optional): `true` or `false`
- `limit` (optional): Number of conversations (default: 50)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "conversation_id": "5511988888888",
      "contact_name": "João Silva",
      "contact_phone": "+5511988888888",
      "unread_count": 3,
      "last_message_at": "2025-01-15T14:30:00Z",
      "status": "active",
      "lead_id": 42,
      "last_message": {
        "content": "Olá, preciso de ajuda",
        "direction": "inbound",
        "type": "text"
      }
    }
  ]
}
```

---

### Get Messages from Conversation

```http
GET /api/social/whatsapp/conversations/{conversationId}/messages
Authorization: Bearer {token}
```

**Query Parameters:**
- `limit` (optional): Number of messages (default: 100)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "message_id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABIYFjNBQ0Y5RTdCRTg5OTNFMDI1RTEyAA==",
      "direction": "inbound",
      "type": "text",
      "content": "Olá, preciso de ajuda",
      "from_phone": "+5511988888888",
      "to_phone": "+5511999999999",
      "status": "read",
      "sent_at": "2025-01-15T14:30:00Z",
      "delivered_at": "2025-01-15T14:30:02Z",
      "read_at": "2025-01-15T14:31:00Z"
    },
    {
      "id": 2,
      "message_id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABEYEjUzN0YzN0M1M0QzRjg2MDJDAA==",
      "direction": "outbound",
      "type": "image",
      "content": "Produto disponível",
      "media_url": "https://example.com/storage/whatsapp/123456.jpg",
      "media_mime_type": "image/jpeg",
      "from_phone": "+5511999999999",
      "to_phone": "+5511988888888",
      "status": "delivered",
      "sent_at": "2025-01-15T14:32:00Z",
      "delivered_at": "2025-01-15T14:32:05Z"
    }
  ]
}
```

---

### Send Text Message

```http
POST /api/social/whatsapp/accounts/{accountId}/send
Authorization: Bearer {token}
Content-Type: application/json

{
  "to": "+5511988888888",
  "message": "Obrigado pelo contato! Em breve retornaremos."
}
```

**Response:**
```json
{
  "success": true,
  "message_id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABEYEjA3Q0YzQzc1RkFEQ0VDMzNDQQ==",
  "conversation": {
    "id": 1,
    "conversation_id": "5511988888888"
  }
}
```

---

### Send Media Message

```http
POST /api/social/whatsapp/accounts/{accountId}/send-media
Authorization: Bearer {token}
Content-Type: application/json

{
  "to": "+5511988888888",
  "media_url": "https://example.com/images/product.jpg",
  "media_type": "image",
  "caption": "Confira nosso novo produto!"
}
```

**Supported Media Types:**
- `image` (JPEG, PNG - max 5MB)
- `video` (MP4, 3GP - max 16MB)
- `audio` (AAC, MP3, OGG - max 16MB)
- `document` (PDF, DOCX, XLSX - max 100MB)

**Response:**
```json
{
  "success": true,
  "message_id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABEYEjA3Q0YzQzc1RkFEQ0VDMzNDQQ==",
  "conversation": {
    "id": 1,
    "conversation_id": "5511988888888"
  }
}
```

---

### Mark Conversation as Read

```http
POST /api/social/whatsapp/conversations/{conversationId}/mark-read
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Conversation marked as read"
}
```

---

### Disconnect WhatsApp Account

```http
DELETE /api/social/whatsapp/accounts/{accountId}/disconnect
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "WhatsApp account disconnected successfully"
}
```

---

## 🔗 Webhook Events

The webhook endpoint handles two types of events:

### Incoming Messages

When a user sends a message to your WhatsApp Business number, the webhook receives:

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WHATSAPP_BUSINESS_ACCOUNT_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "+5511999999999",
              "phone_number_id": "123456789012345"
            },
            "contacts": [
              {
                "profile": {
                  "name": "João Silva"
                },
                "wa_id": "5511988888888"
              }
            ],
            "messages": [
              {
                "from": "5511988888888",
                "id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABIYFjNBQ0Y5RTdCRTg5OTNFMDI1RTEyAA==",
                "timestamp": "1642258200",
                "type": "text",
                "text": {
                  "body": "Olá, preciso de ajuda"
                }
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

**What happens:**
1. Message is validated and signature is verified
2. `ProcessIncomingWhatsAppMessageJob` is dispatched to queue
3. Job creates/updates conversation
4. Message is saved to database
5. Conversation is auto-linked to CRM lead (if phone matches)
6. CRM activity is created for linked lead
7. Media is downloaded and stored (if applicable)

---

### Message Status Updates

When message status changes (sent → delivered → read):

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WHATSAPP_BUSINESS_ACCOUNT_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "+5511999999999",
              "phone_number_id": "123456789012345"
            },
            "statuses": [
              {
                "id": "wamid.HBgLNTUxMTk4ODg4ODg4OBUCABEYEjA3Q0YzQzc1RkFEQ0VDMzNDQQ==",
                "status": "read",
                "timestamp": "1642258300",
                "recipient_id": "5511988888888"
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

**What happens:**
1. Message status is updated in database (`sent` → `delivered` → `read`)
2. Timestamps are updated (`delivered_at`, `read_at`)
3. Read receipt is visible in conversation

---

## 🔐 Webhook Security

### Signature Verification

All webhook requests are validated using HMAC-SHA256 signature:

1. Meta sends `X-Hub-Signature-256` header
2. Server computes HMAC using `WHATSAPP_APP_SECRET`
3. Signatures are compared
4. Request is rejected if signatures don't match

**Implementation:**
```php
$signature = $request->header('X-Hub-Signature-256');
$payload = $request->getContent();
$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, config('services.whatsapp.app_secret'));

if (!hash_equals($signature, $expectedSignature)) {
    abort(403, 'Invalid signature');
}
```

---

## 🔄 Message Flow

### Sending Messages

1. User calls `/api/social/whatsapp/accounts/{id}/send` endpoint
2. Controller validates input and account ownership
3. `WhatsAppService` sends message via Cloud API
4. Message is saved to database with `status: sent`
5. Conversation is created/updated
6. CRM activity is created (if linked to lead)
7. Webhook receives status update (`delivered`, `read`)
8. Database is updated accordingly

### Receiving Messages

1. Meta sends webhook POST to `/api/webhooks/whatsapp/handle`
2. Signature is verified
3. `ProcessIncomingWhatsAppMessageJob` is dispatched
4. Job extracts message data (text, media, location, etc.)
5. Conversation is found or created
6. Auto-linking: Check if phone matches existing lead
7. Message is saved to database
8. Media is downloaded (if applicable)
9. CRM activity is created (if linked to lead)
10. Conversation metadata is updated (unread count, last message time)

---

## 📊 CRM Integration

### Auto-Linking to Leads

Conversations are automatically linked to CRM leads when:

- Phone number matches lead's primary phone (with or without country code)
- Last 10 digits match (handles different formatting: +5511988888888, 011988888888, 11988888888)

**Example:**
- Lead phone: `+5511988888888`
- Message from: `5511988888888` → ✅ Auto-linked
- Message from: `011988888888` → ✅ Auto-linked (last 10 digits match)
- Message from: `11988888888` → ✅ Auto-linked (last 10 digits match)

### CRM Activity Creation

When a message is sent/received on a linked conversation:

```php
Activity::create([
    'company_id' => $account->company_id,
    'lead_id' => $conversation->lead_id,
    'type' => 'whatsapp_message',
    'description' => $direction === 'inbound' 
        ? "Mensagem recebida via WhatsApp: {$content}"
        : "Mensagem enviada via WhatsApp: {$content}",
    'metadata' => [
        'whatsapp_conversation_id' => $conversation->id,
        'whatsapp_message_id' => $message->id,
        'phone' => $phoneNumber,
        'direction' => $direction,
        'type' => $messageType,
    ],
]);
```

---

## 📁 Media Handling

### Uploading Media (Sending)

**Option 1: URL-based (Recommended)**
```json
{
  "to": "+5511988888888",
  "media_url": "https://example.com/images/product.jpg",
  "media_type": "image",
  "caption": "Check this out!"
}
```

**Option 2: Upload to WhatsApp first, then send**
```bash
# 1. Upload media to WhatsApp
POST https://graph.facebook.com/v18.0/{PHONE_NUMBER_ID}/media
Authorization: Bearer {ACCESS_TOKEN}
Content-Type: multipart/form-data

file: @/path/to/file.jpg

# Response: {"id": "MEDIA_ID"}

# 2. Send message with media ID
POST /api/social/whatsapp/accounts/{accountId}/send-media
{
  "to": "+5511988888888",
  "media_id": "MEDIA_ID",
  "media_type": "image",
  "caption": "Check this out!"
}
```

### Downloading Media (Receiving)

When receiving media messages:

1. Webhook includes `media_id` in message payload
2. `ProcessIncomingWhatsAppMessageJob` downloads media:
   ```php
   GET https://graph.facebook.com/v18.0/{MEDIA_ID}
   Authorization: Bearer {ACCESS_TOKEN}
   ```
3. Response includes download URL (expires in 5 minutes)
4. File is downloaded and stored in `storage/app/public/whatsapp/media/`
5. `media_url` is saved to database

**Storage Structure:**
```
storage/app/public/whatsapp/
├── media/
│   ├── {account_id}/
│   │   ├── {date}/
│   │   │   ├── {unique_id}.jpg
│   │   │   ├── {unique_id}.mp4
│   │   │   └── {unique_id}.pdf
```

---

## 🧪 Testing

### Manual Webhook Testing

**Verify endpoint (GET):**
```bash
curl "https://your-domain.com/api/webhooks/whatsapp/verify?hub.mode=subscribe&hub.verify_token=your_token&hub.challenge=test123"
# Expected: "test123"
```

**Send test message (POST):**
```bash
curl -X POST https://your-domain.com/api/webhooks/whatsapp/handle \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: sha256=COMPUTED_SIGNATURE" \
  -d '{
    "object": "whatsapp_business_account",
    "entry": [{
      "id": "BUSINESS_ACCOUNT_ID",
      "changes": [{
        "value": {
          "messaging_product": "whatsapp",
          "metadata": {
            "phone_number_id": "PHONE_NUMBER_ID"
          },
          "messages": [{
            "from": "5511988888888",
            "id": "wamid.TEST123",
            "timestamp": "1642258200",
            "type": "text",
            "text": {"body": "Test message"}
          }]
        }
      }]
    }]
  }'
```

---

## 🚨 Troubleshooting

### Webhook Not Receiving Messages

**Check:**
1. Webhook URL is correct and publicly accessible (HTTPS required)
2. Verify token matches between Meta App and `.env`
3. Webhook fields are subscribed: `messages`, `message_status`
4. SSL certificate is valid
5. Check Laravel logs: `storage/logs/laravel.log`

**Debug:**
```bash
# Check webhook logs
tail -f storage/logs/laravel.log | grep "WhatsApp"

# Test webhook verification
curl "https://your-domain.com/api/webhooks/whatsapp/verify?hub.mode=subscribe&hub.verify_token=your_token&hub.challenge=test"
```

---

### Messages Not Sending

**Check:**
1. Access token is valid (test with Graph API Explorer)
2. Phone number ID is correct
3. WhatsApp Business account is verified
4. Account quality rating is not `RED` (blocked from sending)
5. Recipient phone number includes country code

**Debug:**
```bash
# Check queue workers are running
php artisan queue:work --queue=social

# Check failed jobs
php artisan queue:failed

# Test API directly
curl -X POST "https://graph.facebook.com/v18.0/PHONE_NUMBER_ID/messages" \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"messaging_product":"whatsapp","to":"5511988888888","type":"text","text":{"body":"Test"}}'
```

---

### Media Download Fails

**Check:**
1. Storage directory is writable: `storage/app/public/whatsapp/media/`
2. Symlink exists: `php artisan storage:link`
3. Media URL is accessed within 5 minutes (Meta URLs expire)
4. Access token has correct permissions

**Debug:**
```bash
# Check storage permissions
ls -la storage/app/public/whatsapp/

# Create directory if missing
mkdir -p storage/app/public/whatsapp/media
chmod -R 775 storage/app/public/whatsapp/
```

---

## 📈 Rate Limits

WhatsApp Cloud API has rate limits:

- **Messages per second**: 80 (standard) / 1,000 (enterprise)
- **Messages per day**: 1,000 (tier 1) → scales up to unlimited based on quality rating
- **Business-initiated conversations**: Limited by message template usage

**Best Practices:**
- Use queue system for bulk sending (already implemented)
- Monitor quality rating (stored in `whatsapp_accounts.quality_rating`)
- Implement backoff strategy for rate limit errors (HTTP 429)

---

## 🔄 Queue Configuration

WhatsApp jobs run on the `social` queue:

**Start queue worker:**
```bash
php artisan queue:work --queue=social --timeout=60 --tries=3
```

**Jobs:**
- `ProcessIncomingWhatsAppMessageJob`: Process incoming messages from webhook (60s timeout, 3 tries)
- `SendWhatsAppMessageJob`: Send outbound messages (60s timeout, 3 tries)

**Supervisor configuration (production):**
```ini
[program:whatsapp-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=social --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue-worker.log
stopwaitsecs=3600
```

---

## 📚 Additional Resources

- [WhatsApp Cloud API Documentation](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Business Platform](https://business.facebook.com/)
- [Graph API Explorer](https://developers.facebook.com/tools/explorer/)
- [Webhook Setup Guide](https://developers.facebook.com/docs/graph-api/webhooks/getting-started)
- [Message Templates](https://developers.facebook.com/docs/whatsapp/business-management-api/message-templates)

---

## 🎯 Next Steps

After setup:

1. **Test webhook**: Send a test message to your WhatsApp Business number
2. **Monitor logs**: Check `storage/logs/laravel.log` for incoming messages
3. **Configure templates**: Create message templates in Meta Business Manager (required for business-initiated conversations)
4. **Set up queue workers**: Configure Supervisor for production
5. **Implement auto-responses**: Use message templates for common queries
6. **Analytics**: Monitor conversation metrics and response times
