<template>
  <MainLayout>
    <div class="menus-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Menus de Navegação</h1>
          <p class="subtitle">Gerencie os menus e estrutura de navegação dos sites</p>
        </div>
        <Button variant="primary" @click="showMenuModal = true">
          <i class="fa fa-plus"></i>
          Novo Menu
        </Button>
      </div>

      <!-- Menus List -->
      <div class="menus-grid">
        <div v-for="menu in menus" :key="menu.id" class="menu-card">
          <div class="menu-header">
            <div>
              <h3>{{ menu.name }}</h3>
              <p class="menu-location">{{ menu.location }}</p>
              <span class="site-badge">{{ menu.site?.name }}</span>
            </div>
            <div class="menu-actions">
              <Button variant="secondary" size="small" @click="editMenu(menu)">
                <i class="fa fa-edit"></i>
                Editar
              </Button>
              <Button variant="danger" size="small" @click="deleteMenu(menu)">
                <i class="fa fa-trash"></i>
              </Button>
            </div>
          </div>

          <div class="menu-stats">
            <span><i class="fa fa-list"></i> {{ menu.items_count || 0 }} itens</span>
            <span><i class="fa fa-layer-group"></i> {{ getMaxDepth(menu.items) }} níveis</span>
          </div>

          <div class="menu-preview">
            <div v-if="menu.items?.length" class="menu-tree">
              <MenuItem
                v-for="item in menu.items"
                :key="item.id"
                :item="item"
                :depth="0"
              />
            </div>
            <div v-else class="empty-menu">
              <i class="fa fa-folder-open"></i>
              <p>Menu vazio</p>
            </div>
          </div>
        </div>

        <div v-if="!menus.length" class="empty-state">
          <i class="fa fa-bars"></i>
          <h3>Nenhum menu criado</h3>
          <p>Crie seu primeiro menu de navegação</p>
          <Button variant="primary" @click="showMenuModal = true">
            <i class="fa fa-plus"></i>
            Criar Primeiro Menu
          </Button>
        </div>
      </div>
    </div>

    <!-- Menu Modal (Create/Edit) -->
    <Modal
      v-if="showMenuModal"
      @close="closeMenuModal"
      :title="editingMenu ? 'Editar Menu' : 'Novo Menu'"
      size="large"
    >
      <div class="menu-form">
        <div class="form-group">
          <label>Nome do Menu *</label>
          <Input v-model="menuForm.name" placeholder="Ex: Menu Principal" />
          <span v-if="modalErrors.name" class="error-message">{{ modalErrors.name }}</span>
        </div>

        <div class="form-group">
          <label>Localização *</label>
          <select v-model="menuForm.location" class="form-select">
            <option value="">Selecione uma localização</option>
            <option value="header">Cabeçalho</option>
            <option value="footer">Rodapé</option>
            <option value="sidebar">Sidebar</option>
            <option value="mobile">Mobile</option>
            <option value="custom">Personalizado</option>
          </select>
          <span v-if="modalErrors.location" class="error-message">{{ modalErrors.location }}</span>
        </div>

        <div class="form-group">
          <label>Site *</label>
          <select v-model="menuForm.site_id" class="form-select">
            <option value="">Selecione um site</option>
            <option v-for="site in sites" :key="site.id" :value="site.id">
              {{ site.name }}
            </option>
          </select>
          <span v-if="modalErrors.site_id" class="error-message">{{ modalErrors.site_id }}</span>
        </div>

        <div v-if="editingMenu" class="menu-builder">
          <h4>Itens do Menu</h4>

          <div class="builder-layout">
            <div class="available-items">
              <h5>Adicionar Itens</h5>

              <!-- Pages -->
              <div class="item-section">
                <button @click="toggleSection('pages')" class="section-toggle">
                  <i class="fa" :class="expandedSections.pages ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                  Páginas
                </button>
                <div v-if="expandedSections.pages" class="section-content">
                  <div v-for="page in pages" :key="page.id" class="available-item">
                    <span>{{ page.title }}</span>
                    <Button variant="link" size="small" @click="addMenuItem('page', page)">
                      <i class="fa fa-plus"></i>
                    </Button>
                  </div>
                </div>
              </div>

              <!-- Custom Link -->
              <div class="item-section">
                <button @click="toggleSection('custom')" class="section-toggle">
                  <i class="fa" :class="expandedSections.custom ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                  Link Personalizado
                </button>
                <div v-if="expandedSections.custom" class="section-content">
                  <div class="custom-link-form">
                    <Input v-model="customLink.label" placeholder="Texto do link" />
                    <Input v-model="customLink.url" placeholder="URL" />
                    <Button variant="primary" size="small" @click="addCustomLink">
                      Adicionar
                    </Button>
                  </div>
                </div>
              </div>

              <!-- Categories -->
              <div class="item-section">
                <button @click="toggleSection('categories')" class="section-toggle">
                  <i class="fa" :class="expandedSections.categories ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                  Categorias
                </button>
                <div v-if="expandedSections.categories" class="section-content">
                  <div v-for="category in categories" :key="category.id" class="available-item">
                    <span>{{ category.name }}</span>
                    <Button variant="link" size="small" @click="addMenuItem('category', category)">
                      <i class="fa fa-plus"></i>
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <div class="menu-structure">
              <h5>Estrutura do Menu</h5>
              <div class="menu-items-list">
                <draggable
                  v-model="menuForm.items"
                  item-key="id"
                  :group="{ name: 'menu-items' }"
                  handle=".drag-handle"
                  @change="updateMenuOrder"
                >
                  <template #item="{ element: item, index }">
                    <MenuItemEditor
                      :item="item"
                      :index="index"
                      @edit="editMenuItem"
                      @delete="deleteMenuItem"
                    />
                  </template>
                </draggable>

                <div v-if="!menuForm.items.length" class="empty-structure">
                  <i class="fa fa-arrow-left"></i>
                  <p>Adicione itens ao menu usando a barra lateral</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="closeMenuModal">Cancelar</Button>
        <Button variant="primary" @click="saveMenu" :disabled="savingMenu">
          {{ editingMenu ? 'Atualizar' : 'Criar Menu' }}
        </Button>
      </template>
    </Modal>

    <!-- Menu Item Edit Modal -->
    <Modal
      v-if="showItemModal"
      @close="showItemModal = false"
      title="Editar Item do Menu"
    >
      <div class="item-edit-form">
        <div class="form-group">
          <label>Texto de Navegação</label>
          <Input v-model="editingItem.label" />
        </div>

        <div class="form-group">
          <label>URL</label>
          <Input v-model="editingItem.url" />
        </div>

        <div class="form-group">
          <label>Atributo Title</label>
          <Input v-model="editingItem.title" placeholder="Texto ao passar o mouse" />
        </div>

        <div class="form-group">
          <label>Classes CSS</label>
          <Input v-model="editingItem.css_class" placeholder="classe1 classe2" />
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="editingItem.target_blank">
            Abrir em nova aba
          </label>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="showItemModal = false">Cancelar</Button>
        <Button variant="primary" @click="saveMenuItem">Salvar</Button>
      </template>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';

