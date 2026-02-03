<script setup>
import { computed } from 'vue';

const props = defineProps({
  from: {
    type: Number,
    default: 0
  },
  to: {
    type: Number,
    default: 0
  },
  total: {
    type: Number,
    default: 0
  },
  currentPage: {
    type: Number,
    default: 1
  },
  lastPage: {
    type: Number,
    default: 1
  },
});

const emit = defineEmits(['page-change']);

const pages = computed(() => {
  const pageList = [];
  const current = props.currentPage;
  const last = props.lastPage;

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pageList.push(i);
    }
  } else {
    if (current <= 3) {
      pageList.push(1, 2, 3, 4, '...', last);
    } else if (current >= last - 2) {
      pageList.push(1, '...', last - 3, last - 2, last - 1, last);
    } else {
      pageList.push(1, '...', current - 1, current, current + 1, '...', last);
    }
  }

  return pageList;
});

const changePage = (page) => {
  if (page === '...' || page === props.currentPage) return;
  emit('page-change', page);
};

const previousPage = () => {
  if (props.currentPage > 1) {
    emit('page-change', props.currentPage - 1);
  }
};

const nextPage = () => {
  if (props.currentPage < props.lastPage) {
    emit('page-change', props.currentPage + 1);
  }
};
</script>

<template>
  <div class="pagination">
    <div class="pagination__info">
      Mostrando {{ from }} a {{ to }} de {{ total }} registros
    </div>

    <div class="pagination__buttons">
      <button
        class="pagination__btn pagination__btn--nav"
        :disabled="currentPage === 1"
        @click="previousPage"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <button
        v-for="(page, index) in pages"
        :key="index"
        :class="['pagination__btn', { 'pagination__btn--active': page === currentPage }]"
        :disabled="page === '...'"
        @click="changePage(page)"
      >
        {{ page }}
      </button>

      <button
        class="pagination__btn pagination__btn--nav"
        :disabled="currentPage === lastPage"
        @click="nextPage"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<style scoped lang="scss">
.pagination {
  padding: 24px;
  border-top: 2px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;

  &__info {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
  }

  &__buttons {
    display: flex;
    gap: 8px;
  }

  &__btn {
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
      border-color: #FF6B35;
      transform: translateY(-2px);
    }

    &--active {
      background: #FF6B35;
      color: var(--bg-primary);
      border-color: #FF6B35;
    }

    &--nav {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      width: 40px;

      i {
        font-size: 12px;
      }
    }

    &:disabled {
      cursor: not-allowed;
      opacity: 0.5;
      background: var(--bg-secondary);

      &:hover {
        transform: none;
        border-color: var(--border-color);
      }
    }
  }
}

@media (max-width: 768px) {
  .pagination {
    flex-direction: column;
    align-items: flex-start;

    &__buttons {
      width: 100%;
      flex-wrap: wrap;
    }

    &__btn {
      min-width: 36px;
      height: 36px;
    }
  }
}
</style>
