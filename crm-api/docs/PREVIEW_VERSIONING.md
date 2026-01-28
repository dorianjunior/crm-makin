# Sistema de Preview e Versionamento Avançado - CMS

## 📋 Visão Geral

Sistema completo para visualização de conteúdo não publicado através de tokens temporários e gerenciamento avançado de versões com comparação e rollback.

## 🔐 Sistema de Preview

### Características
- **Tokens temporários** com validade de 24 horas
- **URLs públicas** sem necessidade de autenticação
- **Acesso seguro** via Cache do Laravel
- **Suporte a todos os tipos** de conteúdo CMS

### Fluxo de Preview
1. Usuário autenticado solicita token de preview
2. Token é gerado e armazenado em cache (24h)
3. URL pública é criada com o token
4. Qualquer pessoa com a URL pode visualizar o conteúdo
5. Token pode ser revogado manualmente

---

## 🎯 API Endpoints - Preview

### Gerar Token de Preview
```http
POST /api/cms/preview/{type}/{id}/token
Authorization: Bearer {token}
```

**Parâmetros**:
- `type` - Tipo de conteúdo (pages, posts, portfolios, faqs, testimonials, team-members, forms, banners, menus)
- `id` - ID do conteúdo

**Response**:
```json
{
  "token": "a1b2c3d4e5f6...",
  "expires_at": "2026-01-29T10:30:00.000000Z",
  "preview_url": "https://api.crm.com/api/cms/preview/portfolios/5/a1b2c3d4e5f6..."
}
```

---

### Visualizar Conteúdo via Preview (Público)
```http
GET /api/cms/preview/{type}/{id}/{token}
```

**Sem autenticação necessária!**

**Response**: Retorna o recurso completo do conteúdo

```json
{
  "id": 5,
  "title": "Projeto CRM Avançado",
  "status": "draft",
  "site_id": 1,
  "description": "...",
  "images": [...],
  "technologies": [...],
  "creator": {
    "id": 2,
    "name": "João Silva"
  },
  "versions": [...],
  "approvals": [...]
}
```

**Validações**:
- Token deve existir no cache
- Token não pode estar expirado (24h)
- Token deve corresponder ao tipo e ID solicitados
- Conteúdo pode estar deletado (withTrashed) - preview funciona mesmo em soft-deleted

---

### Revogar Token de Preview
```http
DELETE /api/cms/preview/tokens/{token}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "message": "Token de preview revogado com sucesso."
}
```

---

## 📚 Sistema de Versionamento

### Características
- **Histórico completo** de todas as alterações
- **Comparação** entre duas versões específicas
- **Rollback** para qualquer versão anterior
- **Criação manual** de versões (snapshots)
- **Auto-versionamento** ao criar/editar conteúdo

### Estrutura de Versão
```php
{
  "id": 10,
  "versionable_type": "App\\Models\\CMS\\Portfolio",
  "versionable_id": 5,
  "created_by": 2,
  "version_number": 3,
  "content_data": {
    // Snapshot completo do conteúdo
  },
  "change_summary": "Atualizadas imagens e tecnologias",
  "created_at": "2026-01-28T10:30:00Z"
}
```

---

## 🎯 API Endpoints - Versionamento

### Listar Histórico de Versões
```http
GET /api/cms/versions/{type}/{id}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": [
    {
      "id": 15,
      "version_number": 5,
      "change_summary": "Rolled back to version 3",
      "created_by": 1,
      "created_at": "2026-01-28T15:00:00Z",
      "creator": {
        "id": 1,
        "name": "Admin"
      }
    },
    {
      "id": 12,
      "version_number": 4,
      "change_summary": "Updated technologies",
      "created_by": 2,
      "created_at": "2026-01-28T12:00:00Z"
    },
    {
      "id": 10,
      "version_number": 3,
      "change_summary": "Added client information",
      "created_by": 2,
      "created_at": "2026-01-28T10:00:00Z"
    }
  ]
}
```

---

### Ver Versão Específica
```http
GET /api/cms/versions/{type}/{id}/{versionNumber}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "id": 10,
  "version_number": 3,
  "content_data": {
    "id": 5,
    "title": "Projeto CRM",
    "description": "Descrição da versão 3",
    "images": [...],
    "technologies": ["Laravel", "Vue.js"],
    "client_name": "Empresa XYZ",
    "created_at": "2026-01-27T10:00:00Z"
  },
  "change_summary": "Added client information",
  "created_by": 2,
  "creator": {
    "id": 2,
    "name": "João Silva"
  },
  "created_at": "2026-01-28T10:00:00Z"
}
```

---

