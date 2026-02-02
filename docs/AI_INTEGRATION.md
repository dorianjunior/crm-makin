# IA com Gemini - Documentação da API

## Visão Geral

Sistema de integração com Google Gemini AI para automatizar conversas e interações com leads através de diferentes canais (WhatsApp, Instagram, Email, Chat).

**Funcionalidades principais:**
- Configuração de múltiplos providers de IA (Gemini, OpenAI, Claude)
- Templates de prompts reutilizáveis com variáveis
- Conversas contextuais com histórico
- Rastreamento de tokens e custos
- Métricas de satisfação e conversão
- Processamento assíncrono com filas

## Configuração Inicial

### 1. Variáveis de Ambiente

Adicione ao seu `.env`:

```env
GEMINI_API_KEY=your_gemini_api_key_here
```

### 2. Criar AI Setting

Antes de usar a IA, configure um AI Setting para sua empresa:

```http
POST /api/ai/settings
Authorization: Bearer {token}
Content-Type: application/json

{
  "provider": "gemini",
  "model": "gemini-pro",
  "api_key": "your_api_key",
  "temperature": 0.7,
  "max_tokens": 2048,
  "top_p": 0.95,
  "top_k": 40,
  "is_active": true,
  "is_default": true
}
```

**Resposta:**
```json
{
  "message": "AI setting created successfully",
  "data": {
    "id": 1,
    "company_id": 1,
    "provider": "gemini",
    "model": "gemini-pro",
    "temperature": 0.7,
    "max_tokens": 2048,
    "is_active": true,
    "is_default": true,
    "created_at": "2026-01-28T10:00:00.000000Z"
  }
}
```

## AI Settings

### Listar Settings

```http
GET /api/ai/settings
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "provider": "gemini",
      "model": "gemini-pro",
      "temperature": 0.7,
      "is_active": true,
      "is_default": true
    }
  ]
}
```

### Obter Setting Específico

```http
GET /api/ai/settings/1
Authorization: Bearer {token}
```

### Atualizar Setting

```http
PUT /api/ai/settings/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "temperature": 0.8,
  "max_tokens": 4096
}
```

### Definir como Padrão

```http
POST /api/ai/settings/1/set-default
Authorization: Bearer {token}
```

### Testar Conexão

```http
POST /api/ai/settings/1/test
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "message": "Connection test successful",
  "response": "OK",
  "tokens": {
    "input": 15,
    "output": 2,
    "total": 17
  }
}
```

### Deletar Setting

```http
DELETE /api/ai/settings/1
Authorization: Bearer {token}
```

## Prompt Templates

### Criar Template

```http
POST /api/ai/templates
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Qualificação de Lead - WhatsApp",
  "category": "lead_qualification",
  "description": "Template para qualificar leads via WhatsApp",
  "system_prompt": "Você é um assistente de vendas da empresa {{company_name}}. Sua função é qualificar leads de forma amigável e profissional, identificando necessidades e interesse em nossos produtos.",
  "user_prompt_template": "Lead: {{lead_name}}\nCanal: WhatsApp\nMensagem recebida: {{user_message}}\n\nResponda de forma natural e profissional, fazendo perguntas qualificadoras quando apropriado.",
  "variables": ["company_name", "lead_name", "user_message"],
  "tags": ["whatsapp", "qualification", "sales"],
  "is_active": true
}
```

**Resposta:**
```json
{
  "message": "Prompt template created successfully",
  "data": {
    "id": 1,
    "company_id": 1,
    "name": "Qualificação de Lead - WhatsApp",
    "slug": "qualificacao-de-lead-whatsapp",
    "category": "lead_qualification",
    "is_active": true,
    "usage_count": 0,
    "created_at": "2026-01-28T10:00:00.000000Z"
  }
}
```

### Listar Templates

```http
GET /api/ai/templates
Authorization: Bearer {token}

# Com filtros
GET /api/ai/templates?category=lead_qualification
GET /api/ai/templates?tag=whatsapp
GET /api/ai/templates?is_active=true
```

### Obter Template Específico

```http
GET /api/ai/templates/1
Authorization: Bearer {token}
```

### Atualizar Template

```http
PUT /api/ai/templates/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "temperature": 0.8,
  "is_active": false
}
```

### Preview Template

Visualize como o template ficará com variáveis reais:

```http
POST /api/ai/templates/1/preview
Authorization: Bearer {token}
Content-Type: application/json

{
  "variables": {
    "company_name": "ACME Corp",
    "lead_name": "João Silva",
    "user_message": "Olá, gostaria de saber mais sobre seus produtos"
  }
}
```

**Resposta:**
```json
{
  "data": {
    "system_prompt": "Você é um assistente de vendas da empresa ACME Corp. Sua função é qualificar leads de forma amigável e profissional...",
    "user_prompt": "Lead: João Silva\nCanal: WhatsApp\nMensagem recebida: Olá, gostaria de saber mais sobre seus produtos..."
  }
}
```

### Estatísticas do Template

