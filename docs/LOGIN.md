# 🔐 Sistema de Login - CRM Makin

## Acesso ao Sistema

A página de login está disponível em: **`http://localhost/login`**

## Credenciais de Teste

Após executar os seeders, você pode usar as seguintes credenciais:

### Admin
- **Email:** `admin@demo.com`
- **Senha:** `password`
- **Permissões:** Acesso total ao sistema

### Manager
- **Email:** `manager@demo.com`
- **Senha:** `password`
- **Permissões:** Gerenciar equipes e leads

### Vendedores
- **Email:** `john@demo.com` ou `jane@demo.com`
- **Senha:** `password`
- **Permissões:** Gerenciar leads atribuídos

### Suporte
- **Email:** `support@demo.com`
- **Senha:** `password`
- **Permissões:** Visualizar e responder tickets

## Como Preparar o Sistema

### 1. Executar as Migrations
```bash
php artisan migrate:fresh
```

### 2. Executar os Seeders
```bash
php artisan db:seed
```

### 3. Build dos Assets
```bash
npm run build
# ou para desenvolvimento
npm run dev
```

### 4. Iniciar o Servidor
```bash
php artisan serve
```

### 5. Acessar o Sistema
Abra seu navegador em: `http://localhost:8000/login`

## Design Brutalist

A tela de login segue o design system **Data Brutalism** do projeto:

✅ **Tipografia Oversized** - Título MAKIN em Space Grotesk  
✅ **Bordas Sólidas** - 2px borders, sem sombras  
✅ **Accent Color Único** - Laranja vibrante (#FF6B35)  
✅ **Layout Assimétrico** - Barra laranja vertical  
✅ **Grid Background** - Pattern de grid sutil  
✅ **Hover States** - Transições propositais  
✅ **Dark Mode Support** - Variáveis CSS theme-aware  

## Recursos da Tela

- ✅ Validação de campos
- ✅ Mensagens de erro contextuais
- ✅ Checkbox "Lembrar-me"
- ✅ Status do sistema em tempo real
- ✅ Animações e transições suaves
- ✅ Responsivo (mobile-friendly)
- ✅ Suporte a dark mode
- ✅ Grid pattern no background

## Fluxo de Autenticação

1. Usuário acessa `/login`
2. Preenche email e senha
3. Sistema valida credenciais
4. Verifica se conta está ativa
5. Cria sessão autenticada
6. Redireciona para `/dashboard`

## Middleware de Proteção

- **`auth`** - Garante que usuário está autenticado
- **`active`** - Verifica se conta do usuário está ativa

## Logout

Para fazer logout, use a rota POST `/logout` (já implementada).

## Troubleshooting

### Erro "CSRF token mismatch"
```bash
php artisan config:clear
php artisan cache:clear
```

### Erro "Class not found"
```bash
composer dump-autoload
```

### Erro "Mix manifest not found"
```bash
npm run build
```

### Erro "Database connection failed"
Verifique o arquivo `.env` e configure:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_makin
DB_USERNAME=root
DB_PASSWORD=
```

---

**Desenvolvido com ❤️ seguindo princípios de Clean Code e SOLID**
