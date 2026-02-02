import axios, { AxiosInstance, AxiosError } from 'axios';
import type {
  CMSClientConfig,
  Page,
  Post,
  Site,
  PageFilters,
  PostFilters,
  PaginatedResponse,
  APIError,
} from './types';

/**
 * Cliente JavaScript para API do CMS Makin
 */
export class CMSClient {
  private client: AxiosInstance;

  /**
   * Cria uma nova instância do cliente CMS
   * @param config Configuração do cliente
   */
  constructor(config: CMSClientConfig) {
    this.client = axios.create({
      baseURL: config.baseURL,
      timeout: config.timeout || 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-Key': config.apiKey,
        ...config.headers,
      },
    });

    // Interceptor para tratamento de erros
    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError<APIError>) => {
        if (error.response) {
          const apiError: APIError = {
            message: error.response.data?.message || error.message,
            errors: error.response.data?.errors,
            status: error.response.status,
          };
          return Promise.reject(apiError);
        }
        return Promise.reject({ message: error.message });
      }
    );
  }

  // ==================== PAGES ====================

  /**
   * Busca todas as páginas com filtros
   * @param filters Filtros de busca e paginação
   * @returns Lista paginada de páginas
   */
  async getPages(filters?: PageFilters): Promise<PaginatedResponse<Page>> {
    const response = await this.client.get<PaginatedResponse<Page>>('/api/cms/pages', {
      params: filters,
    });
    return response.data;
  }

  /**
   * Busca uma página por ID
   * @param id ID da página
   * @returns Dados da página
   */
  async getPage(id: number): Promise<Page> {
    const response = await this.client.get<{ data: Page }>(`/api/cms/pages/${id}`);
    return response.data.data;
  }

  /**
   * Busca uma página por slug
   * @param slug Slug da página
   * @param siteId ID do site (opcional)
   * @returns Dados da página
   */
  async getPageBySlug(slug: string, siteId?: number): Promise<Page> {
    const response = await this.client.get<{ data: Page }>('/api/cms/pages/by-slug', {
      params: { slug, site_id: siteId },
    });
    return response.data.data;
  }

  /**
   * Cria uma nova página
   * @param data Dados da página
   * @returns Página criada
   */
  async createPage(data: Partial<Page>): Promise<Page> {
    const response = await this.client.post<{ data: Page }>('/api/cms/pages', data);
    return response.data.data;
  }

  /**
   * Atualiza uma página existente
   * @param id ID da página
   * @param data Dados para atualização
   * @returns Página atualizada
   */
  async updatePage(id: number, data: Partial<Page>): Promise<Page> {
    const response = await this.client.put<{ data: Page }>(`/api/cms/pages/${id}`, data);
    return response.data.data;
  }

  /**
   * Exclui uma página
   * @param id ID da página
   */
  async deletePage(id: number): Promise<void> {
    await this.client.delete(`/api/cms/pages/${id}`);
  }

  // ==================== POSTS ====================

  /**
   * Busca todos os posts com filtros
   * @param filters Filtros de busca e paginação
   * @returns Lista paginada de posts
   */
  async getPosts(filters?: PostFilters): Promise<PaginatedResponse<Post>> {
    const response = await this.client.get<PaginatedResponse<Post>>('/api/cms/posts', {
      params: filters,
    });
    return response.data;
  }

  /**
   * Busca um post por ID
   * @param id ID do post
   * @returns Dados do post
   */
  async getPost(id: number): Promise<Post> {
    const response = await this.client.get<{ data: Post }>(`/api/cms/posts/${id}`);
    return response.data.data;
  }

  /**
   * Busca um post por slug
   * @param slug Slug do post
   * @param siteId ID do site (opcional)
   * @returns Dados do post
   */
  async getPostBySlug(slug: string, siteId?: number): Promise<Post> {
    const response = await this.client.get<{ data: Post }>('/api/cms/posts/by-slug', {
      params: { slug, site_id: siteId },
    });
    return response.data.data;
  }

  /**
   * Cria um novo post
   * @param data Dados do post
   * @returns Post criado
   */
  async createPost(data: Partial<Post>): Promise<Post> {
    const response = await this.client.post<{ data: Post }>('/api/cms/posts', data);
    return response.data.data;
  }

  /**
   * Atualiza um post existente
   * @param id ID do post
   * @param data Dados para atualização
   * @returns Post atualizado
   */
  async updatePost(id: number, data: Partial<Post>): Promise<Post> {
    const response = await this.client.put<{ data: Post }>(`/api/cms/posts/${id}`, data);
    return response.data.data;
  }

  /**
   * Exclui um post
   * @param id ID do post
   */
  async deletePost(id: number): Promise<void> {
    await this.client.delete(`/api/cms/posts/${id}`);
  }

  // ==================== SITES ====================

  /**
   * Busca todos os sites
   * @returns Lista de sites
   */
  async getSites(): Promise<Site[]> {
    const response = await this.client.get<{ data: Site[] }>('/api/cms/sites');
    return response.data.data;
  }

  /**
   * Busca um site por ID
   * @param id ID do site
   * @returns Dados do site
   */
  async getSite(id: number): Promise<Site> {
    const response = await this.client.get<{ data: Site }>(`/api/cms/sites/${id}`);
    return response.data.data;
  }

  // ==================== PREVIEW ====================

  /**
   * Gera um token de preview para uma página
   * @param pageId ID da página
   * @returns Token de preview
   */
  async generatePagePreviewToken(pageId: number): Promise<string> {
    const response = await this.client.post<{ token: string }>(`/api/cms/pages/${pageId}/preview`);
    return response.data.token;
  }

  /**
   * Gera um token de preview para um post
   * @param postId ID do post
   * @returns Token de preview
   */
  async generatePostPreviewToken(postId: number): Promise<string> {
    const response = await this.client.post<{ token: string }>(`/api/cms/posts/${postId}/preview`);
    return response.data.token;
  }

  /**
   * Busca conteúdo de preview usando um token
   * @param token Token de preview
   * @returns Conteúdo da página ou post
   */
  async getPreviewContent(token: string): Promise<Page | Post> {
    const response = await this.client.get<{ data: Page | Post }>(`/api/cms/preview/${token}`);
    return response.data.data;
  }
}
