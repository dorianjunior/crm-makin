# Integração com Instagram

Este documento descreve como configurar e usar a integração com Instagram para o CRM Makin.

## Visão Geral

A integração com Instagram permite:
- ✅ Conectar contas do Instagram Business/Creator
- ✅ Buscar mensagens diretas (DMs) via Meta Graph API
- ✅ Visualizar posts recentes da conta
- ✅ Vincular automaticamente mensagens a leads no CRM
- ✅ Receber mensagens em tempo real via webhooks
- ✅ Gerenciar tokens OAuth com refresh automático

## Pré-requisitos

### 1. Criar Aplicativo no Meta Developer

1. Acesse [Meta for Developers](https://developers.facebook.com/)
2. Crie um novo aplicativo ou use um existente
3. Adicione os produtos:
   - **Instagram Basic Display** (para posts e perfil)
   - **Instagram Messaging API** (para DMs - apenas Business/Creator)

### 2. Configurar OAuth e Webhooks

#### OAuth Configuration
- **Redirect URI**: `https://seudominio.com/api/social/instagram/callback`
- **Scope**: `user_profile,user_media`
- **Client Type**: Web

#### Webhook Configuration
- **Callback URL**: `https://seudominio.com/api/webhooks/instagram/handle`
- **Verify Token**: (gerar um token aleatório seguro)
- **Subscription Fields**: `messages`, `messaging_postbacks`, `message_echoes`

### 3. Variáveis de Ambiente

Adicione no arquivo `.env`:

```env
INSTAGRAM_APP_ID=seu_app_id_aqui
INSTAGRAM_APP_SECRET=seu_app_secret_aqui
INSTAGRAM_REDIRECT_URI="${APP_URL}/api/social/instagram/callback"
INSTAGRAM_WEBHOOK_VERIFY_TOKEN=seu_token_de_verificacao_aleatorio
```

## Fluxo de Autenticação

### 1. Obter URL de Autorização

**Endpoint**: `GET /api/social/instagram/auth-url`

**Response**:
```json
{
  "auth_url": "https://api.instagram.com/oauth/authorize?client_id=..."
}
```

### 2. Conectar Conta

Após o usuário autorizar no Instagram, ele será redirecionado para sua aplicação com um `code`.

**Endpoint**: `POST /api/social/instagram/connect`

**Request**:
```json
{
  "code": "authorization_code_from_instagram",
  "company_id": 1
}
```

**Response**:
```json
{
  "message": "Instagram account connected successfully",
  "account": {
    "id": 1,
    "username": "suaempresa",
    "account_type": "BUSINESS",
    "is_active": true
  }
}
```

## Endpoints da API

### Listar Contas Conectadas

**Endpoint**: `GET /api/social/instagram/accounts`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "accounts": [
    {
      "id": 1,
      "username": "suaempresa",
      "account_type": "BUSINESS",
      "profile_picture_url": "https://...",
      "followers_count": 1500,
      "is_active": true,
      "is_connected": true,
      "token_expires_at": "2025-03-28T21:33:19Z",
      "created_at": "2025-01-28T21:33:19Z"
    }
  ]
}
```

### Buscar Mensagens

**Endpoint**: `GET /api/social/instagram/accounts/{account}/messages?limit=50`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "account_id": 1,
  "messages": [
    {
      "id": 123,
      "conversation_id": "987654321",
      "sender_username": "cliente123",
      "direction": "inbound",
      "type": "text",
      "content": "Olá, gostaria de mais informações",
      "sent_at": "2025-01-28T20:15:00Z",
      "status": "delivered",
      "lead_id": 45
    }
  ],
  "count": 1
}
```

### Buscar Posts

**Endpoint**: `GET /api/social/instagram/accounts/{account}/posts?limit=25`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "account_id": 1,
  "posts": [
    {
      "id": "17841400123456789",
      "caption": "Novo produto disponível!",
      "media_type": "IMAGE",
      "media_url": "https://...",
      "permalink": "https://www.instagram.com/p/...",
      "timestamp": "2025-01-28T18:00:00Z"
    }
  ],
  "count": 1
}
```

### Atualizar Token

**Endpoint**: `POST /api/social/instagram/accounts/{account}/refresh-token`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "message": "Token refreshed successfully",
  "token_expires_at": "2025-03-28T21:33:19Z"
}
```

### Desconectar Conta

