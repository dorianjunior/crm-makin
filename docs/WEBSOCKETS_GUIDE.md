# Guia de WebSockets para o CRM

## 📊 Comparação: Inertia vs API REST vs WebSockets

### **Quando usar cada abordagem:**

| Cenário | Solução Recomendada | Motivo |
|---------|-------------------|--------|
| Sistema interno (CRM/Admin) | **Inertia.js** ⭐ | Mais simples, rápido de desenvolver |
| App mobile + web | **API REST** | Reutilização de código |
| Chat em tempo real | **WebSockets** | Comunicação bidirecional |
| Notificações push | **WebSockets** | Servidor envia sem cliente pedir |
| Monitoramento ao vivo | **WebSockets** | Atualizações instantâneas |

---

## 🎯 Recomendação para seu CRM

**Use Inertia.js + Polling** (solução atual é boa!)

**Por quê?**
- ✅ Mais simples de manter
- ✅ Menos infraestrutura (não precisa servidor WebSocket)
- ✅ Atualização a cada 30s é suficiente para leads
- ✅ Menor complexidade

**Use WebSockets apenas se:**
- Precisar de chat em tempo real
- Notificações instantâneas críticas
- Múltiplos usuários editando mesmo registro

---

## 🔌 Como Implementar WebSockets (se necessário)

### **Opção 1: Pusher (Mais Fácil - SaaS)** ⭐

**Vantagens:**
- Configuração simples
- Infraestrutura gerenciada
- Free tier generoso
- Escalável

**Instalação:**

```bash
# 1. Instalar dependências
composer require pusher/pusher-php-server
npm install --save laravel-echo pusher-js

# 2. Configurar .env
echo "BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1" >> .env

# 3. Descomentar provider
# Em config/app.php
App\Providers\BroadcastServiceProvider::class,
```

### **Opção 2: Soketi (Auto-hospedado - Grátis)** 🆓

**Vantagens:**
- Totalmente grátis
- Compatível com Pusher
- Self-hosted
- Open source

**Instalação com Docker:**

```yaml
# docker-compose.yml
services:
  soketi:
    image: quay.io/soketi/soketi:latest-16-alpine
    ports:
      - "6001:6001"
    environment:
      SOKETI_DEBUG: '1'
      SOKETI_DEFAULT_APP_ID: 'app-id'
      SOKETI_DEFAULT_APP_KEY: 'app-key'
      SOKETI_DEFAULT_APP_SECRET: 'app-secret'
```

```bash
# Subir servidor
docker-compose up -d soketi

# Configurar .env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=app-id
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

---

## 📝 Implementação Completa

### **1. Criar Event para Novo Lead**

```php
<?php
// app/Events/LeadCreated.php

namespace App\Events;

use App\Models\CRM\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Lead $lead
    ) {}

    public function broadcastOn(): Channel
    {
        // Canal privado por empresa
        return new Channel('company.' . $this->lead->company_id);
    }

    public function broadcastAs(): string
    {
        return 'lead.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->lead->id,
            'name' => $this->lead->name,
            'email' => $this->lead->email,
            'status' => $this->lead->status->value,
            'created_at' => $this->lead->created_at->toISOString(),
        ];
    }
}
```

### **2. Disparar Event no Controller**

```php
<?php
// app/Http/Controllers/API/CRM/LeadController.php

use App\Events\LeadCreated;

public function store(StoreLeadRequest $request): LeadResource
{
    $lead = $this->leadService->create($request->validated());
    
    // 🔥 Dispara evento WebSocket
    broadcast(new LeadCreated($lead))->toOthers();
    
    return new LeadResource($lead->load(['source', 'assignedUser']));
}
```

### **3. Configurar Laravel Echo no Frontend**

```javascript
// resources/js/bootstrap.js

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

```javascript
// .env frontend
VITE_PUSHER_APP_KEY=app-key
VITE_PUSHER_HOST=127.0.0.1
VITE_PUSHER_PORT=6001
VITE_PUSHER_SCHEME=http
```

### **4. Escutar Eventos no Vue**

