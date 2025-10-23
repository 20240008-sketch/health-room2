<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            学生管理
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            学生情報の一覧・検索・管理
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="showImportModal = true"
          >
            <UploadIcon class="h-4 w-4 mr-2" />
            CSVインポート
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="$router.push('/students/create')"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            学生を追加
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-6">
      <!-- Search and Filters -->
      <BaseCard>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <!-- Search Input -->
            <div class="sm:col-span-2">
              <BaseInput
                v-model="searchQuery"
                placeholder="学生名、学生番号で検索..."
                @keyup.enter="handleSearch"
              >
                <template #prefix>
                  <SearchIcon class="h-5 w-5 text-gray-400" />
                </template>
              </BaseInput>
            </div>
            
            <!-- Class Filter -->
            <div>
              <BaseSelect
                v-model="selectedClass"
                label="クラス"
              >
                <option value="">すべてのクラス</option>
                <option 
                  v-for="classItem in classes" 
                  :key="classItem.class_id"
                  :value="classItem.class_id"
                >
                  {{ classItem.class_name }}
                </option>
              </BaseSelect>
            </div>

            <!-- Grade Filter -->
            <div>
              <BaseSelect
                v-model="selectedGrade"
                label="学年"
              >
                <option value="">すべての学年</option>
                <option value="1">1年生</option>
                <option value="2">2年生</option>
                <option value="3">3年生</option>
              </BaseSelect>
            </div>
            
            <!-- Sort Filter -->
            <div>
              <BaseSelect
                v-model="sortBy"
                label="並び順"
              >
                <option value="student_number">学生番号順</option>
                <option value="name">名前順</option>
                <option value="created_at">登録日順</option>
              </BaseSelect>
            </div>

            <!-- Search Button -->
            <div class="flex items-end space-x-2">
              <BaseButton
                variant="primary"
                @click="handleSearch"
                class="flex-1"
              >
                <SearchIcon class="h-4 w-4 mr-2" />
                検索
              </BaseButton>
              <BaseButton
                variant="secondary"
                @click="handleReset"
                class="flex-shrink-0"
              >
                リセット
              </BaseButton>
            </div>
          </div>
          
          <!-- Active Filters -->
          <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
            <BaseBadge
              v-if="searchQuery"
              variant="info"
              closable
              @close="searchQuery = ''; handleSearch()"
            >
              検索: {{ searchQuery }}
            </BaseBadge>
            <BaseBadge
              v-if="selectedClass"
              variant="secondary"
              closable
              @close="selectedClass = ''; handleSearch()"
            >
              クラス: {{ getClassName(selectedClass) }}
            </BaseBadge>
            <BaseBadge
              v-if="selectedGrade"
              variant="secondary"
              closable
              @close="selectedGrade = ''; handleSearch()"
            >
              学年: {{ selectedGrade }}年生
            </BaseBadge>
          </div>
        </div>
      </BaseCard>

      <!-- Students Table -->
      <BaseCard>
        <div class="overflow-hidden">
          <BaseTable
            :columns="tableColumns"
            :data="students"
            :loading="isLoading"
            :sortable="true"
            :selectable="true"
            v-model:selected="selectedStudents"
            @sort="handleSort"
            @row-click="handleRowClick"
          >
            <!-- Student ID Column -->
            <template #cell(student_id)="{ item }">
              <div class="text-sm font-mono text-gray-900">
                {{ item.student_id || '-' }}
              </div>
            </template>
            
            <!-- Student Info Column -->
            <template #cell(student_info)="{ item }">
              <div class="space-y-1">
                <div class="font-medium text-gray-900">
                  {{ item.name || '不明' }}
                </div>
                <div class="text-xs text-gray-500">
                  出席番号: {{ item.student_number || '未設定' }}
                </div>
              </div>
            </template>
            
            <!-- Class & Grade Column -->
            <template #cell(class_grade)="{ item }">
              <div class="text-sm text-gray-600">
                <div>{{ getClassName(item.class_id) || '未設定' }}</div>
                <div class="text-xs text-gray-500">{{ getClassGrade(item.class_id) || '?' }}年生</div>
              </div>
            </template>
            
            <!-- Birth Date Column -->
            <template #cell(birth_date)="{ item }">
              <div class="text-sm text-gray-900">
                {{ item.birth_date ? formatDate(item.birth_date) : '-' }}
              </div>
            </template>
            
            <!-- Gender Column -->
            <template #cell(gender)="{ item }">
              <div class="text-sm text-gray-900">
                {{ item.gender === 'male' ? '男' : item.gender === 'female' ? '女' : '-' }}
              </div>
            </template>
            
            <!-- Health Records Count -->
            <template #cell(health_records_count)="{ item }">
              <div class="flex items-center">
                <HeartIcon class="h-4 w-4 text-red-500 mr-1" />
                <span class="text-sm text-gray-900">
                  {{ item.health_records_count || 0 }}件
                </span>
              </div>
            </template>
            
            <!-- Last Record Date -->
            <template #cell(last_record_date)="{ item }">
              <span
                v-if="item.latest_health_record?.measured_date"
                class="text-sm text-gray-900"
              >
                {{ formatDate(item.latest_health_record.measured_date) }}
              </span>
              <span v-else class="text-sm text-gray-400">
                記録なし
              </span>
            </template>
            
            <!-- Actions Column -->
            <template #actions="{ item }">
              <div class="flex space-x-2">
                <BaseButton
                  variant="secondary"
                  size="sm"
                  @click.stop="$router.push(`/students/${item.id}`)"
                >
                  詳細
                </BaseButton>
                <BaseButton
                  variant="primary"
                  size="sm"
                  @click.stop="$router.push(`/students/${item.id}/edit`)"
                >
                  編集
                </BaseButton>
              </div>
            </template>
          </BaseTable>
          
          <!-- Bulk Actions -->
          <div
            v-if="selectedStudents.length > 0"
            class="bg-gray-50 px-6 py-3 border-t border-gray-200"
          >
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">
                {{ selectedStudents.length }}名選択中
              </span>
              <div class="flex space-x-2">
                <BaseButton
                  variant="secondary"
                  size="sm"
                  @click="bulkExport"
                >
                  <DownloadIcon class="h-4 w-4 mr-1" />
                  エクスポート
                </BaseButton>
                <BaseButton
                  variant="danger"
                  size="sm"
                  @click="confirmBulkDelete"
                >
                  <TrashIcon class="h-4 w-4 mr-1" />
                  削除
                </BaseButton>
              </div>
            </div>
          </div>
          
          <!-- Pagination -->
          <div class="bg-white px-6 py-3 border-t border-gray-200">
            <BasePagination
              :current-page="pagination.currentPage"
              :total-pages="pagination.totalPages"
              :total="pagination.total"
              :page-size="pagination.pageSize"
              :show-page-size-selector="true"
              @page-change="handlePageChange"
              @page-size-change="handlePageSizeChange"
            />
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Import Modal -->
    <BaseModal
      v-model="showImportModal"
      title="学生データのCSVインポート"
      size="lg"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            CSVファイルを選択
          </label>
          <input
            ref="fileInput"
            type="file"
            accept=".csv"
            @change="handleFileSelect"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
        </div>
        <div class="text-sm text-gray-500">
          <p>CSVファイルには以下の列が必要です：</p>
          <ul class="mt-1 list-disc list-inside space-y-1">
            <li>学生番号 (student_number)</li>
            <li>名前 (name)</li>
            <li>生年月日 (birth_date)</li>
            <li>性別 (gender)</li>
            <li>クラスID (class_id)</li>
          </ul>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="showImportModal = false"
          >
            キャンセル
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="handleImport"
            :loading="isImporting"
            :disabled="!selectedFile"
          >
            インポート実行
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useStudentStore } from '@/stores/student.js';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseSelect,
  BaseButton,
  BaseBadge,
  BaseTable,
  BasePagination,
  BaseModal
} from '@/components/index.js';