**Endpoint**: `DELETE /api/social/instagram/accounts/{account}/disconnect`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "message": "Instagram account disconnected successfully"
}
```

## Webhooks

### Verificação do Webhook (Meta Challenge)

**Endpoint**: `GET /api/webhooks/instagram/verify`

**Query Params**:
- `hub.mode=subscribe`
- `hub.verify_token={seu_token}`
- `hub.challenge={challenge_string}`

### Receber Mensagens

**Endpoint**: `POST /api/webhooks/instagram/handle`

**Headers**:
- `X-Hub-Signature-256`: Signature para validação

**Payload** (exemplo):
```json
{
  "object": "instagram",
  "entry": [
    {
      "id": "123456789",
      "time": 1706475600,
      "messaging": [
        {
          "sender": {
            "id": "987654321",
            "username": "cliente123"
          },
          "recipient": {
            "id": "123456789"
          },
          "timestamp": 1706475600000,
          "message": {
            "mid": "m_abc123",
            "text": "Olá!"
          }
        }
      ]
    }
  ]
}
```

## Jobs Assíncronos

### SyncInstagramMessagesJob

Sincroniza mensagens periodicamente (a cada 5 minutos).

```php
use App\Jobs\Social\SyncInstagramMessagesJob;

// Disparar manualmente
SyncInstagramMessagesJob::dispatch($accountId);
```

### ProcessIncomingInstagramMessageJob

Processa mensagens recebidas via webhook.

```php
use App\Jobs\Social\ProcessIncomingInstagramMessageJob;

// Executado automaticamente pelo webhook
ProcessIncomingInstagramMessageJob::dispatch($messageData);
```

## Banco de Dados

### Tabela: instagram_accounts

```sql
- id: bigint (PK)
- company_id: bigint (FK)
- instagram_user_id: string (unique)
- username: string
- access_token: text (encrypted)
- token_expires_at: datetime
- account_type: enum (BUSINESS, CREATOR, PERSONAL)
- profile_picture_url: string
- followers_count: int
- is_active: boolean
- metadata: json
- timestamps
- soft_deletes
```

### Tabela: instagram_messages

```sql
- id: bigint (PK)
- instagram_account_id: bigint (FK)
- lead_id: bigint (FK nullable)
- message_id: string (unique)
- conversation_id: string
- sender_id: string
- sender_username: string
- direction: enum (inbound, outbound)
- type: enum (text, image, video, story_mention, story_reply, reel_share)
- content: text
- media_url: string
- status: enum (sent, delivered, read, failed)
- sent_at: datetime
- read_at: datetime
- metadata: json
- timestamps
- soft_deletes
```

## Vinculação Automática com Leads

O sistema tenta vincular automaticamente mensagens recebidas a leads existentes:

1. **Por Instagram Handle**: Verifica se existe lead com `instagram_handle` correspondente
2. **Por Telefone**: Busca no campo `phone` (útil se o username for um número)

```php
// Exemplo de lead linkado
$message = InstagramMessage::with('lead')->find(1);

if ($message->lead) {
    echo "Mensagem vinculada ao lead: {$message->lead->name}";
}
```

## Limitações e Considerações

### Limitações da API do Instagram

- **Instagram Basic Display**: Acesso apenas a posts e perfil público
- **Instagram Messaging API**: Requer conta Business ou Creator conectada a uma Página do Facebook
- **Rate Limits**: Meta impõe limites de chamadas por hora (varia por tipo de conta)
- **Token Expiration**: Tokens expiram em 60 dias (refresh automático implementado)

### Permissões Necessárias

Para usar a Messaging API:
- Conta Instagram deve ser Business ou Creator
- Deve estar vinculada a uma Página do Facebook
- A Página deve ter acesso ao Instagram Messaging

### Segurança

- Tokens são **criptografados** no banco de dados
- Webhooks validam **assinatura X-Hub-Signature-256**
- Apenas usuários autenticados podem acessar contas da própria empresa

## Monitoramento e Logs

Todos os eventos são registrados em logs:

```php
// Logs importantes
Log::info('Instagram account connected', ['account_id' => $account->id]);
Log::info('Instagram message received', ['message_id' => $message->id]);
Log::error('Failed to fetch Instagram posts', ['error' => $exception->getMessage()]);
```

Consulte `storage/logs/laravel.log` para debugging.

## Testes

### Testar Webhook Localmente

Use [ngrok](https://ngrok.com/) para expor sua aplicação local:

```bash
ngrok http 8000
```

Configure o webhook URL no Meta Developer:
```
https://seu-id.ngrok.io/api/webhooks/instagram/handle
```

### Testar Conexão

```bash
# Obter URL de autorização
curl -X GET http://localhost:8000/api/social/instagram/auth-url \
  -H "Authorization: Bearer {token}"

# Verificar contas conectadas
curl -X GET http://localhost:8000/api/social/instagram/accounts \
  -H "Authorization: Bearer {token}"
```

## Próximas Melhorias

- [ ] Enviar mensagens diretamente pela plataforma
- [ ] Responder comentários de posts
- [ ] Monitorar menções e hashtags
- [ ] Analytics de engajamento
- [ ] Agendamento de posts
- [ ] Stories e Reels integration

## Suporte

Para problemas ou dúvidas sobre a integração:
- Consulte a [documentação oficial do Meta](https://developers.facebook.com/docs/instagram)
- Verifique os logs da aplicação
- Entre em contato com o suporte técnico

---

**Última atualização**: Janeiro 2025
