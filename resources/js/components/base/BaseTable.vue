<template>
  <div class="base-table-container">
    <!-- Table wrapper for horizontal scroll -->
    <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
      <table class="min-w-full divide-y divide-gray-300">
        <!-- Table Header -->
        <thead class="bg-gray-50">
          <tr>
            <!-- Select all checkbox -->
            <th
              v-if="selectable"
              scope="col"
              class="relative w-12 px-6 sm:w-16 sm:px-8"
            >
              <input
                type="checkbox"
                :checked="isAllSelected"
                :indeterminate="isIndeterminate"
                @change="toggleSelectAll"
                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600 sm:left-6"
              />
            </th>
            
            <!-- Column headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              :class="getHeaderClass(column)"
              @click="handleSort(column)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.title }}</span>
                
                <!-- Sort icons -->
                <div v-if="column.sortable" class="flex flex-col">
                  <svg
                    :class="getSortIconClass(column, 'asc')"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    class="w-3 h-3"
                  >
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                  </svg>
                  <svg
                    :class="getSortIconClass(column, 'desc')"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    class="w-3 h-3 -mt-1"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </th>
            
            <!-- Actions column -->
            <th
              v-if="$slots.actions || hasRowActions"
              scope="col"
              class="relative py-3.5 pl-3 pr-4 sm:pr-6"
            >
              <span class="sr-only">アクション</span>
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-gray-200 bg-white">
          <!-- Empty state -->
          <tr v-if="!data || data.length === 0">
            <td :colspan="totalColumns" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-lg font-medium text-gray-900 mb-1">{{ emptyTitle }}</p>
                <p class="text-sm text-gray-500">{{ emptyMessage }}</p>
              </div>
            </td>
          </tr>

          <!-- Loading state -->
          <tr v-else-if="loading">
            <td :colspan="totalColumns" class="px-6 py-12 text-center">
              <div class="flex items-center justify-center">
                <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <span class="text-sm text-gray-500">読み込み中...</span>
              </div>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-else
            v-for="(item, index) in data"
            :key="getRowKey(item, index)"
            :class="getRowClass(item, index)"
            @click="handleRowClick(item, index)"
          >
            <!-- Select checkbox -->
            <td v-if="selectable" class="relative w-12 px-6 sm:w-16 sm:px-8">
              <input
                type="checkbox"
                :checked="isRowSelected(item)"
                @change="toggleRowSelection(item)"
                @click.stop
                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600 sm:left-6"
              />
            </td>
            
            <!-- Data cells -->
            <td
              v-for="column in columns"
              :key="column.key"
              :class="getCellClass(column)"
            >
              <slot
                :name="`cell(${column.key})`"
                :item="item"
                :value="getCellValue(item, column.key)"
                :index="index"
              >
                {{ formatCellValue(getCellValue(item, column.key), column) }}
              </slot>
            </td>
            
            <!-- Actions -->
            <td
              v-if="$slots.actions || hasRowActions"
              class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
            >
              <slot
                name="actions"
                :item="item"
                :index="index"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination && data && data.length > 0"
      class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
    >
      <div class="flex flex-1 justify-between sm:hidden">
        <BaseButton
          size="sm"
          variant="secondary"
          :disabled="pagination.current_page <= 1"
          @click="previousPage"
        >
          前へ
        </BaseButton>
        <BaseButton
          size="sm"
          variant="secondary"
          :disabled="pagination.current_page >= pagination.last_page"
          @click="nextPage"
        >
          次へ
        </BaseButton>
      </div>
      
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            <span class="font-medium">{{ startItem }}</span>
            から
            <span class="font-medium">{{ endItem }}</span>
            件目を表示（全
            <span class="font-medium">{{ pagination.total }}</span>
            件中）
          </p>
        </div>
        
        <div>
          <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="ページネーション">
            <button
              :disabled="pagination.current_page <= 1"
              @click="previousPage"
              class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span class="sr-only">前のページ</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/>
              </svg>
            </button>
            
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="getPageButtonClass(page)"
            >
              {{ page }}
            </button>
            
            <button
              :disabled="pagination.current_page >= pagination.last_page"
              @click="nextPage"
              class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span class="sr-only">次のページ</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref } from 'vue';
import BaseButton from './BaseButton.vue';