// MenuItem Component
const MenuItem = {
  name: 'MenuItem',
  props: ['item', 'depth'],
  template: `
    <div class="menu-item" :style="{ paddingLeft: (depth * 20) + 'px' }">
      <i class="fa fa-grip-vertical"></i>
      <span>{{ item.label }}</span>
      <span v-if="item.children?.length" class="children-count">{{ item.children.length }}</span>
    </div>
    <MenuItem
      v-for="child in item.children"
      :key="child.id"
      :item="child"
      :depth="depth + 1"
    />
  `
};

// MenuItemEditor Component
const MenuItemEditor = {
  name: 'MenuItemEditor',
  props: ['item', 'index'],
  emits: ['edit', 'delete'],
  template: `
    <div class="menu-item-editor">
      <div class="item-row">
        <i class="fa fa-grip-vertical drag-handle"></i>
        <span class="item-label">{{ item.label }}</span>
        <div class="item-actions">
          <button @click="$emit('edit', item)" class="action-btn">
            <i class="fa fa-edit"></i>
          </button>
          <button @click="$emit('delete', index)" class="action-btn danger">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
  `
};

const props = defineProps({
  menus: Array,
  sites: Array,
  pages: Array,
  categories: Array,
  errors: Object
});

const showMenuModal = ref(false);
const showItemModal = ref(false);
const editingMenu = ref(null);
const editingItem = ref(null);
const savingMenu = ref(false);
const modalErrors = ref({});