```vue
<!-- resources/js/Pages/Leads/Index.vue -->
<script setup>
import { onMounted, onUnmounted } from 'vue';

const companyId = computed(() => auth.user.company_id);

onMounted(() => {
    // 🎧 Escutar novos leads
    window.Echo.channel(`company.${companyId.value}`)
        .listen('lead.created', (event) => {
            console.log('🎉 Novo lead:', event);
            
            // Adicionar na lista
            leads.value.data.unshift(event);
            
            // Atualizar estatísticas
            stats.value.total++;
            
            // Mostrar notificação
            alert.success(`Novo lead: ${event.name}`);
        });
});

onUnmounted(() => {
    // Limpar listeners
    window.Echo.leave(`company.${companyId.value}`);
});
</script>
```

---

## 🎨 Composable com WebSocket

```javascript
// resources/js/composables/useLeadsRealtime.js

import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useLeads } from './useLeads';

export function useLeadsRealtime() {
    const {
        leads,
        stats,
        loadLeads,
        ...rest
    } = useLeads({ autoRefresh: false }); // Desativa polling
    
    const companyId = computed(() => window.auth?.user?.company_id);
    
    // Configurar WebSocket
    const setupWebSocket = () => {
        if (!window.Echo || !companyId.value) return;
        
        const channel = window.Echo.channel(`company.${companyId.value}`);
        
        // Novo lead
        channel.listen('lead.created', (event) => {
            leads.value.data.unshift(event);
            stats.value.total++;
            stats.value.new_this_month++;
        });
        
        // Lead atualizado
        channel.listen('lead.updated', (event) => {
            const index = leads.value.data.findIndex(l => l.id === event.id);
            if (index !== -1) {
                leads.value.data[index] = event;
            }
        });
        
        // Lead deletado
        channel.listen('lead.deleted', (event) => {
            const index = leads.value.data.findIndex(l => l.id === event.id);
            if (index !== -1) {
                leads.value.data.splice(index, 1);
                stats.value.total--;
            }
        });
    };
    
    onMounted(() => {
        loadLeads();
        setupWebSocket();
    });
    
    onUnmounted(() => {
        if (window.Echo && companyId.value) {
            window.Echo.leave(`company.${companyId.value}`);
        }
    });
    
    return {
        leads,
        stats,
        ...rest,
    };
}
```

---

## 🔍 Debugging WebSockets

### **Verificar se está funcionando:**

```javascript
// No console do navegador
Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ WebSocket conectado!');
});

Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Erro WebSocket:', err);
});
```

### **Monitorar eventos:**

```bash
# Laravel
php artisan queue:work --verbose

# Soketi (logs em tempo real)
docker logs -f soketi
```

---

## 💰 Custos e Escalabilidade

### **Pusher (SaaS):**
- **Free:** 200k mensagens/dia, 100 conexões simultâneas
- **Paid:** $49/mês para 1M mensagens/dia

### **Soketi (Self-hosted):**
- **Grátis** (só paga servidor)
- Servidor pequeno: $5-10/mês (Digital Ocean, AWS)
- Suporta milhares de conexões

---

## 🎯 Conclusão

### **Para o seu CRM, recomendo:**

1. **Continuar com Inertia.js + Polling** para a maioria das features
2. **Adicionar WebSocket** apenas para:
   - Chat/mensagens
   - Notificações críticas
   - Dashboard em tempo real

### **Arquitetura Híbrida (Ideal):**

```
┌─────────────────────────────────────┐
│         Frontend (Vue)              │
├─────────────────────────────────────┤
│  Inertia.js  │  WebSocket (Echo)    │
│  (CRUD)      │  (Tempo Real)        │
└──────┬───────┴──────────┬───────────┘
       │                  │
┌──────▼──────┐  ┌───────▼────────┐
│   Laravel   │  │  Soketi/Pusher │
│  (Backend)  │  │  (WebSocket)   │
└─────────────┘  └────────────────┘
```

**Benefícios:**
- ✅ Simplicidade do Inertia para CRUD
- ✅ Tempo real onde necessário
- ✅ Melhor performance
- ✅ Custo otimizado

---

## 📚 Recursos

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Echo](https://laravel.com/docs/broadcasting#client-side-installation)
- [Soketi Docs](https://docs.soketi.app/)
- [Pusher Docs](https://pusher.com/docs)
