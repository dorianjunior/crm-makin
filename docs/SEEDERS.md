# Database Seeders - CRM API

## 📦 Seeders Criados

### 1. PermissionSeeder
Cria **65 permissões** organizadas por módulo:
- Companies (view, create, edit, delete)
- Users (view, create, edit, delete)
- Leads (view, create, edit, delete, assign)
- Activities (view, create, edit, delete)
- Tasks (view, create, edit, delete)
- Pipelines (view, create, edit, delete)
- Products (view, create, edit, delete)
- Proposals (view, create, edit, delete)
- Communication (emails, whatsapp)
- Files (view, upload, delete)
- Reports (view, export)
- Settings (view, edit)

### 2. RoleSeeder
Cria **5 roles** com permissões associadas:

#### **Admin**
- Todas as permissões do sistema
- Acesso completo

#### **Manager**
- Gerenciamento completo exceto:
  - Criar/deletar empresas
  - Editar configurações do sistema

#### **Sales**
- Leads (view, create, edit, assign)
- Activities, Tasks, Pipelines (view, create, edit)
- Products (view)
- Proposals (view, create, edit)
- Communication (emails, whatsapp)
- Files (view, upload)

#### **Support**
- Leads (view, edit)
- Activities, Tasks (view, create, edit)
- Communication (emails, whatsapp)
- Files (view)

#### **Viewer**
- Apenas permissões de visualização

### 3. CompanySeeder
Cria **3 empresas exemplo**:
1. **Demo Company** (Premium)
2. **Acme Corporation** (Enterprise)
3. **Tech Solutions Ltd** (Basic)

### 4. UserSeeder
Cria **5 usuários** na Demo Company:

| Nome | Email | Role | Senha |
|------|-------|------|-------|
| Admin User | admin@demo.com | Admin | password |
| Manager User | manager@demo.com | Manager | password |
| John Sales | john@demo.com | Sales | password |
| Jane Sales | jane@demo.com | Sales | password |
| Support User | support@demo.com | Support | password |

### 5. PipelineSeeder
Cria **2 pipelines** para cada empresa:

#### **Sales Pipeline**
1. New Lead
2. Contact Made
3. Qualification
4. Proposal Sent
5. Negotiation
6. Closed Won
7. Closed Lost

#### **Support Pipeline**
1. New Ticket
2. In Progress
3. Waiting Customer
4. Resolved
5. Closed

### 6. LeadSourceSeeder
Cria **12 fontes de leads** para cada empresa:
- Website
- Facebook
- Instagram
- LinkedIn
- Google Ads
- Email Campaign
- Referral
- Cold Call
- Event
- Direct
- WhatsApp
- Partner

### 7. ProductSeeder
Cria **8 produtos** na Demo Company:
- Basic Plan (R$ 29,90)
- Professional Plan (R$ 79,90)
- Enterprise Plan (R$ 199,90)
- Consulting Service (R$ 150,00)
- Training Package (R$ 500,00)
- Setup Fee (R$ 99,00)
- Custom Integration (R$ 999,00)
- Support Package (R$ 49,90)

### 8. MessageTemplateSeeder
Cria **8 templates de mensagem**:

**Email:**
- Welcome Email
- Follow Up
- Proposal Sent
- Meeting Confirmation
- Thank You for Your Purchase

**WhatsApp:**
- Quick Follow Up
- Meeting Reminder
- Thank You

### 9. LeadSeeder
Cria **8 leads exemplo** com diferentes status:
- Michael Johnson (new)
- Sarah Williams (contacted)
- David Brown (qualified)
- Emma Davis (proposal)
- James Wilson (negotiation)
- Olivia Martinez (won)
- William Garcia (new)
- Sophia Rodriguez (contacted)

## 🚀 Como Usar

### Executar todos os seeders:
```bash
php artisan db:seed
```

### Executar seeder específico:
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=CompanySeeder
```

### Resetar e popular novamente:
```bash
php artisan migrate:fresh --seed
```

## 📋 Ordem de Execução

A ordem é importante devido às dependências:

1. **PermissionSeeder** - Base para roles
2. **RoleSeeder** - Depende de permissions
3. **CompanySeeder** - Base para usuários
4. **UserSeeder** - Depende de companies e roles
5. **PipelineSeeder** - Depende de companies
6. **LeadSourceSeeder** - Depende de companies
7. **ProductSeeder** - Depende de companies
8. **MessageTemplateSeeder** - Depende de companies
9. **LeadSeeder** - Depende de companies, sources e users

## 🔑 Credenciais de Teste

Use qualquer um dos usuários criados para testar:

```json
{
  "email": "admin@demo.com",
  "password": "password"
}
```

## 📊 Estatísticas

Após executar os seeders, o banco terá:
- ✅ 65 Permissions
- ✅ 5 Roles com permissões vinculadas
- ✅ 3 Companies
- ✅ 5 Users
- ✅ 6 Pipelines (2 por empresa)
- ✅ 30 Pipeline Stages
- ✅ 36 Lead Sources (12 por empresa)
- ✅ 8 Products
- ✅ 8 Message Templates
- ✅ 8 Leads

**Total: ~170+ registros criados**
