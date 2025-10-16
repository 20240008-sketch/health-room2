<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <span class="text-sm font-medium text-gray-500">クラス管理</span>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            クラス一覧
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            学校のクラス情報を管理します
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="exportToPDF"
          >
            <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
            PDF出力
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="$router.push('/classes/create')"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            新しいクラス
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Filter Bar -->
    <BaseCard class="mb-6">
      <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-900">検索・フィルタ</h3>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Search -->
          <div>
            <BaseInput
              v-model="searchQuery"
              placeholder="クラス名で検索..."
              @input="handleSearch"
            >
              <template #prepend>
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
              </template>
            </BaseInput>
          </div>

          <!-- Grade Filter -->
          <div>
            <BaseInput
              type="select"
              v-model="filters.grade"
              placeholder="学年を選択"
              @change="applyFilters"
            >
              <option value="">すべての学年</option>
              <option value="1">1年</option>
              <option value="2">2年</option>
              <option value="3">3年</option>
            </BaseInput>
          </div>

          <!-- Academic Year Filter -->
          <div>
            <BaseInput
              type="select"
              v-model="filters.academic_year"
              placeholder="年度を選択"
              @change="applyFilters"
            >
              <option value="">すべての年度</option>
              <option
                v-for="year in availableAcademicYears"
                :key="year"
                :value="year"
              >
                {{ year }}年度
              </option>
            </BaseInput>
          </div>

          <!-- Reset Button -->
          <div class="flex items-end">
            <BaseButton
              variant="secondary"
              size="sm"
              @click="resetFilters"
              class="w-full"
            >
              リセット
            </BaseButton>
          </div>
        </div>
      </div>
    </BaseCard>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-6">
      <BaseCard>
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <AcademicCapIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  総クラス数
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.totalClasses }}クラス
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  総学生数
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.totalStudents }}人
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CalculatorIcon class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  平均クラス人数
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.averageClassSize }}人
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Classes List -->
    <BaseCard>
      <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-medium text-gray-900">
            {{ filteredClasses.length }}件のクラス
          </h3>
          <div class="flex items-center space-x-2">
            <!-- View Toggle -->
            <div class="flex bg-gray-100 rounded-lg p-1">
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                  viewMode === 'grid'
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-900'
                ]"
              >
                <ViewColumnsIcon class="h-4 w-4" />
              </button>
              <button
                @click="viewMode = 'list'"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                  viewMode === 'list'
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-900'
                ]"
              >
                <Bars3Icon class="h-4 w-4" />
              </button>
            </div>

            <!-- Sort -->
            <BaseInput
              type="select"
              v-model="sortBy"
              @change="applySorting"
              class="w-40"
            >
              <option value="name">名前順</option>
              <option value="grade">学年順</option>
              <option value="student_count">学生数順</option>
              <option value="created_at">作成日順</option>
            </BaseInput>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <BaseSpinner size="lg" text="読み込み中..." />
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredClasses.length === 0"
        class="text-center py-12"
      >
        <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          クラスが見つかりません
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasActiveFilters ? '検索条件を変更してください' : '最初のクラスを作成しましょう' }}
        </p>
        <div class="mt-6" v-if="!hasActiveFilters">
          <BaseButton
            variant="primary"
            @click="$router.push('/classes/create')"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            新しいクラス
          </BaseButton>
        </div>
      </div>

      <!-- Grid View -->
      <div
        v-else-if="viewMode === 'grid'"
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 p-6"
      >
        <div
          v-for="schoolClass in filteredClasses"
          :key="schoolClass.id"
          class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          @click="$router.push(`/classes/${schoolClass.id}`)"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <AcademicCapIcon class="h-8 w-8 text-blue-600" />
                </div>
                <div class="ml-3">
                  <h3 class="text-lg font-medium text-gray-900">
                    {{ schoolClass.class_name }}
                  </h3>
                  <p class="text-sm text-gray-500">
                    {{ schoolClass.grade }}年{{ schoolClass.kumi }}組
                  </p>
                </div>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <div class="text-2xl font-bold text-gray-900">
                  {{ schoolClass.students_count || 0 }}
                </div>
                <div class="text-sm text-gray-500">学生数</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-gray-900">
                  {{ schoolClass.year }}
                </div>
                <div class="text-sm text-gray-500">年度</div>
              </div>
            </div>

            <!-- Teacher Info -->
            <div v-if="schoolClass.homeroom_teacher" class="mb-4">
              <p class="text-sm text-gray-500">担任教師</p>
              <p class="font-medium text-gray-900">
                {{ schoolClass.homeroom_teacher }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-2">
              <BaseButton
                size="sm"
                variant="secondary"
                @click.stop="$router.push(`/classes/${schoolClass.id}/edit`)"
              >
                編集
              </BaseButton>
              <BaseButton
                size="sm"
                variant="primary"
                @click.stop="$router.push(`/classes/${schoolClass.id}`)"
              >
                詳細
              </BaseButton>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="overflow-hidden">
        <BaseTable
          :columns="tableColumns"
          :data="filteredClasses"
          :loading="isLoading"
          @row-click="(row) => $router.push(`/classes/${row.id}`)"
        >
          <template #name="{ row }">
            <div class="flex items-center">
              <AcademicCapIcon class="h-5 w-5 text-blue-600 mr-2" />
              <div>
                <div class="font-medium text-gray-900">{{ row.class_name }}</div>
                <div class="text-sm text-gray-500">{{ row.grade }}年{{ row.kumi }}組</div>
              </div>
            </div>
          </template>

          <template #students_count="{ row }">
            <div class="text-center">
              <div class="text-lg font-semibold text-gray-900">
                {{ row.students_count || 0 }}
              </div>
              <div class="text-xs text-gray-500">人</div>
            </div>
          </template>

          <template #homeroom_teacher="{ row }">
            <div v-if="row.homeroom_teacher" class="font-medium text-gray-900">
              {{ row.homeroom_teacher }}
            </div>
            <div v-else class="text-sm text-gray-400">
              未設定
            </div>
          </template>

          <template #actions="{ row }">
            <div class="flex justify-end space-x-2">
              <BaseButton
                size="sm"
                variant="secondary"
                @click.stop="$router.push(`/classes/${row.id}/edit`)"
              >
                編集
              </BaseButton>
              <BaseButton
                size="sm"
                variant="primary"
                @click.stop="$router.push(`/classes/${row.id}`)"
              >
                詳細
              </BaseButton>
            </div>
          </template>
        </BaseTable>
      </div>
    </BaseCard>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseSpinner,
  BaseTable
} from '@/components/index.js';