// Icons
const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
  `
};

const SearchIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
  `
};

const UploadIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
    </svg>
  `
};

const UserIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
  `
};

const HeartIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
    </svg>
  `
};

const DownloadIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
  `
};

const TrashIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
  `
};

export default {
  name: 'StudentIndex',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseSelect,
    BaseButton,
    BaseBadge,
    BaseTable,
    BasePagination,
    BaseModal,
    PlusIcon,
    SearchIcon,
    UploadIcon,
    UserIcon,
    HeartIcon,
    DownloadIcon,
    TrashIcon
  },
  
  setup() {
    const router = useRouter();
    const studentStore = useStudentStore();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const searchQuery = ref('');
    const selectedClass = ref('');
    const selectedGrade = ref('');
    const sortBy = ref('student_number');
    const selectedStudents = ref([]);
    const showImportModal = ref(false);
    const selectedFile = ref(null);
    const fileInput = ref(null);
    const isImporting = ref(false);
    
    // Computed
    const students = computed(() => studentStore.students);
    const classes = computed(() => classStore.classes);
    const isLoading = computed(() => studentStore.isLoading);
    const pagination = computed(() => studentStore.pagination);
    
    const hasActiveFilters = computed(() => {
      return searchQuery.value || selectedClass.value || selectedGrade.value;
    });
    
    // Table columns
    const tableColumns = [
      {
        key: 'student_id',
        title: '学籍番号',
        label: '学籍番号',
        sortable: true,
        width: '120px'
      },
      {
        key: 'student_info',
        title: '学生名・出席番号',
        label: '学生名・出席番号',
        sortable: true,
        width: '200px'
      },
      {
        key: 'class_grade',
        title: 'クラス・学年',
        label: 'クラス・学年',
        sortable: false,
        width: '120px'
      },
      {
        key: 'birth_date',
        title: '生年月日',
        label: '生年月日',
        sortable: true,
        width: '120px'
      },
      {
        key: 'gender',
        title: '性別',
        label: '性別',
        sortable: false,
        width: '80px'
      },
      {
        key: 'health_records_count',
        title: '健康記録',
        label: '健康記録',
        sortable: true,
        width: '100px'
      },
      {
        key: 'last_record_date',
        title: '最新記録日',
        label: '最新記録日',
        sortable: true,
        width: '120px'
      },
      {
        key: 'actions',
        title: '操作',
        label: '操作',
        sortable: false,
        width: '140px'
      }
    ];
    
    // Methods
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('ja-JP');
    };
    
    const getClassName = (classId) => {
      const classItem = classes.value.find(c => c.class_id === classId);
      return classItem ? classItem.class_name : '';
    };
    
    const getClassGrade = (classId) => {
      const classItem = classes.value.find(c => c.class_id === classId);
      return classItem ? classItem.grade : null;
    };
    
    const handleSearch = async () => {
      console.log('Search params:', {
        search: searchQuery.value,
        class_id: selectedClass.value,
        grade_id: selectedGrade.value,
        sort: sortBy.value
      }); // Debug log
      await studentStore.fetchStudents({
        search: searchQuery.value,
        class_id: selectedClass.value,
        grade_id: selectedGrade.value,
        sort: sortBy.value,
        page: 1
      });
    };
    
    const handleReset = async () => {
      searchQuery.value = '';
      selectedClass.value = '';
      selectedGrade.value = '';
      sortBy.value = 'student_number';
      await handleSearch();
    };
    
    const handleSort = async (column, direction) => {
      await studentStore.fetchStudents({
        search: searchQuery.value,
        class_id: selectedClass.value,
        grade_id: selectedGrade.value,
        sort: direction === 'desc' ? `-${column}` : column,
        page: pagination.value.currentPage
      });
    };
    
    const handleRowClick = (student) => {
      router.push(`/students/${student.id}`);
    };
    
    const handlePageChange = async (page) => {
      await studentStore.fetchStudents({
        search: searchQuery.value,
        class_id: selectedClass.value,
        grade_id: selectedGrade.value,
        sort: sortBy.value,
        page: page
      });
    };
    
    const handlePageSizeChange = async (pageSize) => {
      await studentStore.fetchStudents({
        search: searchQuery.value,
        class_id: selectedClass.value,
        grade_id: selectedGrade.value,
        sort: sortBy.value,
        page: 1,
        per_page: pageSize
      });
    };
    
    const handleFileSelect = (event) => {
      selectedFile.value = event.target.files[0];
    };
    
    const handleImport = async () => {
      if (!selectedFile.value) return;
      
      isImporting.value = true;
      
      try {
        await studentStore.importFromCsv(selectedFile.value);
        showImportModal.value = false;
        selectedFile.value = null;
        fileInput.value.value = '';
        
        notificationStore.addNotification({
          type: 'success',
          title: 'インポート完了',
          message: '学生データのインポートが完了しました'
        });
        
        await handleSearch();
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'インポートエラー',
          message: 'データのインポートに失敗しました'
        });
      } finally {
        isImporting.value = false;
      }
    };
    
    const bulkExport = async () => {
      try {
        await studentStore.exportStudents(selectedStudents.value.map(s => s.id));
        notificationStore.addNotification({
          type: 'success',
          title: 'エクスポート完了',
          message: '選択した学生データをエクスポートしました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'エクスポートエラー',
          message: 'データのエクスポートに失敗しました'
        });
      }
    };
    
    const confirmBulkDelete = () => {
      if (confirm(`選択した${selectedStudents.value.length}名の学生を削除しますか？`)) {
        bulkDelete();
      }
    };
    
    const bulkDelete = async () => {
      try {
        await studentStore.deleteStudents(selectedStudents.value.map(s => s.id));
        selectedStudents.value = [];
        
        notificationStore.addNotification({
          type: 'success',
          title: '削除完了',
          message: '選択した学生を削除しました'
        });
        
        await handleSearch();
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: '削除エラー',
          message: '学生の削除に失敗しました'
        });
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      await Promise.all([
        classStore.fetchClasses(),
        handleSearch()
      ]);
    });
    
    // Watchers
    watch(searchQuery, () => {
      // Debounce search
      setTimeout(() => {
        if (searchQuery.value.length === 0 || searchQuery.value.length >= 2) {
          handleSearch();
        }
      }, 300);
    });
    
    // Watch for filter changes
    watch([selectedClass, selectedGrade, sortBy], () => {
      handleSearch();
    });
    
    return {
      // State
      searchQuery,
      selectedClass,
      selectedGrade,
      sortBy,
      selectedStudents,
      showImportModal,
      selectedFile,
      fileInput,
      isImporting,
      
      // Computed
      students,
      classes,
      isLoading,
      pagination,
      hasActiveFilters,
      tableColumns,
      
      // Methods
      formatDate,
      getClassName,
      getClassGrade,
      handleSearch,
      handleReset,
      handleSort,
      handleRowClick,
      handlePageChange,
      handlePageSizeChange,
      handleFileSelect,
      handleImport,
      bulkExport,
      confirmBulkDelete
    };
  }
};
</script>