const expandedSections = reactive({
  pages: true,
  custom: false,
  categories: false
});

const customLink = reactive({
  label: '',
  url: ''
});

const menuForm = ref({
  name: '',
  location: '',
  site_id: '',
  items: []
});

const breadcrumbs = [
  { label: 'CMS', url: '/cms' },
  { label: 'Menus' }
];

const getMaxDepth = (items, depth = 1) => {
  if (!items?.length) return depth - 1;
  return Math.max(...items.map(item =>
    getMaxDepth(item.children, depth + 1)
  ));
};

const toggleSection = (section) => {
  expandedSections[section] = !expandedSections[section];
};

const editMenu = (menu) => {
  editingMenu.value = menu;
  menuForm.value = {
    name: menu.name,
    location: menu.location,
    site_id: menu.site_id,
    items: menu.items || []
  };
  showMenuModal.value = true;
};

const closeMenuModal = () => {
  showMenuModal.value = false;
  editingMenu.value = null;
  modalErrors.value = {};
  menuForm.value = {
    name: '',
    location: '',
    site_id: '',
    items: []
  };
};

const addMenuItem = (type, data) => {
  const newItem = {
    id: `temp-${Date.now()}`,
    type: type,
    label: data.title || data.name,
    url: type === 'page' ? `/page/${data.slug}` : `/category/${data.slug}`,
    target_blank: false,
    css_class: '',
    title: '',
    children: []
  };
  menuForm.value.items.push(newItem);
};

const addCustomLink = () => {
  if (customLink.label && customLink.url) {
    const newItem = {
      id: `temp-${Date.now()}`,
      type: 'custom',
      label: customLink.label,
      url: customLink.url,
      target_blank: false,
      css_class: '',
      title: '',
      children: []
    };
    menuForm.value.items.push(newItem);
    customLink.label = '';
    customLink.url = '';
  }
};

const editMenuItem = (item) => {
  editingItem.value = { ...item };
  showItemModal.value = true;
};

const saveMenuItem = () => {
  const index = menuForm.value.items.findIndex(i => i.id === editingItem.value.id);
  if (index !== -1) {
    menuForm.value.items[index] = { ...editingItem.value };
  }
  showItemModal.value = false;
  editingItem.value = null;
};

const deleteMenuItem = (index) => {
  if (confirm('Remover este item do menu?')) {
    menuForm.value.items.splice(index, 1);
  }
};

const updateMenuOrder = () => {
  // Order is automatically updated by draggable
};

const saveMenu = () => {
  savingMenu.value = true;
  const url = editingMenu.value ? `/cms/menus/${editingMenu.value.id}` : '/cms/menus';
  const method = editingMenu.value ? 'put' : 'post';

  router[method](url, menuForm.value, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      closeMenuModal();
    },
    onError: (errors) => {
      modalErrors.value = errors;
    },
    onFinish: () => {
      savingMenu.value = false;
    }
  });
};

const deleteMenu = (menu) => {
  if (confirm(`Tem certeza que deseja excluir o menu "${menu.name}"?`)) {
    router.delete(`/cms/menus/${menu.id}`, {
      preserveState: true,
      preserveScroll: true
    });
  }
};
</script>

<style scoped lang="scss">
.menus-page {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  .subtitle {
    margin: 0;
    color: var(--text-secondary);
  }
}

.menus-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
}

