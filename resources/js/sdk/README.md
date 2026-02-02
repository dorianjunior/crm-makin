# CMS Client SDK

SDK JavaScript/TypeScript para consumir a API do CMS Makin.

## 📦 Instalação

```bash
npm install @crm-makin/cms-client-sdk
# ou
yarn add @crm-makin/cms-client-sdk
```

## 🚀 Uso Básico

### Inicializando o Cliente

```javascript
import { CMSClient } from '@crm-makin/cms-client-sdk';

const cms = new CMSClient({
  baseURL: 'https://sua-api.com',
  apiKey: 'sua-api-key-aqui',
  timeout: 10000, // opcional, padrão: 10000ms
});
```

### TypeScript

O SDK possui tipagem completa TypeScript:

```typescript
import { CMSClient, Page, Post, ContentStatus } from '@crm-makin/cms-client-sdk';

const cms = new CMSClient({
  baseURL: process.env.CMS_API_URL!,
  apiKey: process.env.CMS_API_KEY!,
});
```

## 📄 Páginas (Pages)

### Listar Páginas

```javascript
// Listar todas as páginas publicadas
const pages = await cms.getPages({
  status: 'published',
  per_page: 10,
  page: 1,
});

console.log(pages.data); // Array de páginas
console.log(pages.total); // Total de registros
console.log(pages.current_page); // Página atual
```

### Buscar Página por ID

```javascript
const page = await cms.getPage(1);
console.log(page.title, page.content);
```

### Buscar Página por Slug

```javascript
const page = await cms.getPageBySlug('sobre-nos', 1); // slug, site_id
console.log(page.title);
```

### Criar Página

```javascript
const newPage = await cms.createPage({
  site_id: 1,
  title: 'Nova Página',
  slug: 'nova-pagina',
  content: {
    blocks: [
      {
        type: 'heading',
        data: { text: 'Bem-vindo', level: 1 },
      },
      {
        type: 'paragraph',
        data: { text: 'Conteúdo da página' },
      },
    ],
  },
  status: 'draft',
  meta: {
    title: 'Nova Página - SEO Title',
    description: 'Descrição para SEO',
    keywords: ['página', 'nova'],
  },
});
```

### Atualizar Página

```javascript
const updatedPage = await cms.updatePage(1, {
  title: 'Título Atualizado',
  status: 'published',
});
```

### Excluir Página

```javascript
await cms.deletePage(1);
```

## 📝 Posts (Blog)

### Listar Posts

```javascript
// Posts publicados com filtros
const posts = await cms.getPosts({
  status: 'published',
  category: 'tecnologia',
  tag: 'javascript',
  search: 'tutorial',
  per_page: 12,
  page: 1,
});

posts.data.forEach(post => {
  console.log(post.title, post.excerpt);
});
```

### Buscar Post por ID

```javascript
const post = await cms.getPost(5);
console.log(post.title, post.content, post.tags);
```

### Buscar Post por Slug

```javascript
const post = await cms.getPostBySlug('meu-primeiro-post');
console.log(post.title);
```

### Criar Post

```javascript
const newPost = await cms.createPost({
  site_id: 1,
  title: 'Como usar o CMS SDK',
  slug: 'como-usar-cms-sdk',
  excerpt: 'Um guia completo para usar nosso SDK',
  content: {
    blocks: [
      {
        type: 'paragraph',
        data: { text: 'Introdução ao SDK...' },
      },
    ],
  },
  featured_image: 'https://example.com/image.jpg',
  tags: ['tutorial', 'sdk', 'javascript'],
  categories: ['desenvolvimento'],
  status: 'draft',
  meta: {
    title: 'Como usar o CMS SDK - Guia Completo',
    description: 'Aprenda a usar nosso SDK JavaScript',
  },
});
```

### Atualizar Post

```javascript
const updatedPost = await cms.updatePost(5, {
  title: 'Título Atualizado do Post',
  status: 'published',
  published_at: new Date().toISOString(),
});
```

### Excluir Post

```javascript
await cms.deletePost(5);
```

## 🌐 Sites

### Listar Sites

