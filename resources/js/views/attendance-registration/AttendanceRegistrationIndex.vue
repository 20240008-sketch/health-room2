<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            {{ pageTitle }}
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ pageDescription }}
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
            {{ createButtonText }}
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
            type="text"
            v-model="filters.search"
            label="学生名検索"
            placeholder="学生名、出席番号..."
            @input="applyFilters"
          />
          
          <!-- Date Selection (Scroll Type) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              日付
            </label>
            <div class="grid grid-cols-3 gap-1">
              <select
                v-model="dateComponents.year"
                @change="updateDateFilter"
                class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
              </select>
              <select
                v-model="dateComponents.month"
                @change="updateDateFilter"
                class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
              </select>
              <select
                v-model="dateComponents.day"
                @change="updateDateFilter"
                class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="d in daysInMonth" :key="d" :value="d">{{ d }}日</option>
              </select>
            </div>
          </div>
          
          <BaseInput
            type="select"
            v-model="filters.class_id"
            label="クラス"
            @change="applyFilters"
          >
            <option value="">全クラス</option>
            <option
              v-for="cls in classes"
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

      <!-- Attendance Statistics (Only for attendance records) -->
      <div v-if="recordType === 'attendance'" class="grid grid-cols-1 gap-5 sm:grid-cols-4">
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
          <h2 class="text-lg font-medium text-gray-900">{{ recordListTitle }}</h2>
        </template>

        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-sm text-gray-500">読み込み中...</p>
        </div>

        <div v-else-if="attendanceRecords.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">{{ emptyMessage }}</h3>
          <p class="mt-1 text-sm text-gray-500">
            新しい{{ recordType === 'nursing' ? '保健室記録' : '出席記録' }}を追加してください
          </p>
          <div class="mt-6">
            <BaseButton
              variant="primary"
              @click="goToCreate"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              {{ createButtonText }}
            </BaseButton>
          </div>
        </div>

        <div v-else class="overflow-hidden">
          <BaseTable
            :columns="columns"
            :data="attendanceRecords"
          >
            <template #cell(category)="{ item }">
              <span>{{ getCategoryLabel(item.category) }}</span>
            </template>
            <template #cell(type_detail)="{ item }">
              <span>{{ getTypeDetailLabel(item.type_detail) }}</span>
            </template>
            <template #cell(occurrence_time)="{ item }">
              <span>{{ getOccurrenceTimeLabel(item.occurrence_time) }}</span>
            </template>
            <template #actions="{ item }">
              <div class="flex space-x-2">
                <BaseButton
                  size="sm"
                  variant="secondary"
                  @click.stop="viewDetails(item)"
                >
                  詳細
                </BaseButton>
                <BaseButton
                  size="sm"
                  variant="secondary"
                  @click.stop="router.push({ name: 'attendance-registration.edit', params: { id: item.id } })"
                >
                  編集
                </BaseButton>
                <BaseButton
                  size="sm"
                  variant="danger"
                  @click.stop="handleDelete(item)"
                >
                  削除
                </BaseButton>
              </div>
            </template>
          </BaseTable>
        </div>
      </BaseCard>

      <!-- Detail Modal -->
      <BaseModal
        :show="showDetailModal"
        @close="closeDetailModal"
        title="来室記録詳細"
        size="lg"
      >
        <div v-if="selectedRecord" class="space-y-4">
          <!-- Time and Student Info -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">基本情報</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div>
                <span class="text-gray-600">来室時間:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.time || '-' }}</span>
              </div>
              <div>
                <span class="text-gray-600">日付:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.date || '-' }}</span>
              </div>
              <div>
                <span class="text-gray-600">学年:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.grade || '-' }}年</span>
              </div>
              <div>
                <span class="text-gray-600">クラス:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.class_name || '-' }}</span>
              </div>
              <div>
                <span class="text-gray-600">出席番号:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.student_number || '-' }}番</span>
              </div>
              <div>
                <span class="text-gray-600">氏名:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.student_name || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Category and Type -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">分類・種別</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div>
                <span class="text-gray-600">分類:</span>
                <span class="ml-2 font-medium">{{ getCategoryLabel(selectedRecord.category) }}</span>
              </div>
              <div v-if="selectedRecord.category !== 'absence' && selectedRecord.category !== 'late' && selectedRecord.type_detail">
                <span class="text-gray-600">種別:</span>
                <span class="ml-2 font-medium">{{ getTypeDetailLabel(selectedRecord.type_detail) }}</span>
              </div>
              <div v-if="(selectedRecord.category === 'absence' || selectedRecord.category === 'late') && selectedRecord.absence_reason">
                <span class="text-gray-600">原因・理由:</span>
                <span class="ml-2 font-medium">{{ getAbsenceReasonLabel(selectedRecord.absence_reason) }}</span>
              </div>
            </div>
          </div>

          <!-- Occurrence Details -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">発生状況</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="text-gray-600">発生時:</span>
                <span class="ml-2 font-medium">{{ getOccurrenceTimeLabel(selectedRecord.occurrence_time) }}</span>
              </div>
              <div v-if="selectedRecord.subject">
                <span class="text-gray-600">教科:</span>
                <span class="ml-2 font-medium">{{ getSubjectLabel(selectedRecord.subject) }}</span>
              </div>
              <div v-if="selectedRecord.club">
                <span class="text-gray-600">部活:</span>
                <span class="ml-2 font-medium">{{ getClubLabel(selectedRecord.club) }}</span>
              </div>
              <div v-if="selectedRecord.event">
                <span class="text-gray-600">行事:</span>
                <span class="ml-2 font-medium">{{ getEventLabel(selectedRecord.event) }}</span>
              </div>
            </div>
          </div>

          <!-- Internal Medicine Details (if category is internal) -->
          <div v-if="selectedRecord.category === 'internal'" class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">内科関連情報</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div v-if="selectedRecord.breakfast">
                <span class="text-gray-600">朝食:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.breakfast || '-' }}</span>
              </div>
              <div v-if="selectedRecord.bowel_movement">
                <span class="text-gray-600">便通:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.bowel_movement || '-' }}</span>
              </div>
              <div v-if="selectedRecord.treatment" class="col-span-2">
                <span class="text-gray-600">処置:</span>
                <span class="ml-2 font-medium">{{ selectedRecord.treatment || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Surgical Details (if category is surgical) -->
          <div v-if="selectedRecord.category === 'surgical'" class="bg-red-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">外科関連情報</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div v-if="selectedRecord.injury_location">
                <span class="text-gray-600">怪我の部位:</span>
                <span class="ml-2 font-medium">{{ getInjuryLocationLabel(selectedRecord.injury_location) }}</span>
              </div>
              <div v-if="selectedRecord.injury_place">
                <span class="text-gray-600">発生場所:</span>
                <span class="ml-2 font-medium">{{ getInjuryPlaceLabel(selectedRecord.injury_place) }}</span>
              </div>
              <div v-if="selectedRecord.surgical_treatment" class="col-span-2">
                <span class="text-gray-600">処置:</span>
                <span class="ml-2 font-medium">{{ getSurgicalTreatmentLabel(selectedRecord.surgical_treatment) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="selectedRecord.treatment_notes" class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">備考・原因・その他</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ selectedRecord.treatment_notes }}</p>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-end space-x-3">
            <BaseButton
              variant="secondary"
              @click="closeDetailModal"
            >
              閉じる
            </BaseButton>
            <BaseButton
              variant="primary"
              @click="router.push({ name: 'attendance-registration.edit', params: { id: selectedRecord.id } }); closeDetailModal();"
            >
              編集
            </BaseButton>
          </div>
        </template>
      </BaseModal>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import axios from 'axios';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseTable,
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
    BaseModal,
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
    
    // Get record type from route - use computed to make it reactive
    const recordType = computed(() => router.currentRoute.value.meta.recordType || 'attendance');
    
    // State
    const loading = ref(false);
    const attendanceRecords = ref([]);
    const showDetailModal = ref(false);
    const selectedRecord = ref(null);
    const today = new Date();
    const dateComponents = ref({
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: today.getDate()
    });
    
    const filters = ref({
      search: '',
      date: new Date().toISOString().split('T')[0],
      class_id: '',
      status: ''
    });
    
    const statistics = ref({
      present: 0,
      absent: 0,
      late: 0,
      early_leave: 0
    });
    
    // Date-related computed
    const years = computed(() => {
      const currentYear = new Date().getFullYear();
      const yearList = [];
      for (let i = currentYear - 5; i <= currentYear + 1; i++) {
        yearList.push(i);
      }
      return yearList;
    });
    
    const daysInMonth = computed(() => {
      const year = dateComponents.value.year;
      const month = dateComponents.value.month;
      return new Date(year, month, 0).getDate();
    });
    
    // Computed
    const classes = computed(() => classStore.classes);
    
    const pageTitle = computed(() => {
      return router.currentRoute.value.meta.title || '出席・保健室記録';
    });
    
    const pageDescription = computed(() => {
      if (recordType.value === 'nursing') {
        return '日々の保健室利用を登録・管理します';
      }
      return '日々の出席状況を登録・管理します';
    });
    
    const createButtonText = computed(() => {
      if (recordType.value === 'nursing') {
        return '保健室記録入力';
      }
      return '出席記録入力';
    });
    
    const recordListTitle = computed(() => {
      if (recordType.value === 'nursing') {
        return '保健室記録一覧';
      }
      return '出席記録一覧';
    });
    
    const emptyMessage = computed(() => {
      if (recordType.value === 'nursing') {
        return '保健室記録がありません';
      }
      return '出席記録がありません';
    });
    
    // Table columns - Different columns for nursing vs attendance
    const columns = computed(() => {
      if (recordType.value === 'nursing') {
        return [
          { key: 'date', label: '日付' },
          { key: 'time', label: '時間' },
          { key: 'student_name', label: '学生名' },
          { key: 'class_name', label: 'クラス' },
          { key: 'category', label: '分類' },
          { key: 'type_detail', label: '種別' },
          { key: 'occurrence_time', label: '発生時刻' },
          { key: 'treatment_notes', label: '処置内容' }
        ];
      } else {
        return [
          { key: 'date', label: '日付' },
          { key: 'student_name', label: '学生名' },
          { key: 'class_name', label: 'クラス' },
          { key: 'status', label: '状態' },
          { key: 'notes', label: '備考' }
        ];
      }
    });
    
    // Methods
    const viewDetails = (record) => {
      selectedRecord.value = record;
      showDetailModal.value = true;
    };

    const closeDetailModal = () => {
      showDetailModal.value = false;
      selectedRecord.value = null;
    };

    const handleDelete = async (record) => {
      if (confirm('この記録を削除してもよろしいですか？削除した記録は元に戻せません。')) {
        try {
          const endpoint = recordType.value === 'nursing'
            ? `/api/v1/nursing-visits/${record.id}`
            : `/api/v1/attendance-records/${record.id}`;
          
          await axios.delete(endpoint);
          
          notificationStore.addNotification({
            type: 'success',
            title: '削除完了',
            message: '記録を削除しました'
          });
          
          // Reload data
          await loadAttendanceRecords();
        } catch (error) {
          console.error('削除エラー:', error);
          notificationStore.addNotification({
            type: 'danger',
            title: '削除エラー',
            message: '記録の削除に失敗しました'
          });
        }
      }
    };
    
    const updateDateFilter = () => {
      const year = dateComponents.value.year;
      const month = String(dateComponents.value.month).padStart(2, '0');
      const day = String(dateComponents.value.day).padStart(2, '0');
      filters.value.date = `${year}-${month}-${day}`;
      applyFilters();
    };
    
    const applyFilters = async () => {
      await loadAttendanceRecords();
    };
    
    const goToCreate = () => {
      if (recordType.value === 'nursing') {
        router.push({ name: 'attendance-registration.nursing.create' });
      } else {
        router.push({ name: 'attendance-registration.attendance.create' });
      }
    };
    
    const exportData = () => {
      // TODO: Implement export functionality
      notificationStore.addNotification({
        type: 'info',
        title: 'エクスポート',
        message: 'エクスポート機能は開発中です'
      });
    };

    const getCategoryLabel = (category) => {
      const labels = {
        'internal': '内科',
        'surgical': '外科',
        'other': 'その他',
        'absence': '欠席',
        'late': '遅刻他'
      };
      return labels[category] || category || '-';
    };

    const getTypeDetailLabel = (typeDetail) => {
      const labels = {
        // 内科
        'headache': '頭痛',
        'stomachache': '腹痛',
        'stomach_pain': '胃痛',
        'cold': 'かぜ症状',
        'diarrhea': '下痢',
        'nausea': '吐き気・嘔吐',
        'constipation': '便秘',
        'menstrual_pain': '月経痛',
        'toothache': '歯痛',
        'sleep_deprivation': '睡眠不足',
        'feeling_sick': '気分が悪い',
        'asthma': '喘息',
        'poor_health': '体調不良',
        'hyperventilation': '過呼吸',
        'fatigue': '倦怠感',
        'dizziness': 'めまい・貧血',
        'seizure': '発作',
        'fever': '発熱',
        'cough': '咳',
        // 外科
        'scrape': 'すり傷',
        'cut': '切傷',
        'stab': '刺傷',
        'bruise': '打撲・打ち身',
        'sprain': '捻挫',
        'finger_jam': '突き指',
        'muscle_pain': '筋肉痛',
        'nosebleed': '鼻出血',
        'eye_pain': '眼痛',
        'back_pain': '腰痛',
        'fracture': '骨折・脱臼',
        'hives': 'じんましん',
        'suppuration': '化膿',
        'ear_pain': '耳痛',
        'burn': '火傷',
        'insect_bite': '虫さされ',
        'nail_detachment': '爪剥離',
        'skin_condition': '皮むけ・皮膚疾患',
        'foot_pain': '足痛',
        'pain': '痛み',
        'concussion': '脳震盪',
        'wound_disinfection': '傷口消毒',
        'tooth_extraction': '抜歯・歯が欠ける',
        // その他
        'mental_counseling': 'こころ(相談)',
        'physical_counseling': 'からだ(相談)',
        'somehow': '何となく',
        'psychogenic': '心因性',
        'call_waiting': '呼び出し・待機',
        'measurement': '計測等',
        'infirmary_attendance': '保健室登校',
        'other_counseling': 'その他の相談',
        'safe_place': '居場所',
        'observation': '経過観察',
        'infirmary_exam': '保健室受験',
        'counseling': '相談',
        'rest': '休養',
        'other': 'その他',
        // 欠席（旧）
        'sick': '病欠',
        'accident': '事故欠',
        'suspension': '出停',
        'mourning': '忌引',
        'injury': '怪我',
        'family': '家庭の事情'
      };
      return labels[typeDetail] || typeDetail || '-';
    };

    const getAbsenceReasonLabel = (reason) => {
      const labels = {
        // 欠席
        'headache': '頭痛',
        'cold': 'かぜ症状',
        'stomachache': '腹痛',
        'diarrhea': '下痢',
        'fever': '発熱',
        'sleep_deprivation': '睡眠不足',
        'asthma': '喘息',
        'nausea': '吐き気・嘔吐',
        'nephritis': '腎炎',
        'injury': '外傷',
        'ent_disease': '耳鼻疾患',
        'influenza': 'インフルエンザ',
        'chickenpox': '水痘',
        'mumps': '耳下腺炎',
        'epidemic_keratitis': 'はやり目',
        'other_infectious': 'その他の伝染病',
        'accident': '事故欠',
        'unknown': '理由不明',
        'mourning': '忌引',
        'housework': '家事',
        'poor_health': '体調不良',
        'hospitalization': '入院',
        'truancy': '不登校',
        'hospital_visit': '通院',
        'psychogenic': '心因性',
        'laziness': '怠惰',
        'appendicitis': '虫垂炎',
        'covid19': 'コロナウイルス感染症',
        'orthostatic': '起立性調節障害',
        // 遅刻他
        'carelessness': '不注意',
        'truancy_tendency': '不登校傾向',
        'counseling_room': '相談室登校',
        'other': 'その他'
      };
      return labels[reason] || reason || '-';
    };

    const getInjuryLocationLabel = (location) => {
      const labels = {
        'hand_wrist': '手・手首',
        'arm': '腕',
        'finger_hand': '指（手）',
        'finger_foot': '指（足）',
        'foot_ankle': '足・足首',
        'leg': '脚',
        'knee': '膝',
        'head': '頭',
        'face': '顔',
        'abdomen': '腹',
        'chest': '胸',
        'waist': '腰',
        'back_shoulder_neck': '背中・肩・首',
        'tooth': '歯',
        'eye': '眼',
        'ear': '耳',
        'nose': '鼻',
        'other': 'その他'
      };
      return labels[location] || location || '';
    };

    const getInjuryPlaceLabel = (place) => {
      const labels = {
        'classroom': '教室',
        'hallway': '廊下',
        'stairs': '階段',
        'gymnasium': '体育館',
        'ground': 'グランド',
        'schoolyard': '校庭',
        'music_room': '音楽室',
        'art_room': '美術室',
        'science_room': '理科室',
        'auditorium': '講堂',
        'home_economics': '家庭科室',
        'cooking_room': '調理室',
        'computer_room': 'コンピュータ室',
        'commute_route': '通学路',
        'toilet': 'トイレ',
        'unknown': '不明',
        'bicycle_parking': '自転車置き場',
        'martial_arts': '格技室',
        'practice_room': '実習室',
        'cafeteria': '食堂',
        'other': 'その他'
      };
      return labels[place] || place || '';
    };

    const getSurgicalTreatmentLabel = (treatment) => {
      const labels = {
        'disinfection': '処置（消毒）',
        'icing': '処置（アイシング・湿布）',
        'warm_compress': '温罨法',
        'foreign_removal': '異物除去',
        'parent_contact': '保護者連絡',
        'hospital_instruction': '病院受診を指示',
        'hospital_transport': '病院へ搬送',
        'observation': '経過観察',
        'rest_observation': '経過観察（安静）',
        'teacher_contact': '担任・部活顧問連絡',
        'bandaid': 'カットバン貼付',
        'ointment': '塗り薬',
        'other': 'その他'
      };
      return labels[treatment] || treatment || '';
    };

    const getOccurrenceTimeLabel = (occurrenceTime) => {
      const labels = {
        'during_class': '授業中',
        'self_study': '自習中',
        'before_school': '始業前',
        'break': '休憩時間',
        'lunch': '昼休み',
        'cleaning': '掃除中',
        'after_school': '放課後',
        'club': '部活動',
        'commute': '登下校中',
        'event': '行事',
        'exam': '試験中',
        'supplementary': '補習',
        'extracurricular': '課外授業',
        'other': 'その他'
      };
      return labels[occurrenceTime] || occurrenceTime || '-';
    };

    const getSubjectLabel = (subject) => {
      const labels = {
        'japanese': '国語',
        'social_studies': '社会',
        'mathematics': '数学',
        'science': '理科',
        'english': '英語',
        'music': '音楽',
        'art': '美術',
        'technology': '技術',
        'home_economics': '家庭科',
        'health_pe': '保健体育',
        'lhr': 'LHR',
        'integrated': '総合',
        'commerce': '商業',
        'welfare': '福祉',
        'industrial': '工業',
        'information': '情報',
        'research': '課題研究',
        'life_culture': '生活教養',
        'calligraphy': '書道',
        'elective': '選択',
        'childcare': '保育',
        'other': 'その他'
      };
      return labels[subject] || subject || '';
    };

    const getClubLabel = (club) => {
      const labels = {
        'volleyball': 'バレー',
        'basketball': 'バスケット',
        'tennis': 'テニス',
        'soccer': 'サッカー',
        'baseball': '野球',
        'track_field': '陸上',
        'art': '美術',
        'brass_band': '吹奏楽',
        'judo': '柔道',
        'cycling': '自転車競技',
        'golf': 'ゴルフ',
        'table_tennis': '卓球',
        'archery': '弓道',
        'other': 'その他'
      };
      return labels[club] || club || '';
    };

    const getEventLabel = (event) => {
      const labels = {
        'school_trip': '修学旅行',
        'class_match': 'クラスマッチ',
        'monthly_address': '月頭訓話',
        'sports_day': '運動会',
        'field_training': '郊外研修・実習',
        'culture_festival': '文化祭',
        'lecture': '講演会',
        'assembly': '全校・学年別集会',
        'entrance_ceremony': '入学式',
        'graduation_ceremony': '卒業式',
        'student_assembly': '生徒総会',
        'opening_ceremony': '始業式',
        'closing_ceremony': '終業式',
        'graduation_rehearsal': '卒業式予行',
        'clean_operation': 'クリーン作戦',
        'open_school': 'オープンスクール',
        'other': 'その他'
      };
      return labels[event] || event || '';
    };
    
    const loadAttendanceRecords = async () => {
      loading.value = true;
      try {
        // Build query parameters
        const params = new URLSearchParams();
        if (filters.value.search) params.append('search', filters.value.search);
        if (filters.value.date) params.append('date', filters.value.date);
        if (filters.value.class_id) params.append('class_id', filters.value.class_id);
        if (filters.value.status) params.append('status', filters.value.status);
        
        // Choose API endpoint based on record type
        const apiEndpoint = recordType.value === 'nursing' 
          ? '/api/v1/nursing-visits' 
          : '/api/v1/attendance-records';
        
        // Fetch records from API
        const response = await fetch(`${apiEndpoint}?${params.toString()}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('出席記録の取得に失敗しました');
        }
        
        const result = await response.json();
        
        // Format records based on record type
        let records = result.data || [];
        if (recordType.value === 'attendance') {
          records = records.map(record => ({
            ...record,
            date: record.date ? record.date.split('T')[0] : '',
            student_name: record.student_name || '',
            student_number: record.student_number || '',
            class_name: record.class_name || '',
            grade: record.grade || '',
            gender: record.student?.gender || ''
          }));
        } else if (recordType.value === 'nursing') {
          records = records.map(record => ({
            ...record,
            date: record.date ? record.date.split('T')[0] : '',
            time: record.time || '',
            student_name: record.student_name || '',
            student_number: record.student_number || '',
            class_name: record.class_name || '',
            grade: record.grade || '',
            category: record.category || '',
            type_detail: record.type_detail || '',
            absence_reason: record.absence_reason || '',
            occurrence_time: record.occurrence_time || '',
            subject: record.subject || '',
            club: record.club || '',
            event: record.event || '',
            breakfast: record.breakfast || '',
            bowel_movement: record.bowel_movement || '',
            treatment: record.treatment || '',
            injury_location: record.injury_location || '',
            injury_place: record.injury_place || '',
            surgical_treatment: record.surgical_treatment || '',
            treatment_notes: record.treatment_notes || ''
          }));
        }
        
        attendanceRecords.value = records;
        
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
    
    // Watch for route changes and reload data
    watch(() => router.currentRoute.value.meta.recordType, async (newType, oldType) => {
      if (newType && newType !== oldType) {
        console.log('Route changed, reloading data for:', newType);
        await loadAttendanceRecords();
      }
    });
    
    return {
      recordType,
      loading,
      attendanceRecords,
      filters,
      dateComponents,
      years,
      daysInMonth,
      statistics,
      classes,
      pageTitle,
      pageDescription,
      createButtonText,
      recordListTitle,
      emptyMessage,
      columns,
      router,
      showDetailModal,
      selectedRecord,
      viewDetails,
      closeDetailModal,
      handleDelete,
      updateDateFilter,
      applyFilters,
      goToCreate,
      exportData,
      getCategoryLabel,
      getTypeDetailLabel,
      getAbsenceReasonLabel,
      getInjuryLocationLabel,
      getInjuryPlaceLabel,
      getSurgicalTreatmentLabel,
      getOccurrenceTimeLabel,
      getSubjectLabel,
      getClubLabel,
      getEventLabel
    };
  }
};
</script>