```http
GET /api/ai/templates/1/statistics
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "data": {
    "usage_count": 45,
    "avg_satisfaction_rating": 4.2,
    "total_conversations": 45,
    "active_conversations": 3,
    "completed_conversations": 42
  }
}
```

### Deletar Template

```http
DELETE /api/ai/templates/1
Authorization: Bearer {token}
```

## Conversas

### Criar Conversa

```http
POST /api/ai/conversations
Authorization: Bearer {token}
Content-Type: application/json

{
  "ai_prompt_template_id": 1,
  "lead_id": 5,
  "channel": "whatsapp",
  "conversationable_type": "App\\Models\\WhatsappConversation",
  "conversationable_id": 10,
  "context_data": {
    "product_interest": "Software CRM",
    "source": "website"
  }
}
```

**Resposta:**
```json
{
  "message": "Conversation created successfully",
  "data": {
    "id": 1,
    "company_id": 1,
    "conversation_id": "550e8400-e29b-41d4-a716-446655440000",
    "channel": "whatsapp",
    "status": "active",
    "provider": "gemini",
    "model": "gemini-pro",
    "message_count": 0,
    "total_cost": 0,
    "created_at": "2026-01-28T10:00:00.000000Z"
  }
}
```

### Listar Conversas

```http
GET /api/ai/conversations
Authorization: Bearer {token}

# Com filtros
GET /api/ai/conversations?status=active
GET /api/ai/conversations?channel=whatsapp
GET /api/ai/conversations?lead_id=5
GET /api/ai/conversations?per_page=20
```

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "status": "active",
      "channel": "whatsapp",
      "message_count": 5,
      "total_cost": 0.002345,
      "lead": {
        "id": 5,
        "name": "João Silva"
      },
      "created_at": "2026-01-28T10:00:00.000000Z"
    }
  ],
  "links": {},
  "meta": {}
}
```

### Obter Conversa com Mensagens

```http
GET /api/ai/conversations/1
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "status": "active",
    "channel": "whatsapp",
    "message_count": 5,
    "total_input_tokens": 1234,
    "total_output_tokens": 567,
    "total_cost": 0.002345,
    "messages": [
      {
        "id": 1,
        "role": "user",
        "content": "Olá, gostaria de saber mais sobre seus produtos",
        "created_at": "2026-01-28T10:00:00.000000Z"
      },
      {
        "id": 2,
        "role": "assistant",
        "content": "Olá! Fico feliz em ajudar. Temos diversos produtos...",
        "input_tokens": 245,
        "output_tokens": 120,
        "cost": 0.000456,
        "processing_time_ms": 1234,
        "created_at": "2026-01-28T10:00:05.000000Z"
      }
    ],
    "lead": {
      "id": 5,
      "name": "João Silva"
    }
  }
}
```

### Enviar Mensagem

```http
POST /api/ai/conversations/1/send-message
Authorization: Bearer {token}
Content-Type: application/json

{
  "message": "Qual é o preço do plano básico?",
  "variables": {
    "company_name": "ACME Corp",
    "lead_name": "João Silva"
  }
}
```

**Resposta:**
```json
{
  "message": "Message sent successfully",
  "data": {
    "user_message": {
      "id": 3,
      "role": "user",
      "content": "Qual é o preço do plano básico?",
      "created_at": "2026-01-28T10:05:00.000000Z"
    },
    "assistant_message": {
      "id": 4,
      "role": "assistant",
      "content": "Nosso plano básico custa R$ 99/mês e inclui...",
      "input_tokens": 250,
      "output_tokens": 130,
      "total_tokens": 380,
      "cost": 0.000475,
      "processing_time_ms": 1456,
      "created_at": "2026-01-28T10:05:02.000000Z"
    },
    "conversation": {
      "id": 1,
      "message_count": 6,
      "total_cost": 0.002820
    }
  }
}
```

### Completar Conversa

```http
POST /api/ai/conversations/1/complete
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "message": "Conversation completed successfully",
  "data": {
    "id": 1,
    "status": "completed",
    "duration_seconds": 3600,
    "message_count": 15,
    "total_cost": 0.008945
  }
}
```

### Estatísticas Gerais

```http
GET /api/ai/conversations/statistics
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "data": {
    "total_conversations": 120,
    "active_conversations": 8,
    "completed_conversations": 110,
    "total_messages": 1840,
    "total_tokens": 456789,
    "total_cost": 1.234567,
    "avg_satisfaction": 4.3,
    "lead_conversion_rate": 35.5
  }
}
```

## Processamento Assíncrono

### Processar Conversa em Background

Para processar conversas de forma assíncrona (recomendado para produção):

```php
use App\Jobs\ProcessAIConversationJob;

ProcessAIConversationJob::dispatch(
    conversationId: 1,
    userMessage: 'Olá, preciso de ajuda',
    variables: ['lead_name' => 'João Silva']
);
```

### Atualizar Estatísticas

Para recalcular estatísticas de uso e satisfação:

```php
use App\Jobs\UpdateAIStatisticsJob;

