/**
 * Configuração do Cliente CMS
 */
export interface CMSClientConfig {
  baseURL: string;
  apiKey: string;
  timeout?: number;
  headers?: Record<string, string>;
}

/**
 * Status de Conteúdo
 */
export enum ContentStatus {
  DRAFT = 'draft',
  REVIEW = 'review',
  APPROVED = 'approved',
  PUBLISHED = 'published',
  ARCHIVED = 'archived',
}

/**
 * Metadados de Página
 */
export interface PageMeta {
  title?: string;
  description?: string;
  keywords?: string[];
  og_image?: string;
  [key: string]: any;
}

/**
 * Modelo de Página
 */
export interface Page {
  id: number;
  site_id: number;
  title: string;
  slug: string;
  content: any;
  status: ContentStatus;
  meta?: PageMeta;
  published_at?: string;
  created_at: string;
  updated_at: string;
  created_by: number;
  updated_by?: number;
}

/**
 * Modelo de Post
 */
export interface Post {
  id: number;
  site_id: number;
  title: string;
  slug: string;
  excerpt?: string;
  content: any;
  status: ContentStatus;
  featured_image?: string;
  meta?: PageMeta;
  tags?: string[];
  categories?: string[];
  published_at?: string;
  created_at: string;
  updated_at: string;
  created_by: number;
  updated_by?: number;
}

/**
 * Modelo de Site
 */
export interface Site {
  id: number;
  company_id: number;
  name: string;
  domain: string;
  settings?: Record<string, any>;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

/**
 * Parâmetros de Paginação
 */
export interface PaginationParams {
  page?: number;
  per_page?: number;
}

/**
 * Parâmetros de Filtro para Páginas
 */
export interface PageFilters extends PaginationParams {
  site_id?: number;
  status?: ContentStatus;
  search?: string;
}

/**
 * Parâmetros de Filtro para Posts
 */
export interface PostFilters extends PaginationParams {
  site_id?: number;
  status?: ContentStatus;
  tag?: string;
  category?: string;
  search?: string;
}

/**
 * Resposta Paginada
 */
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

/**
 * Resposta de Erro da API
 */
export interface APIError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}