.menu-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
  transition: all 0.3s;

  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .menu-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);

    h3 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
    }

    .menu-location {
      margin: 0 0 0.5rem 0;
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .site-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      background: var(--bg-tertiary);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }

    .menu-actions {
      display: flex;
      gap: 0.5rem;
    }
  }

  .menu-stats {
    display: flex;
    gap: 2rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: var(--text-secondary);

    span {
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: var(--color-primary);
      }
    }
  }

  .menu-preview {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 1rem;
    max-height: 300px;
    overflow-y: auto;

    .menu-tree {
      .menu-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        color: var(--text-primary);
        font-size: 0.9rem;

        i {
          color: var(--text-tertiary);
        }

        .children-count {
          margin-left: auto;
          padding: 0.25rem 0.5rem;
          background: var(--color-primary);
          color: white;
          border-radius: 12px;
          font-size: 0.75rem;
        }
      }
    }

    .empty-menu {
      text-align: center;
      padding: 2rem;
      color: var(--text-secondary);

      i {
        font-size: 2rem;
        opacity: 0.3;
        margin-bottom: 0.5rem;
      }

      p {
        margin: 0;
      }
    }
  }
}

// Menu Builder
.menu-builder {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);

  h4 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
  }

  .builder-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    min-height: 400px;

    .available-items {
      border: 1px solid var(--border-color);
      border-radius: 4px;
      overflow: hidden;

      h5 {
        margin: 0;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 0.9rem;
        border-bottom: 1px solid var(--border-color);
      }

      .item-section {
        border-bottom: 1px solid var(--border-color);

        &:last-child {
          border-bottom: none;
        }

        .section-toggle {
          width: 100%;
          padding: 0.75rem 1rem;
          border: none;
          background: transparent;
          text-align: left;
          cursor: pointer;
          color: var(--text-primary);
          display: flex;
          align-items: center;
          gap: 0.5rem;
          transition: background 0.2s;

          &:hover {
            background: var(--bg-secondary);
          }
        }

        .section-content {
          padding: 0.5rem 0;

          .available-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;

            &:hover {
              background: var(--bg-secondary);
            }
          }

          .custom-link-form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
          }
        }
      }
    }

    .menu-structure {
      border: 1px solid var(--border-color);
      border-radius: 4px;
      overflow: hidden;

      h5 {
        margin: 0;
        padding: 1rem;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 0.9rem;
        border-bottom: 1px solid var(--border-color);
      }

      .menu-items-list {
        padding: 1rem;
        min-height: 300px;

        .menu-item-editor {
          margin-bottom: 0.5rem;
          padding: 1rem;
          background: var(--bg-secondary);
          border: 1px solid var(--border-color);
          border-radius: 4px;

          .item-row {
            display: flex;
            align-items: center;
            gap: 1rem;

            .drag-handle {
              cursor: move;
              color: var(--text-tertiary);
            }

            .item-label {
              flex: 1;
              color: var(--text-primary);
            }

            .item-actions {
              display: flex;
              gap: 0.5rem;

              .action-btn {
                padding: 0.25rem 0.5rem;
                border: 1px solid var(--border-color);
                background: var(--bg-primary);
                border-radius: 4px;
                cursor: pointer;
                color: var(--text-primary);
                transition: all 0.2s;

                &:hover {
                  background: var(--bg-tertiary);
                  border-color: var(--color-primary);
                  color: var(--color-primary);
                }

                &.danger:hover {
                  border-color: var(--color-error);
                  color: var(--color-error);
                }
              }
            }
          }
        }

        .empty-structure {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 3rem;
          color: var(--text-secondary);
          text-align: center;

          i {
            font-size: 2rem;
            opacity: 0.3;
            margin-bottom: 0.5rem;
          }

          p {
            margin: 0;
          }
        }
      }
    }
  }
}

// Form Styles
.menu-form,
.item-edit-form {
  .form-group {
    margin-bottom: 1.5rem;

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-primary);

      input[type="checkbox"] {
        margin-right: 0.5rem;
      }
    }

    .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      background: var(--bg-primary);
      color: var(--text-primary);

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }
    }

    .error-message {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.85rem;
      color: var(--color-error);
    }
  }
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);

  i {
    font-size: 4rem;
    opacity: 0.3;
    margin-bottom: 1rem;
  }

  h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  p {
    margin: 0 0 2rem 0;
  }
}

@media (max-width: 768px) {
  .menus-grid {
    grid-template-columns: 1fr;
  }

  .menu-builder .builder-layout {
    grid-template-columns: 1fr;

    .available-items {
      max-height: 300px;
      overflow-y: auto;
    }
  }
}
</style>
