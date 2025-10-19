<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            出席登録
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            日々の出席状況を登録・管理します
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="exportData"
          >
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            エクスポート
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="goToCreate"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            出席入力
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-6">
      <!-- Filters -->
      <BaseCard>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <BaseInput
            type="date"
            v-model="filters.date"
            label="日付"
            @change="applyFilters"
          />
          
          <BaseInput
            type="select"
            v-model="filters.grade"
            label="学年"
            @change="onGradeChange"
          >
            <option value="">全学年</option>
            <option value="1">1年生</option>
            <option value="2">2年生</option>
            <option value="3">3年生</option>
            <option value="4">4年生</option>
            <option value="5">5年生</option>
            <option value="6">6年生</option>
          </BaseInput>
          
          <BaseInput
            type="select"
            v-model="filters.class_id"
            label="クラス"
            @change="applyFilters"
          >
            <option value="">全クラス</option>
            <option
              v-for="cls in filteredClasses"
              :key="cls.id"
              :value="cls.class_id"
            >
              {{ cls.name }}
            </option>
          </BaseInput>
          
          <BaseInput
            type="select"
            v-model="filters.status"
            label="状態"
            @change="applyFilters"
          >
            <option value="">全て</option>
            <option value="present">出席</option>
            <option value="absent">欠席</option>
            <option value="late">遅刻</option>
            <option value="early_leave">早退</option>
          </BaseInput>
        </div>
      </BaseCard>

      <!-- Attendance Statistics -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    出席
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.present }}名
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <XCircleIcon class="h-6 w-6 text-red-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    欠席
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.absent }}名
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-yellow-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    遅刻
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.late }}名
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowRightOnRectangleIcon class="h-6 w-6 text-orange-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    早退
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.early_leave }}名
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Attendance List -->
      <BaseCard>
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">出席記録一覧</h2>
        </template>

        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-sm text-gray-500">読み込み中...</p>
        </div>

        <div v-else-if="attendanceRecords.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">出席記録がありません</h3>
          <p class="mt-1 text-sm text-gray-500">
            新しい出席記録を追加してください
          </p>
          <div class="mt-6">
            <BaseButton
              variant="primary"
              @click="goToCreate"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              出席入力
            </BaseButton>
          </div>
        </div>

        <div v-else class="overflow-hidden">
          <BaseTable
            :columns="columns"
            :data="attendanceRecords"
            :actions="actions"
          />
        </div>
      </BaseCard>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
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

const ArrowDownTrayIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
    </svg>
  `
};

const CheckCircleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const XCircleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const ClockIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const ArrowRightOnRectangleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
    </svg>
  `
};

const CalendarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
    </svg>
  `
};

export default {
  name: 'AttendanceRegistrationIndex',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseTable,
    PlusIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowRightOnRectangleIcon,
    CalendarIcon
  },
  
  setup() {
    const router = useRouter();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const loading = ref(false);
    const attendanceRecords = ref([]);
    const filters = ref({
      date: new Date().toISOString().split('T')[0],
      grade: '',
      class_id: '',
      status: ''
    });
    
    const statistics = ref({
      present: 0,
      absent: 0,
      late: 0,
      early_leave: 0
    });
    
    // Computed
    const classes = computed(() => classStore.classes);
    
    const filteredClasses = computed(() => {
      if (!filters.value.grade) return classes.value;
      return classes.value.filter(c => c.grade.toString() === filters.value.grade);
    });
    
    // Table columns
    const columns = [
      { key: 'date', label: '日付' },
      { key: 'student_name', label: '学生名' },
      { key: 'class_name', label: 'クラス' },
      { key: 'status', label: '状態' },
      { key: 'notes', label: '備考' }
    ];
    
    // Table actions
    const actions = [
      {
        label: '編集',
        onClick: (record) => {
          router.push({ name: 'attendance-registration.edit', params: { id: record.id } });
        }
      },
      {
        label: '削除',
        onClick: async (record) => {
          if (confirm('この出席記録を削除してもよろしいですか？')) {
            // TODO: Implement delete functionality
            notificationStore.addNotification({
              type: 'success',
              title: '削除完了',
              message: '出席記録を削除しました'
            });
          }
        }
      }
    ];
    
    // Methods
    const onGradeChange = () => {
      filters.value.class_id = '';
      applyFilters();
    };
    
    const applyFilters = async () => {
      await loadAttendanceRecords();
    };
    
    const goToCreate = () => {
      router.push({ name: 'attendance-registration.create' });
    };
    
    const exportData = () => {
      // TODO: Implement export functionality
      notificationStore.addNotification({
        type: 'info',
        title: 'エクスポート',
        message: 'エクスポート機能は開発中です'
      });
    };
    
    const loadAttendanceRecords = async () => {
      loading.value = true;
      try {
        // Build query parameters
        const params = new URLSearchParams();
        if (filters.value.date) params.append('date', filters.value.date);
        if (filters.value.grade) params.append('grade', filters.value.grade);
        if (filters.value.class_id) params.append('class_id', filters.value.class_id);
        if (filters.value.status) params.append('status', filters.value.status);
        
        // Fetch attendance records from API
        const response = await fetch(`/api/v1/attendance-records?${params.toString()}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('出席記録の取得に失敗しました');
        }
        
        const result = await response.json();
        attendanceRecords.value = result.data || [];
        
        // Update statistics
        if (result.statistics) {
          statistics.value = result.statistics;
        }
      } catch (error) {
        console.error('Failed to load attendance records:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '出席記録の取得に失敗しました'
        });
      } finally {
        loading.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      try {
        await classStore.fetchClasses();
        await loadAttendanceRecords();
      } catch (error) {
        console.error('Failed to initialize:', error);
      }
    });
    
    return {
      loading,
      attendanceRecords,
      filters,
      statistics,
      classes,
      filteredClasses,
      columns,
      actions,
      onGradeChange,
      applyFilters,
      goToCreate,
      exportData
    };
  }
};
</script>