### Criar Versão Manual (Snapshot)
```http
POST /api/cms/versions/{type}/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "change_summary": "Checkpoint before major changes"
}
```

**Validação**:
- `change_summary` - obrigatório, máx 500 caracteres

**Response**:
```json
{
  "message": "Versão criada com sucesso.",
  "version": {
    "id": 20,
    "version_number": 6,
    "change_summary": "Checkpoint before major changes",
    "created_at": "2026-01-28T16:00:00Z"
  }
}
```

---

### Rollback para Versão Anterior
```http
POST /api/cms/versions/{type}/{id}/rollback/{versionNumber}
Authorization: Bearer {token}
```

**Ações**:
1. Busca a versão especificada
2. Restaura o conteúdo com os dados da versão
3. Remove campos que não devem ser restaurados (id, timestamps, deleted_at)
4. Atualiza o conteúdo atual
5. Cria nova versão registrando o rollback

**Response**:
```json
{
  "message": "Conteúdo revertido para versão 3 com sucesso.",
  "current_version": 7
}
```

⚠️ **Nota**: O rollback cria uma NOVA versão. O histórico é preservado.

---

### Comparar Duas Versões
```http
POST /api/cms/versions/{type}/{id}/compare
Authorization: Bearer {token}
Content-Type: application/json

{
  "version1": 3,
  "version2": 5
}
```

**Validação**:
- `version1` - obrigatório, inteiro, mín 1
- `version2` - obrigatório, inteiro, mín 1

**Response**:
```json
{
  "content_type": "portfolios",
  "content_id": 5,
  "version1": 3,
  "version2": 5,
  "differences": {
    "description": {
      "version_3": "Descrição antiga",
      "version_5": "Descrição nova e melhorada"
    },
    "technologies": {
      "version_3": ["Laravel", "Vue.js"],
      "version_5": ["Laravel", "Vue.js", "Redis", "Docker"]
    },
    "completion_date": {
      "version_3": "2025-12-15",
      "version_5": "2026-01-15"
    }
  },
  "fields_changed": 3
}
```

**Algoritmo de Comparação**:
- Itera sobre todos os campos da versão 1
- Compara valor com versão 2
- Lista apenas campos diferentes
- Mostra valor de cada versão lado a lado

---

## 💡 Casos de Uso

### 1. Compartilhar Rascunho para Revisão Externa
```bash
# Editor cria conteúdo draft
POST /api/cms/portfolios
{
  "title": "Novo Projeto",
  "status": "draft"
}

# Editor gera token de preview
POST /api/cms/preview/portfolios/10/token

# Editor compartilha URL com cliente
# Cliente acessa sem login:
GET /api/cms/preview/portfolios/10/a1b2c3d4...

# Após aprovação, token pode ser revogado
DELETE /api/cms/preview/tokens/a1b2c3d4...
```

---

### 2. Criar Checkpoint Antes de Mudanças Grandes
```bash
# Criar snapshot manual
POST /api/cms/versions/portfolios/10
{
  "change_summary": "Backup before redesign"
}

# Fazer alterações no conteúdo
PUT /api/cms/portfolios/10
{
  "description": "Nova descrição...",
  "images": [...]
}

# Se der errado, fazer rollback
POST /api/cms/versions/portfolios/10/rollback/5
```

---

### 3. Revisar Histórico de Alterações
```bash
# Listar todas as versões
GET /api/cms/versions/portfolios/10

# Ver versão específica
GET /api/cms/versions/portfolios/10/3

# Comparar versão atual (7) com versão antiga (3)
POST /api/cms/versions/portfolios/10/compare
{
  "version1": 3,
  "version2": 7
}
```

---

## 🛠️ Implementação Técnica

### PreviewController

**Tipos Suportados**:
- pages
- posts
- portfolios
- faqs
- testimonials
- team-members
- forms
- banners
- menus

**Segurança**:
- Tokens armazenados em Cache (Redis/Memcached)
- Validade de 24 horas automática
- Validação de tipo e ID
- Cache key: `preview_token:{token}`

**Dados do Token**:
```php
[
    'type' => 'portfolios',
    'id' => 10,
    'user_id' => 2  // Quem gerou
]
```

---

### VersionController

**Tipos Suportados**: mesmos do Preview

**Métodos do VersioningService Utilizados**:
- `getHistory($content)` - Histórico completo
- `getVersion($content, $versionNumber)` - Versão específica
- `createVersion($content, $userId, $summary)` - Criar versão
- `rollback($content, $versionNumber, $userId)` - Reverter
- `compareVersions($content, $v1, $v2)` - Comparar

---

