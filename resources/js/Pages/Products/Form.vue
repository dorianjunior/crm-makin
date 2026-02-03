<template>
  <MainLayout>
    <div class="product-form">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>{{ isEdit ? 'Editar Produto' : 'Novo Produto' }}</h1>
        </div>
        <div class="header-actions">
          <Button variant="secondary" @click="goBack">
            <i class="fa fa-arrow-left"></i>
            Voltar
          </Button>
          <Button variant="primary" @click="saveProduct" :disabled="saving">
            <i class="fa fa-save"></i>
            Salvar
          </Button>
        </div>
      </div>

      <div class="form-layout">
        <!-- Main Content -->
        <div class="form-main">
          <div class="form-card">
            <h3>Informações Básicas</h3>

            <div class="form-group">
              <label>Nome do Produto *</label>
              <Input v-model="form.name" placeholder="Ex: Desenvolvimento de Website" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>SKU</label>
                <Input v-model="form.sku" placeholder="Ex: PROD-001" />
              </div>

              <div class="form-group">
                <label>Tipo *</label>
                <select v-model="form.type" class="form-select">
                  <option value="product">Produto</option>
                  <option value="service">Serviço</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Descrição</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Descreva o produto ou serviço..."
              ></textarea>
            </div>

            <div class="form-group">
              <label>Descrição Detalhada</label>
              <div class="editor-toolbar">
                <div class="toolbar-group">
                  <button @click="execCommand('bold')" class="toolbar-btn" type="button">
                    <i class="fa fa-bold"></i>
                  </button>
                  <button @click="execCommand('italic')" class="toolbar-btn" type="button">
                    <i class="fa fa-italic"></i>
                  </button>
                  <button @click="execCommand('underline')" class="toolbar-btn" type="button">
                    <i class="fa fa-underline"></i>
                  </button>
                </div>

                <div class="toolbar-group">
                  <button @click="execCommand('insertUnorderedList')" class="toolbar-btn" type="button">
                    <i class="fa fa-list-ul"></i>
                  </button>
                  <button @click="execCommand('insertOrderedList')" class="toolbar-btn" type="button">
                    <i class="fa fa-list-ol"></i>
                  </button>
                </div>
              </div>

              <div class="editor-content">
                <div
                  ref="editor"
                  class="editor"
                  contenteditable="true"
                  @input="updateContent"
                  v-html="form.detailed_description"
                ></div>
              </div>
            </div>
          </div>

          <div class="form-card">
            <h3>Preço e Valores</h3>

            <div class="form-row">
              <div class="form-group">
                <label>Preço Base *</label>
                <div class="currency-input">
                  <span class="currency-symbol">R$</span>
                  <Input
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                  />
                </div>
              </div>

              <div class="form-group">
                <label>Custo</label>
                <div class="currency-input">
                  <span class="currency-symbol">R$</span>
                  <Input
                    v-model="form.cost"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                  />
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Margem de Lucro</label>
                <div class="info-display">
                  <span class="info-value">{{ calculateMargin }}%</span>
                </div>
              </div>

              <div class="form-group">
                <label>Lucro</label>
                <div class="info-display">
                  <span class="info-value">{{ formatCurrency(calculateProfit) }}</span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.allow_discount">
                Permitir desconto nas propostas
              </label>
            </div>

            <div v-if="form.allow_discount" class="form-row">
              <div class="form-group">
                <label>Desconto Máximo (%)</label>
                <Input
                  v-model="form.max_discount_percent"
                  type="number"
                  step="1"
                  min="0"
                  max="100"
                  placeholder="0"
                />
              </div>

              <div class="form-group">
                <label>Preço Mínimo</label>
                <div class="info-display">
                  <span class="info-value">{{ formatCurrency(calculateMinPrice) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="form.type === 'product'" class="form-card">
            <h3>Estoque</h3>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.track_inventory">
                Controlar estoque
              </label>
            </div>

            <div v-if="form.track_inventory" class="form-row">
              <div class="form-group">
                <label>Quantidade Atual</label>
                <Input
                  v-model="form.stock_quantity"
                  type="number"
                  min="0"
                  placeholder="0"
                />
              </div>

              <div class="form-group">
                <label>Estoque Mínimo</label>
                <Input
                  v-model="form.stock_min"
                  type="number"
                  min="0"
                  placeholder="0"
                />
                <span class="help-text">Alerta quando atingir este valor</span>
              </div>
            </div>

            <div v-if="form.track_inventory && form.stock_quantity <= form.stock_min" class="alert alert-warning">
              <i class="fa fa-exclamation-triangle"></i>
              Estoque baixo! Quantidade atual está abaixo do mínimo.
            </div>
          </div>

          <div class="form-card">
            <h3>Características e Especificações</h3>

            <div class="features-list">
              <div
                v-for="(feature, index) in form.features"
                :key="index"
                class="feature-item"
              >
                <Input
                  v-model="feature.name"
                  placeholder="Nome da característica"
                  class="feature-name"
                />
                <Input
                  v-model="feature.value"
                  placeholder="Valor"
                  class="feature-value"
                />
                <button @click="removeFeature(index)" class="remove-btn">
                  <i class="fa fa-trash"></i>
                </button>
              </div>

              <Button variant="secondary" size="small" @click="addFeature">
                <i class="fa fa-plus"></i>
                Adicionar Característica
              </Button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="form-sidebar">
          <!-- Status -->
          <div class="sidebar-card">
            <h3>Status</h3>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.is_active">
                Produto Ativo
              </label>
              <span class="help-text">
                {{ form.is_active ? 'Visível para vendas' : 'Oculto do catálogo' }}
              </span>
            </div>
          </div>

          <!-- Image -->
          <div class="sidebar-card">
            <h3>Imagem</h3>

            <div v-if="form.image" class="image-preview">
              <img :src="form.image" alt="Imagem do produto">
              <button @click="form.image = null" class="remove-image-btn">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <div v-else class="image-placeholder">
              <i class="fa fa-box"></i>
              <p>Nenhuma imagem</p>
            </div>

            <input
              ref="imageInput"
              type="file"
              accept="image/*"
              @change="handleImageUpload"
              style="display: none"
            >

            <Button
              variant="secondary"
              size="small"
              @click="$refs.imageInput.click()"
              class="mt-2"
              block
            >
              <i class="fa fa-upload"></i>
              {{ form.image ? 'Alterar Imagem' : 'Enviar Imagem' }}
            </Button>
          </div>

          <!-- Gallery -->
          <div class="sidebar-card">
            <h3>Galeria</h3>

            <div class="gallery-grid">
              <div
                v-for="(img, index) in form.gallery"
                :key="index"
                class="gallery-item"
              >
                <img :src="img" alt="Galeria">
                <button @click="removeGalleryImage(index)" class="remove-gallery-btn">
                  <i class="fa fa-times"></i>
                </button>
              </div>
            </div>

            <input
              ref="galleryInput"
              type="file"
              accept="image/*"
              multiple
              @change="handleGalleryUpload"
              style="display: none"
            >

            <Button
              variant="secondary"
              size="small"
              @click="$refs.galleryInput.click()"
              block
            >
              <i class="fa fa-images"></i>
              Adicionar Imagens
            </Button>
          </div>

          <!-- Category -->
          <div class="sidebar-card">
            <h3>Categoria</h3>

            <div class="form-group">
              <label>Selecione a categoria</label>
              <select v-model="form.category" class="form-select">
                <option value="">Nenhuma</option>
                <option value="websites">Websites</option>
                <option value="apps">Aplicativos</option>
                <option value="design">Design</option>
                <option value="marketing">Marketing</option>
                <option value="consultoria">Consultoria</option>
                <option value="outros">Outros</option>
              </select>
            </div>
          </div>

          <!-- Tags -->
          <div class="sidebar-card">
            <h3>Tags</h3>

            <div class="tags-list">
              <span v-for="(tag, index) in form.tags" :key="index" class="tag">
                {{ tag }}
                <button @click="removeTag(index)" class="remove-tag-btn">
                  <i class="fa fa-times"></i>
                </button>
              </span>
            </div>

            <div class="tag-input">
              <Input
                v-model="newTag"
                @keyup.enter="addTag"
                placeholder="Adicionar tag..."
              />
              <Button variant="secondary" size="small" @click="addTag">
                <i class="fa fa-plus"></i>
              </Button>
            </div>
          </div>

          <!-- Additional Info -->
          <div v-if="isEdit" class="sidebar-card">
            <h3>Informações</h3>

            <div class="info-list">
              <div class="info-item">
                <span class="label">Criado em:</span>
                <span class="value">{{ formatDate(product?.created_at) }}</span>
              </div>
              <div class="info-item">
                <span class="label">Atualizado em:</span>
                <span class="value">{{ formatDate(product?.updated_at) }}</span>
              </div>
              <div v-if="product?.proposals_count" class="info-item">
                <span class="label">Usado em propostas:</span>
                <span class="value">{{ product.proposals_count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const props = defineProps({
  product: Object
});

const editor = ref(null);
const imageInput = ref(null);
const galleryInput = ref(null);
const saving = ref(false);
const newTag = ref('');

const isEdit = computed(() => !!props.product);

const form = ref({
  name: props.product?.name || '',
  sku: props.product?.sku || '',
  type: props.product?.type || 'service',
  description: props.product?.description || '',
  detailed_description: props.product?.detailed_description || '',
  price: props.product?.price || 0,
  cost: props.product?.cost || 0,
  allow_discount: props.product?.allow_discount || false,
  max_discount_percent: props.product?.max_discount_percent || 0,
  track_inventory: props.product?.track_inventory || false,
  stock_quantity: props.product?.stock_quantity || 0,
  stock_min: props.product?.stock_min || 0,
  is_active: props.product?.is_active ?? true,
  image: props.product?.image || null,
  gallery: props.product?.gallery || [],
  category: props.product?.category || '',
  tags: props.product?.tags || [],
  features: props.product?.features || []
});

const breadcrumbs = [
  { label: 'Produtos', to: '/products' },
  { label: isEdit.value ? 'Editar' : 'Novo Produto' }
];

const calculateMargin = computed(() => {
  const price = parseFloat(form.value.price) || 0;
  const cost = parseFloat(form.value.cost) || 0;
  if (price === 0) return 0;
  return (((price - cost) / price) * 100).toFixed(2);
});

const calculateProfit = computed(() => {
  const price = parseFloat(form.value.price) || 0;
  const cost = parseFloat(form.value.cost) || 0;
  return price - cost;
});

const calculateMinPrice = computed(() => {
  const price = parseFloat(form.value.price) || 0;
  const discount = parseFloat(form.value.max_discount_percent) || 0;
  return price - (price * discount / 100);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('pt-BR');
};

const execCommand = (command, value = null) => {
  document.execCommand(command, false, value);
  editor.value?.focus();
};

const updateContent = () => {
  if (editor.value) {
    form.value.detailed_description = editor.value.innerHTML;
  }
};

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      form.value.image = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleGalleryUpload = (e) => {
  const files = Array.from(e.target.files);
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (event) => {
      form.value.gallery.push(event.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const removeGalleryImage = (index) => {
  form.value.gallery.splice(index, 1);
};

const addTag = () => {
  if (newTag.value.trim() && !form.value.tags.includes(newTag.value.trim())) {
    form.value.tags.push(newTag.value.trim());
    newTag.value = '';
  }
};

const removeTag = (index) => {
  form.value.tags.splice(index, 1);
};

const addFeature = () => {
  form.value.features.push({ name: '', value: '' });
};

const removeFeature = (index) => {
  form.value.features.splice(index, 1);
};

const saveProduct = () => {
  saving.value = true;

  const url = isEdit.value
    ? `/products/${props.product.id}`
    : '/products';

  const method = isEdit.value ? 'put' : 'post';

  router[method](url, form.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const goBack = () => {
  router.visit('/products');
};
</script>

