<template>
  <AppLayout :show-right-sidebar="true">
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <router-link to="/students" class="text-gray-400 hover:text-gray-500">
                  学生管理
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">学生詳細</span>
                </div>
              </li>
            </ol>
          </nav>
          <div class="mt-2 flex items-center">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              {{ student?.name }}
            </h1>
            <BaseBadge
              v-if="student?.school_class"
              variant="info"
              class="ml-3"
            >
              {{ student.school_class.name }}
            </BaseBadge>
          </div>
          <p class="mt-1 text-sm text-gray-500">
            学生番号: {{ student?.student_number }}
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="primary"
            @click="$router.push(`/health-records/create?student_id=${student?.id}`)"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            健康記録を追加
          </BaseButton>
          <BaseButton
            variant="secondary"
            @click="$router.push(`/students/${student?.id}/edit`)"
          >
            <PencilIcon class="h-4 w-4 mr-2" />
            編集
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <BaseSpinner size="lg" text="読み込み中..." />
    </div>

    <!-- Content -->
    <div v-else-if="student" class="space-y-8">
      <!-- Basic Information -->
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Student Info Card -->
        <div class="lg:col-span-2">
          <BaseCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                <BaseBadge :variant="getStatusVariant(student.status)">
                  {{ getStatusText(student.status) }}
                </BaseBadge>
              </div>
            </template>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">氏名</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ student.name }}</dd>
              </div>
              
              <div v-if="student.name_kana">
                <dt class="text-sm font-medium text-gray-500">ふりがな</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ student.name_kana }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">学生番号</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ student.student_number }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">性別</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ student.gender === 'male' ? '男' : student.gender === 'female' ? '女' : '-' }}
                </dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">生年月日</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ formatDate(student.birth_date) }}
                  <span class="text-gray-500 ml-2">({{ calculateAge(student.birth_date) }}歳)</span>
                </dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">所属クラス</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ student.school_class?.name || '未所属' }}
                </dd>
              </div>
            </div>
          </BaseCard>
        </div>

        <!-- Health Overview -->
        <div>
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">健康状況概要</h2>
            </template>

            <div class="space-y-4">
              <div v-if="latestHealthRecord">
                <dt class="text-sm font-medium text-gray-500">最新記録日</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ formatDate(latestHealthRecord.measured_date) }}
                </dd>
              </div>

              <div v-if="latestHealthRecord">
                <dt class="text-sm font-medium text-gray-500">身長・体重</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ latestHealthRecord.height }}cm / {{ latestHealthRecord.weight }}kg
                </dd>
              </div>

              <div v-if="latestHealthRecord?.bmi">
                <dt class="text-sm font-medium text-gray-500">BMI</dt>
                <dd class="mt-1 flex items-center">
                  <span class="text-sm text-gray-900">{{ latestHealthRecord.bmi.toFixed(1) }}</span>
                  <BaseBadge
                    :variant="getBmiStatus(latestHealthRecord.bmi).variant"
                    size="sm"
                    class="ml-2"
                  >
                    {{ getBmiStatus(latestHealthRecord.bmi).label }}
                  </BaseBadge>
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">健康記録数</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ student.health_records_count || 0 }}件
                </dd>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>

      <!-- Contact Information -->
      <BaseCard>
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">連絡先情報</h2>
        </template>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-if="student.phone">
            <dt class="text-sm font-medium text-gray-500">電話番号</dt>
            <dd class="mt-1 text-sm text-gray-900">
              <a :href="`tel:${student.phone}`" class="text-blue-600 hover:text-blue-500">
                {{ student.phone }}
              </a>
            </dd>
          </div>
          
          <div v-if="student.emergency_contact">
            <dt class="text-sm font-medium text-gray-500">緊急連絡先</dt>
            <dd class="mt-1 text-sm text-gray-900">
              <a :href="`tel:${student.emergency_contact}`" class="text-blue-600 hover:text-blue-500">
                {{ student.emergency_contact }}
              </a>
            </dd>
          </div>
          
          <div v-if="student.address" class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500">住所</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ student.address }}</dd>
          </div>
        </div>
      </BaseCard>

      <!-- Medical Information -->
      <BaseCard v-if="hasMedicalInfo">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">医療情報</h2>
        </template>

        <div class="space-y-6">
          <div v-if="student.allergies">
            <dt class="text-sm font-medium text-gray-500">アレルギー</dt>
            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
              {{ student.allergies }}
            </dd>
          </div>
          
          <div v-if="student.medical_conditions">
            <dt class="text-sm font-medium text-gray-500">既往症・持病</dt>
            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
              {{ student.medical_conditions }}
            </dd>
          </div>
          
          <div v-if="student.medications">
            <dt class="text-sm font-medium text-gray-500">服用中の薬</dt>
            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
              {{ student.medications }}
            </dd>
          </div>
        </div>
      </BaseCard>

      <!-- Health Records History -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">健康記録履歴</h2>
            <BaseButton
              variant="secondary"
              size="sm"
              @click="$router.push(`/health-records?student_id=${student.id}`)"
            >
              すべて見る
            </BaseButton>
          </div>
        </template>

        <div v-if="healthRecords.length > 0">
          <BaseTable
            :columns="healthRecordColumns"
            :data="healthRecords"
            :show-pagination="false"
            @row-click="handleHealthRecordClick"
          >
            <!-- BMI Column -->
            <template #bmi="{ row }">
              <div v-if="row.bmi" class="flex items-center">
                <span class="text-sm text-gray-900">{{ row.bmi.toFixed(1) }}</span>
                <BaseBadge
                  :variant="getBmiStatus(row.bmi).variant"
                  size="xs"
                  class="ml-2"
                >
                  {{ getBmiStatus(row.bmi).label }}
                </BaseBadge>
              </div>
              <span v-else class="text-gray-400">-</span>
            </template>

            <!-- Date Column -->
            <template #recorded_date="{ row }">
              {{ formatDate(row.recorded_date) }}
            </template>
          </BaseTable>
        </div>
        <div v-else class="text-center py-8">
          <HeartIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">
            健康記録がありません
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            最初の健康記録を追加しましょう。
          </p>
          <div class="mt-6">
            <BaseButton
              variant="primary"
              @click="$router.push(`/health-records/create?student_id=${student.id}`)"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              記録を追加
            </BaseButton>
          </div>
        </div>
      </BaseCard>

      <!-- Notes -->
      <BaseCard v-if="student.notes">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">備考</h2>
        </template>

        <div class="text-sm text-gray-900 whitespace-pre-line">
          {{ student.notes }}
        </div>
      </BaseCard>
    </div>

    <!-- Right Sidebar -->
    <template #rightSidebar>
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div>
          <h3 class="text-sm font-medium text-gray-900 mb-3">クイックアクション</h3>
          <div class="space-y-2">
            <BaseButton
              variant="primary"
              size="sm"
              class="w-full justify-start"
              @click="$router.push(`/health-records/create?student_id=${student?.id}`)"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              健康記録追加
            </BaseButton>
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full justify-start"
              @click="$router.push(`/students/${student?.id}/edit`)"
            >
              <PencilIcon class="h-4 w-4 mr-2" />
              情報編集
            </BaseButton>
            <BaseButton
              variant="info"
              size="sm"
              class="w-full justify-start"
              @click="generateReport"
            >
              <DocumentIcon class="h-4 w-4 mr-2" />
              レポート生成
            </BaseButton>
          </div>
        </div>

        <!-- Health Trends -->
        <div v-if="healthRecords.length > 1">
          <h3 class="text-sm font-medium text-gray-900 mb-3">成長トレンド</h3>
          <div class="space-y-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">身長変化</span>
                <span class="text-sm font-medium" :class="getHeightTrendColor()">
                  {{ getHeightTrend() }}
                </span>
              </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">体重変化</span>
                <span class="text-sm font-medium" :class="getWeightTrendColor()">
                  {{ getWeightTrend() }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStudentStore } from '@/stores/student.js';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseButton,
  BaseBadge,
  BaseTable,
  BaseSpinner
} from '@/components/index.js';