export default {
  name: 'BaseTable',
  components: {
    BaseButton
  },
  props: {
    columns: {
      type: Array,
      required: true
    },
    
    data: {
      type: Array,
      default: () => []
    },
    
    loading: {
      type: Boolean,
      default: false
    },
    
    selectable: {
      type: Boolean,
      default: false
    },
    
    pagination: {
      type: Object,
      default: null
    },
    
    sortBy: {
      type: String,
      default: ''
    },
    
    sortDirection: {
      type: String,
      default: 'asc',
      validator: (value) => ['asc', 'desc'].includes(value)
    },
    
    rowKey: {
      type: String,
      default: 'id'
    },
    
    selectedRows: {
      type: Array,
      default: () => []
    },
    
    clickable: {
      type: Boolean,
      default: false
    },
    
    striped: {
      type: Boolean,
      default: true
    },
    
    emptyTitle: {
      type: String,
      default: 'データがありません'
    },
    
    emptyMessage: {
      type: String,
      default: '表示するデータが見つかりませんでした'
    }
  },
  
  emits: [
    'sort', 
    'page-change', 
    'row-click', 
    'selection-change',
    'select-all',
    'update:selectedRows'
  ],
  
  setup(props, { emit, slots }) {
    const selectedRowsRef = ref([...props.selectedRows]);
    
    // Check if table has row actions
    const hasRowActions = computed(() => {
      return !!slots.actions;
    });
    
    // Calculate total columns
    const totalColumns = computed(() => {
      let count = props.columns.length;
      if (props.selectable) count++;
      if (hasRowActions.value) count++;
      return count;
    });
    
    // Selection management
    const isAllSelected = computed(() => {
      return props.data && props.data.length > 0 && 
             selectedRowsRef.value.length === props.data.length;
    });
    
    const isIndeterminate = computed(() => {
      return selectedRowsRef.value.length > 0 && !isAllSelected.value;
    });
    
    const isRowSelected = (item) => {
      const key = getRowKey(item, -1);
      return selectedRowsRef.value.some(selected => 
        getRowKey(selected, -1) === key
      );
    };
    
    const toggleRowSelection = (item) => {
      const key = getRowKey(item, -1);
      const index = selectedRowsRef.value.findIndex(selected => 
        getRowKey(selected, -1) === key
      );
      
      if (index > -1) {
        selectedRowsRef.value.splice(index, 1);
      } else {
        selectedRowsRef.value.push(item);
      }
      
      emit('update:selectedRows', [...selectedRowsRef.value]);
      emit('selection-change', [...selectedRowsRef.value]);
    };
    
    const toggleSelectAll = () => {
      if (isAllSelected.value) {
        selectedRowsRef.value = [];
      } else {
        selectedRowsRef.value = [...props.data];
      }
      
      emit('update:selectedRows', [...selectedRowsRef.value]);
      emit('select-all', [...selectedRowsRef.value]);
    };
    
    // Pagination
    const startItem = computed(() => {
      if (!props.pagination || props.data.length === 0) return 0;
      return (props.pagination.current_page - 1) * props.pagination.per_page + 1;
    });
    
    const endItem = computed(() => {
      if (!props.pagination || props.data.length === 0) return 0;
      return Math.min(
        props.pagination.current_page * props.pagination.per_page,
        props.pagination.total
      );
    });
    
    const visiblePages = computed(() => {
      if (!props.pagination) return [];
      
      const current = props.pagination.current_page;
      const total = props.pagination.last_page;
      const pages = [];
      
      // Show max 7 pages
      const maxPages = 7;
      let start = Math.max(1, current - Math.floor(maxPages / 2));
      let end = Math.min(total, start + maxPages - 1);
      
      if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    });
    
    // Helper functions
    const getRowKey = (item, index) => {
      return item[props.rowKey] || index;
    };
    
    const getCellValue = (item, key) => {
      return key.split('.').reduce((obj, k) => obj?.[k], item);
    };
    
    const formatCellValue = (value, column) => {
      if (value === null || value === undefined) return '';
      
      if (column.formatter && typeof column.formatter === 'function') {
        return column.formatter(value);
      }
      
      if (column.type === 'date' && value) {
        return new Date(value).toLocaleDateString('ja-JP');
      }
      
      if (column.type === 'number' && typeof value === 'number') {
        return value.toLocaleString('ja-JP');
      }
      
      return value;
    };
    
    // CSS class methods
    const getHeaderClass = (column) => {
      const base = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
      const sortable = column.sortable ? 'cursor-pointer hover:bg-gray-100' : '';
      return `${base} ${sortable}`;
    };
    
    const getCellClass = (column) => {
      const base = 'px-6 py-4 whitespace-nowrap text-sm';
      const alignment = column.align === 'center' ? 'text-center' : 
                       column.align === 'right' ? 'text-right' : 'text-left';
      return `${base} ${alignment}`;
    };
    
    const getRowClass = (item, index) => {
      const base = '';
      const clickable = props.clickable ? 'cursor-pointer hover:bg-gray-50' : '';
      const striped = props.striped && index % 2 === 1 ? 'bg-gray-50' : '';
      return `${base} ${clickable} ${striped}`;
    };
    
    const getSortIconClass = (column, direction) => {
      const base = 'text-gray-400';
      const active = props.sortBy === column.key && props.sortDirection === direction
        ? 'text-blue-600' 
        : base;
      return active;
    };
    
    const getPageButtonClass = (page) => {
      const base = 'relative inline-flex items-center px-4 py-2 text-sm font-semibold';
      const active = page === props.pagination.current_page
        ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
        : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0';
      return `${base} ${active}`;
    };
    
    // Event handlers
    const handleSort = (column) => {
      if (!column.sortable) return;
      
      let direction = 'asc';
      if (props.sortBy === column.key && props.sortDirection === 'asc') {
        direction = 'desc';
      }
      
      emit('sort', { column: column.key, direction });
    };
    
    const handleRowClick = (item, index) => {
      if (props.clickable) {
        emit('row-click', { item, index });
      }
    };
    
    const previousPage = () => {
      if (props.pagination && props.pagination.current_page > 1) {
        emit('page-change', props.pagination.current_page - 1);
      }
    };
    
    const nextPage = () => {
      if (props.pagination && props.pagination.current_page < props.pagination.last_page) {
        emit('page-change', props.pagination.current_page + 1);
      }
    };
    
    const goToPage = (page) => {
      emit('page-change', page);
    };
    
    return {
      hasRowActions,
      totalColumns,
      isAllSelected,
      isIndeterminate,
      startItem,
      endItem,
      visiblePages,
      isRowSelected,
      toggleRowSelection,
      toggleSelectAll,
      getRowKey,
      getCellValue,
      formatCellValue,
      getHeaderClass,
      getCellClass,
      getRowClass,
      getSortIconClass,
      getPageButtonClass,
      handleSort,
      handleRowClick,
      previousPage,
      nextPage,
      goToPage
    };
  }
};
</script>