## 📊 Estrutura do Banco

### Tabela: content_versions

```sql
CREATE TABLE content_versions (
    id BIGINT PRIMARY KEY,
    versionable_type VARCHAR(255),
    versionable_id BIGINT,
    created_by BIGINT,
    version_number INT,
    content_data JSON,           -- Snapshot completo
    change_summary TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX(versionable_type, versionable_id, version_number)
);
```

### content_data (JSON)
Armazena snapshot completo do conteúdo no momento da versão:
```json
{
  "id": 5,
  "site_id": 1,
  "title": "Projeto",
  "slug": "projeto",
  "description": "...",
  "status": "draft",
  "created_by": 2,
  "created_at": "...",
  "updated_at": "..."
}
```

---

## 🔄 Auto-Versionamento

### Quando Versões São Criadas Automaticamente?

1. **Na criação** de conteúdo (versão 1)
2. **Ao aprovar** uma solicitação
3. **Ao fazer rollback** (cria nova versão)

### Versionamento Manual

Use `POST /api/cms/versions/{type}/{id}` para criar checkpoints estratégicos:
- Antes de mudanças grandes
- Ao finalizar uma fase de edição
- Antes de solicitar aprovação

---

## 🎨 Interface Sugerida (Frontend)

### Histórico de Versões
```vue
<template>
  <div class="version-history">
    <h3>Histórico de Versões</h3>
    <div v-for="version in versions" :key="version.id" class="version-item">
      <div class="version-header">
        <span class="version-number">v{{ version.version_number }}</span>
        <span class="version-author">{{ version.creator.name }}</span>
        <span class="version-date">{{ version.created_at }}</span>
      </div>
      <p class="version-summary">{{ version.change_summary }}</p>
      <div class="version-actions">
        <button @click="viewVersion(version.version_number)">Ver</button>
        <button @click="compareWith(version.version_number)">Comparar</button>
        <button @click="rollbackTo(version.version_number)">Reverter</button>
      </div>
    </div>
  </div>
</template>
```

### Preview Público
```vue
<template>
  <div class="preview-generator">
    <button @click="generatePreview">Gerar Link de Preview</button>
    
    <div v-if="previewUrl" class="preview-result">
      <p>Link válido por 24 horas:</p>
      <input :value="previewUrl" readonly />
      <button @click="copyToClipboard(previewUrl)">Copiar</button>
      <button @click="revokePreview">Revogar</button>
    </div>
  </div>
</template>
```

---

## ⚡ Performance

### Cache Strategy
- Preview tokens em Cache (não banco)
- Expira automaticamente em 24h
- Rápido acesso via Cache::get()

### Versões
- Paginação no histórico (se necessário)
- Índices otimizados para queries
- JSON comprimido para snapshots grandes

---

## 🔒 Segurança

### Preview
- ✅ Tokens aleatórios de 64 caracteres
- ✅ Validade limitada (24h)
- ✅ Validação de tipo e ID
- ✅ Possibilidade de revogação
- ❌ Não requer autenticação (por design)

### Versionamento
- ✅ Requer autenticação
- ✅ Registra quem criou cada versão
- ✅ Preserva histórico completo
- ✅ Rollback seguro (não sobrescreve)

---

## 📝 Exemplo Completo

```bash
# 1. Criar conteúdo
POST /api/cms/portfolios
{
  "title": "Projeto Alpha",
  "status": "draft"
}
# Response: { "id": 15 }

# 2. Gerar preview para cliente
POST /api/cms/preview/portfolios/15/token
# Response: { "preview_url": "..." }

# 3. Cliente visualiza (sem login)
GET /api/cms/preview/portfolios/15/abc123...
# Response: Conteúdo completo

# 4. Fazer alterações
PUT /api/cms/portfolios/15
{
  "description": "Nova descrição"
}

# 5. Criar checkpoint
POST /api/cms/versions/portfolios/15
{
  "change_summary": "After client feedback"
}

# 6. Mais alterações
PUT /api/cms/portfolios/15
{
  "technologies": ["Laravel", "Vue", "Redis"]
}

# 7. Comparar versões
POST /api/cms/versions/portfolios/15/compare
{
  "version1": 2,
  "version2": 3
}

# 8. Se necessário, reverter
POST /api/cms/versions/portfolios/15/rollback/2

# 9. Ver histórico completo
GET /api/cms/versions/portfolios/15
```

---

**Status**: ✅ Sistema de Preview e Versionamento Completo
**Arquivos Criados**: 2 controllers (PreviewController, VersionController)
**Rotas Adicionadas**: 8 endpoints (3 preview + 5 versioning)