// Icons
const ChevronRightIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
  `
};

const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
  `
};

const PencilIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
  `
};

const DocumentIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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

export default {
  name: 'StudentShow',
  components: {
    AppLayout,
    BaseCard,
    BaseButton,
    BaseBadge,
    BaseTable,
    BaseSpinner,
    ChevronRightIcon,
    PlusIcon,
    PencilIcon,
    DocumentIcon,
    HeartIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const studentStore = useStudentStore();
    const healthRecordStore = useHealthRecordStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const healthRecords = ref([]);
    
    // Computed
    const student = computed(() => studentStore.currentStudent);
    const latestHealthRecord = computed(() => {
      return healthRecords.value[0] || null;
    });
    
    const hasMedicalInfo = computed(() => {
      return student.value?.allergies || 
             student.value?.medical_conditions || 
             student.value?.medications;
    });
    
    // Health record table columns
    const healthRecordColumns = [
      {
        key: 'measured_date',
        label: '記録日',
        width: '100px'
      },
      {
        key: 'height',
        label: '身長(cm)',
        width: '80px'
      },
      {
        key: 'weight',
        label: '体重(kg)',
        width: '80px'
      },
      {
        key: 'bmi',
        label: 'BMI',
        width: '120px'
      }
    ];
    
    // Methods
    const formatDate = (dateString) => {
      if (!dateString) return '-';
      return new Date(dateString).toLocaleDateString('ja-JP');
    };
    
    const calculateAge = (birthDate) => {
      if (!birthDate) return 0;
      const today = new Date();
      const birth = new Date(birthDate);
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      
      return age;
    };
    
    const getBmiStatus = (bmi) => {
      if (bmi < 18.5) {
        return { label: '低体重', variant: 'info' };
      } else if (bmi < 25) {
        return { label: '標準', variant: 'success' };
      } else if (bmi < 30) {
        return { label: '過体重', variant: 'warning' };
      } else {
        return { label: '肥満', variant: 'danger' };
      }
    };
    
    const getStatusVariant = (status) => {
      switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'secondary';
        case 'graduated': return 'info';
        default: return 'secondary';
      }
    };
    
    const getStatusText = (status) => {
      switch (status) {
        case 'active': return '在学中';
        case 'inactive': return '休学中';
        case 'graduated': return '卒業';
        default: return '不明';
      }
    };
    
    const getHeightTrend = () => {
      if (healthRecords.value.length < 2) return '-';
      
      const latest = healthRecords.value[0];
      const previous = healthRecords.value[1];
      const diff = latest.height - previous.height;
      
      if (diff > 0) return `+${diff.toFixed(1)}cm`;
      if (diff < 0) return `${diff.toFixed(1)}cm`;
      return '±0cm';
    };
    
    const getHeightTrendColor = () => {
      if (healthRecords.value.length < 2) return 'text-gray-500';
      
      const latest = healthRecords.value[0];
      const previous = healthRecords.value[1];
      const diff = latest.height - previous.height;
      
      if (diff > 0) return 'text-green-600';
      if (diff < 0) return 'text-red-600';
      return 'text-gray-600';
    };
    
    const getWeightTrend = () => {
      if (healthRecords.value.length < 2) return '-';
      
      const latest = healthRecords.value[0];
      const previous = healthRecords.value[1];
      const diff = latest.weight - previous.weight;
      
      if (diff > 0) return `+${diff.toFixed(1)}kg`;
      if (diff < 0) return `${diff.toFixed(1)}kg`;
      return '±0kg';
    };
    
    const getWeightTrendColor = () => {
      if (healthRecords.value.length < 2) return 'text-gray-500';
      
      const latest = healthRecords.value[0];
      const previous = healthRecords.value[1];
      const diff = latest.weight - previous.weight;
      
      if (diff > 0) return 'text-blue-600';
      if (diff < 0) return 'text-orange-600';
      return 'text-gray-600';
    };
    
    const handleHealthRecordClick = (record) => {
      router.push(`/health-records/${record.id}`);
    };
    
    const generateReport = async () => {
      try {
        await studentStore.generateStudentReport(student.value.id);
        notificationStore.addNotification({
          type: 'success',
          title: 'レポート生成完了',
          message: '学生レポートを生成しました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'レポート生成エラー',
          message: 'レポートの生成に失敗しました'
        });
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      const studentId = route.params.id;
      
      try {
        // 学生情報と健康記録を並行取得
        const [studentData, healthRecordsData] = await Promise.all([
          studentStore.fetchStudent(studentId),
          healthRecordStore.getHealthRecordsByStudent(studentId)
        ]);
        
        // 健康記録をstateに設定
        healthRecords.value = healthRecordsData || [];
        
      } catch (error) {
        console.error('データ取得エラー:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '学生情報の取得に失敗しました'
        });
        router.push('/students');
      } finally {
        isLoading.value = false;
      }
    });
    
    return {
      isLoading,
      student,
      healthRecords,
      latestHealthRecord,
      hasMedicalInfo,
      healthRecordColumns,
      formatDate,
      calculateAge,
      getBmiStatus,
      getStatusVariant,
      getStatusText,
      getHeightTrend,
      getHeightTrendColor,
      getWeightTrend,
      getWeightTrendColor,
      handleHealthRecordClick,
      generateReport
    };
  }
};
</script>