// Icons
const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
  `
};

const DocumentArrowDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
    </svg>
  `
};

const MagnifyingGlassIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z"/>
    </svg>
  `
};

const AcademicCapIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 14 6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
    </svg>
  `
};

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const CalculatorIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
    </svg>
  `
};

const ViewColumnsIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.5v15m6-15v15m-13.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/>
    </svg>
  `
};

const Bars3Icon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
    </svg>
  `
};

export default {
  name: 'ClassIndex',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseSpinner,
    BaseTable,
    PlusIcon,
    MagnifyingGlassIcon,
    AcademicCapIcon,
    UsersIcon,
    CalculatorIcon,
    ViewColumnsIcon,
    Bars3Icon
  },
  
  setup() {
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const searchQuery = ref('');
    const sortBy = ref('name');
    const viewMode = ref('grid'); // 'grid' or 'list'
    
    const filters = reactive({
      grade: '',
      academic_year: ''
    });
    
    // Computed
    const classes = computed(() => classStore.classes);
    
    const availableAcademicYears = computed(() => {
      const years = classes.value.map(c => c.year);
      return [...new Set(years)].sort((a, b) => b - a);
    });
    
    const filteredClasses = computed(() => {
      let result = [...classes.value];
      
      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(c =>
          c.class_name.toLowerCase().includes(query) ||
          c.homeroom_teacher?.toLowerCase().includes(query)
        );
      }
      
      // Grade filter
      if (filters.grade) {
        result = result.filter(c => c.grade.toString() === filters.grade);
      }
      
      // Academic year filter
      if (filters.academic_year) {
        result = result.filter(c => c.year.toString() === filters.academic_year);
      }
      
      // Sorting
      result.sort((a, b) => {
        switch (sortBy.value) {
          case 'name':
            return a.class_name.localeCompare(b.class_name, 'ja');
          case 'grade':
            return a.grade - b.grade || a.kumi - b.kumi;
          case 'student_count':
            return (b.students_count || 0) - (a.students_count || 0);
          case 'created_at':
            return new Date(b.created_at) - new Date(a.created_at);
          default:
            return 0;
        }
      });
      
      return result;
    });
    
    const stats = computed(() => {
      const totalClasses = classes.value.length;
      const totalStudents = classes.value.reduce((sum, c) => sum + (c.students_count || 0), 0);
      const averageClassSize = totalClasses > 0 ? Math.round(totalStudents / totalClasses) : 0;
      
      return {
        totalClasses,
        totalStudents,
        averageClassSize
      };
    });
    
    const hasActiveFilters = computed(() => {
      return searchQuery.value || filters.grade || filters.academic_year;
    });
    
    const tableColumns = [
      { key: 'name', label: 'クラス名', sortable: true },
      { key: 'academic_year', label: '年度', sortable: true, width: '100px' },
      { key: 'students_count', label: '学生数', sortable: true, width: '100px' },
      { key: 'homeroom_teacher', label: '担任教師', sortable: true },
      { key: 'actions', label: '', width: '140px' }
    ];
    
    // Methods
    const handleSearch = () => {
      // Reactive search, no need for additional logic
    };
    
    const applyFilters = () => {
      // Reactive filters, no need for additional logic
    };
    
    const applySorting = () => {
      // Reactive sorting, no need for additional logic
    };
    
    const resetFilters = () => {
      searchQuery.value = '';
      filters.grade = '';
      filters.academic_year = '';
      sortBy.value = 'name';
    };

    const exportToPDF = async () => {
      try {
        notificationStore.addNotification({
          type: 'info',
          title: 'PDF出力中',
          message: 'クラスリストのPDFを生成しています...'
        });

        const response = await fetch('/api/v1/classes/export-pdf', {
          method: 'GET',
        });

        if (!response.ok) {
          throw new Error('PDF生成に失敗しました');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `クラス一覧_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        notificationStore.addNotification({
          type: 'success',
          title: 'PDF出力完了',
          message: 'クラスリストのPDFをダウンロードしました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'PDF出力エラー',
          message: error.message || 'PDFの生成に失敗しました'
        });
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      try {
        await classStore.fetchClasses();
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: 'クラス情報の取得に失敗しました'
        });
      } finally {
        isLoading.value = false;
      }
    });
    
    return {
      isLoading,
      searchQuery,
      sortBy,
      viewMode,
      filters,
      filteredClasses,
      stats,
      hasActiveFilters,
      tableColumns,
      availableAcademicYears,
      handleSearch,
      applyFilters,
      applySorting,
      resetFilters,
      exportToPDF
    };
  }
};
</script>