```javascript
const sites = await cms.getSites();
sites.forEach(site => {
  console.log(site.name, site.domain);
});
```

### Buscar Site por ID

```javascript
const site = await cms.getSite(1);
console.log(site.name, site.settings);
```

## 👁️ Preview de Conteúdo

### Gerar Token de Preview

```javascript
// Para página
const pageToken = await cms.generatePagePreviewToken(1);
const previewUrl = `https://seu-site.com/preview?token=${pageToken}`;

// Para post
const postToken = await cms.generatePostPreviewToken(5);
```

### Buscar Conteúdo com Token de Preview

```javascript
const previewContent = await cms.getPreviewContent(token);
console.log(previewContent); // Page ou Post
```

## 🛠️ Exemplos de Uso Completo

### Exemplo: Site Vue.js

```vue
<template>
  <div>
    <h1>{{ page?.title }}</h1>
    <div v-html="renderContent(page?.content)"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CMSClient } from '@crm-makin/cms-client-sdk';

const cms = new CMSClient({
  baseURL: import.meta.env.VITE_CMS_API_URL,
  apiKey: import.meta.env.VITE_CMS_API_KEY,
});

const page = ref(null);

onMounted(async () => {
  try {
    page.value = await cms.getPageBySlug('home');
  } catch (error) {
    console.error('Erro ao carregar página:', error);
  }
});

function renderContent(content) {
  // Renderizar blocos de conteúdo
  return content?.blocks?.map(block => {
    if (block.type === 'paragraph') {
      return `<p>${block.data.text}</p>`;
    }
    if (block.type === 'heading') {
      return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`;
    }
    return '';
  }).join('');
}
</script>
```

### Exemplo: Blog com React

```jsx
import React, { useEffect, useState } from 'react';
import { CMSClient } from '@crm-makin/cms-client-sdk';

const cms = new CMSClient({
  baseURL: process.env.REACT_APP_CMS_API_URL,
  apiKey: process.env.REACT_APP_CMS_API_KEY,
});

function BlogList() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadPosts() {
      try {
        const response = await cms.getPosts({
          status: 'published',
          per_page: 10,
        });
        setPosts(response.data);
      } catch (error) {
        console.error('Erro ao carregar posts:', error);
      } finally {
        setLoading(false);
      }
    }
    loadPosts();
  }, []);

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <h1>Blog</h1>
      {posts.map(post => (
        <article key={post.id}>
          <h2>{post.title}</h2>
          <p>{post.excerpt}</p>
          <small>{new Date(post.published_at).toLocaleDateString()}</small>
        </article>
      ))}
    </div>
  );
}

export default BlogList;
```

## ❌ Tratamento de Erros

```javascript
try {
  const page = await cms.getPage(999);
} catch (error) {
  console.error('Erro:', error.message); // Mensagem de erro
  console.error('Status:', error.status); // Código HTTP
  console.error('Validações:', error.errors); // Erros de validação
}
```

## 🔑 Autenticação

O SDK usa API Key para autenticação. Configure a chave no header `X-API-Key`:

```javascript
const cms = new CMSClient({
  baseURL: 'https://api.exemplo.com',
  apiKey: 'sua-api-key-secreta',
  headers: {
    // Headers personalizados opcionais
    'X-Custom-Header': 'valor',
  },
});
```

## 📊 Tipos TypeScript

```typescript
import {
  Page,
  Post,
  Site,
  ContentStatus,
  PageFilters,
  PostFilters,
  PaginatedResponse,
} from '@crm-makin/cms-client-sdk';

// ContentStatus enum
type Status = ContentStatus.DRAFT | ContentStatus.PUBLISHED;

// Filtros tipados
const filters: PostFilters = {
  status: ContentStatus.PUBLISHED,
  per_page: 10,
  tag: 'javascript',
};
```

## 🧪 Testando o SDK

```bash
npm test
```

## 📝 Licença

MIT

## 🤝 Contribuindo

Contribuições são bem-vindas! Abra uma issue ou pull request.

---

**CRM Makin** - Sistema de gerenciamento de conteúdo headless
