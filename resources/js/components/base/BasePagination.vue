<template>
  <nav 
    class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6"
    aria-label="ページネーション"
  >
    <!-- Mobile pagination -->
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        :disabled="currentPage <= 1"
        @click="goToPage(currentPage - 1)"
        :class="mobileButtonClasses(currentPage <= 1)"
      >
        前へ
      </button>
      <span class="text-sm text-gray-700 px-4 py-2">
        {{ currentPage }} / {{ totalPages }}
      </span>
      <button
        :disabled="currentPage >= totalPages"
        @click="goToPage(currentPage + 1)"
        :class="mobileButtonClasses(currentPage >= totalPages)"
      >
        次へ
      </button>
    </div>
    
    <!-- Desktop pagination -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <!-- Results info -->
      <div>
        <p class="text-sm text-gray-700">
          <span class="font-medium">{{ startItem }}</span>
          -
          <span class="font-medium">{{ endItem }}</span>
          件 / 全
          <span class="font-medium">{{ total }}</span>
          件
        </p>
      </div>
      
      <!-- Page numbers -->
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="ページネーション">
          <!-- Previous button -->
          <button
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
            :class="navigationButtonClasses(currentPage <= 1)"
            aria-label="前のページへ"
          >
            <span class="sr-only">前へ</span>
            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
          </button>
          
          <!-- Page numbers -->
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page === '...'"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 cursor-default"
            >
              ...
            </button>
            <button
              v-else
              @click="goToPage(page)"
              :class="pageButtonClasses(page)"
              :aria-label="`ページ ${page} へ`"
              :aria-current="page === currentPage ? 'page' : undefined"
            >
              {{ page }}
            </button>
          </template>
          
          <!-- Next button -->
          <button
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
            :class="navigationButtonClasses(currentPage >= totalPages)"
            aria-label="次のページへ"
          >
            <span class="sr-only">次へ</span>
            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </nav>
      </div>
    </div>
    
    <!-- Page size selector -->
    <div v-if="showPageSizeSelector" class="ml-4">
      <label for="page-size" class="sr-only">ページサイズ</label>
      <select
        id="page-size"
        :value="pageSize"
        @change="handlePageSizeChange"
        class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
      >
        <option v-for="size in pageSizeOptions" :key="size" :value="size">
          {{ size }} 件表示
        </option>
      </select>
    </div>
  </nav>
</template>

<script>
import { computed } from 'vue';

// Chevron Icons
const ChevronLeftIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
  `
};

const ChevronRightIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
  `
};

export default {
  name: 'BasePagination',
  components: {
    ChevronLeftIcon,
    ChevronRightIcon
  },
  props: {
    // Pagination data
    currentPage: {
      type: Number,
      required: true,
      validator: (value) => value >= 1
    },
    
    totalPages: {
      type: Number,
      required: true,
      validator: (value) => value >= 0
    },
    
    total: {
      type: Number,
      required: true,
      validator: (value) => value >= 0
    },
    
    pageSize: {
      type: Number,
      default: 10,
      validator: (value) => value > 0
    },
    
    // Page size selector
    showPageSizeSelector: {
      type: Boolean,
      default: false
    },
    
    pageSizeOptions: {
      type: Array,
      default: () => [10, 20, 50, 100]
    },
    
    // Display options
    maxVisiblePages: {
      type: Number,
      default: 7,
      validator: (value) => value >= 5 && value % 2 === 1
    }
  },
  
  emits: ['update:currentPage', 'update:pageSize', 'page-change', 'page-size-change'],
  
  setup(props, { emit }) {
    // Calculate start and end items
    const startItem = computed(() => {
      if (props.total === 0) return 0;
      return (props.currentPage - 1) * props.pageSize + 1;
    });
    
    const endItem = computed(() => {
      const calculated = props.currentPage * props.pageSize;
      return Math.min(calculated, props.total);
    });
    
    // Generate visible page numbers
    const visiblePages = computed(() => {
      const delta = Math.floor(props.maxVisiblePages / 2);
      const range = [];
      const rangeWithDots = [];
      
      // Calculate range around current page
      let start = Math.max(2, props.currentPage - delta);
      let end = Math.min(props.totalPages - 1, props.currentPage + delta);
      
      // Adjust range if we're near the beginning or end
      if (props.currentPage - delta <= 2) {
        end = Math.min(props.totalPages - 1, props.maxVisiblePages - 1);
      }
      if (props.currentPage + delta >= props.totalPages - 1) {
        start = Math.max(2, props.totalPages - props.maxVisiblePages + 2);
      }
      
      // Add first page
      if (props.totalPages > 0) {
        rangeWithDots.push(1);
      }
      
      // Add ellipsis if needed
      if (start > 2) {
        rangeWithDots.push('...');
      }
      
      // Add middle pages
      for (let i = start; i <= end; i++) {
        rangeWithDots.push(i);
      }
      
      // Add ellipsis if needed
      if (end < props.totalPages - 1) {
        rangeWithDots.push('...');
      }
      
      // Add last page (if different from first)
      if (props.totalPages > 1) {
        rangeWithDots.push(props.totalPages);
      }
      
      // Remove duplicates while preserving order
      const seen = new Set();
      return rangeWithDots.filter(page => {
        if (page === '...' || !seen.has(page)) {
          if (page !== '...') seen.add(page);
          return true;
        }
        return false;
      });
    });
    
    // Style classes
    const baseButtonClasses = 'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-600';
    
    const pageButtonClasses = (page) => {
      const isActive = page === props.currentPage;
      const activeClasses = isActive 
        ? 'z-10 bg-blue-600 text-white focus:ring-blue-600'
        : 'text-gray-900 hover:bg-gray-50';
      
      return `${baseButtonClasses} ${activeClasses}`;
    };
    
    const navigationButtonClasses = (disabled) => {
      const disabledClasses = disabled 
        ? 'text-gray-400 cursor-not-allowed'
        : 'text-gray-400 hover:bg-gray-50 hover:text-gray-500';
      
      return `${baseButtonClasses} ${disabledClasses}`;
    };
    
    const mobileButtonClasses = (disabled) => {
      const disabledClasses = disabled 
        ? 'text-gray-400 cursor-not-allowed'
        : 'text-gray-700 hover:text-gray-500';
      
      return `relative inline-flex items-center px-4 py-2 text-sm font-medium border border-gray-300 rounded-md bg-white ${disabledClasses}`;
    };
    
    // Event handlers
    const goToPage = (page) => {
      if (page < 1 || page > props.totalPages || page === props.currentPage) {
        return;
      }
      
      emit('update:currentPage', page);
      emit('page-change', page);
    };
    
    const handlePageSizeChange = (event) => {
      const newPageSize = parseInt(event.target.value);
      emit('update:pageSize', newPageSize);
      emit('page-size-change', newPageSize);
    };
    
    return {
      startItem,
      endItem,
      visiblePages,
      pageButtonClasses,
      navigationButtonClasses,
      mobileButtonClasses,
      goToPage,
      handlePageSizeChange
    };
  }
};
</script>