// Atualizar estatísticas de uma empresa
UpdateAIStatisticsJob::dispatch(companyId: 1);

// Atualizar estatísticas de um template específico
UpdateAIStatisticsJob::dispatch(companyId: 1, templateId: 5);
```

## Parâmetros de Configuração

### Temperature (0.0 - 2.0)
- **0.0-0.3**: Respostas mais determinísticas e focadas
- **0.4-0.7**: Balanceado (recomendado para atendimento)
- **0.8-1.0**: Mais criativo e variado
- **1.0+**: Muito criativo (use com cuidado)

### Max Tokens
- **1024**: Respostas curtas e diretas
- **2048**: Respostas médias (recomendado)
- **4096**: Respostas longas e detalhadas
- **8192+**: Contextos muito longos

### Top P (0.0 - 1.0)
- Controla a diversidade das respostas
- **0.9-0.95**: Recomendado para balancear qualidade e diversidade

### Top K (1 - 100)
- Número de tokens considerados
- **40**: Valor padrão recomendado

## Exemplos de Uso

### 1. Qualificação Automática de Lead via WhatsApp

```javascript
// 1. Criar template de qualificação
const template = await createTemplate({
  name: "Qualificação WhatsApp",
  category: "lead_qualification",
  system_prompt: "Você é um assistente que qualifica leads...",
  user_prompt_template: "Lead: {{lead_name}}\nMensagem: {{message}}"
});

// 2. Quando lead enviar mensagem
const conversation = await createConversation({
  ai_prompt_template_id: template.id,
  lead_id: lead.id,
  channel: "whatsapp"
});

// 3. Enviar mensagem do lead
const response = await sendMessage(conversation.id, {
  message: "Olá, quero saber sobre o produto",
  variables: {
    lead_name: lead.name
  }
});

// 4. IA responde automaticamente
console.log(response.assistant_message.content);
// "Olá! Fico feliz em ajudar. Posso saber qual produto específico você tem interesse?"
```

### 2. Suporte Técnico Automatizado

```javascript
// Template de suporte
const supportTemplate = await createTemplate({
  name: "Suporte Técnico",
  category: "support",
  system_prompt: `Você é um especialista em suporte técnico.
    Analise o problema do cliente e forneça soluções claras.
    Se não souber a resposta, encaminhe para um humano.`,
  user_prompt_template: "Cliente: {{customer_name}}\nProblema: {{issue}}"
});

// Criar conversa de suporte
const supportChat = await createConversation({
  ai_prompt_template_id: supportTemplate.id,
  lead_id: customer.id,
  channel: "chat"
});
```

### 3. Follow-up Automático de Propostas

```javascript
// Template de follow-up
const followupTemplate = await createTemplate({
  name: "Follow-up Proposta",
  category: "follow_up",
  system_prompt: `Você acompanha propostas enviadas.
    Seja educado e busque feedback sobre a proposta.`,
  user_prompt_template: `Proposta: {{proposal_number}}
    Valor: {{proposal_value}}
    Enviada há: {{days_ago}} dias`
});
```

## Códigos de Erro

| Código | Mensagem | Descrição |
|--------|----------|-----------|
| 400 | Missing required variables | Variáveis obrigatórias não fornecidas |
| 400 | No active AI setting found | Nenhuma configuração de IA ativa |
| 400 | Cannot delete template in use | Template não pode ser deletado |
| 422 | Validation error | Dados de entrada inválidos |
| 500 | Failed to generate AI response | Erro ao gerar resposta da IA |

## Boas Práticas

### 1. Segurança
- ✅ Nunca exponha API keys no front-end
- ✅ Use variáveis de ambiente para chaves
- ✅ Implemente rate limiting
- ✅ Valide todas as entradas do usuário

### 2. Performance
- ✅ Use processamento assíncrono para conversas
- ✅ Limite histórico de mensagens (recomendado: 20)
- ✅ Configure timeouts apropriados
- ✅ Monitore custos de tokens

### 3. Qualidade
- ✅ Teste templates antes de ativar
- ✅ Monitore satisfação dos usuários
- ✅ Ajuste temperature para seu caso de uso
- ✅ Revise conversas periodicamente

### 4. Custos
- ✅ Monitore total_cost nas conversas
- ✅ Configure max_tokens apropriadamente
- ✅ Use filas para evitar chamadas desnecessárias
- ✅ Implemente cache quando possível

## Webhooks

Para integrar com WhatsApp/Instagram e responder automaticamente:

```php
// No WebhookController
public function handleIncomingMessage($message)
{
    // Buscar ou criar conversa
    $conversation = AIConversation::firstOrCreate([
        'conversationable_type' => WhatsappConversation::class,
        'conversationable_id' => $message->conversation_id,
    ]);

    // Processar mensagem assincronamente
    ProcessAIConversationJob::dispatch(
        $conversation->id,
        $message->content,
        ['lead_name' => $message->sender_name]
    );
}
```

## Suporte

Para mais informações:
- Documentação do Gemini: https://ai.google.dev/docs
- Issues: https://github.com/seu-repo/crm/issues
