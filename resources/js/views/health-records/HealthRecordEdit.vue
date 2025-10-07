<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <router-link to="/health-records" class="text-gray-400 hover:text-gray-500">
                  健康記録管理
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <router-link 
                    :to="`/health-records/${record?.id}`" 
                    class="ml-4 text-sm text-gray-400 hover:text-gray-500"
                  >
                    記録詳細
                  </router-link>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">編集</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            健康記録の編集
          </h1>
          <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <UserIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ record?.student?.name }}
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <CalendarIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ formatDate(record?.measured_date) }}
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-4xl" v-if="record">
      <form @submit.prevent="handleSubmit">
        <div class="space-y-8">
          <!-- Current Record Overview -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">現在の記録</h2>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <!-- Current Height -->
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">
                  {{ originalRecord.height }} <span class="text-base text-gray-500">cm</span>
                </div>
                <div class="text-sm text-gray-500 mt-1">現在の身長</div>
              </div>

              <!-- Current Weight -->
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">
                  {{ originalRecord.weight }} <span class="text-base text-gray-500">kg</span>
                </div>
                <div class="text-sm text-gray-500 mt-1">現在の体重</div>
              </div>

              <!-- Current BMI -->
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold" :class="getBMIColor(originalRecord.bmi)">
                  {{ originalRecord.bmi }}
                </div>
                <div class="text-sm text-gray-500 mt-1">現在のBMI</div>
                <BaseBadge :variant="getBMIVariant(originalRecord.bmi)" size="sm" class="mt-2">
                  {{ getBMICategory(originalRecord.bmi) }}
                </BaseBadge>
              </div>

              <!-- Measured Date -->
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-lg font-bold text-gray-900">
                  {{ formatShortDate(originalRecord.measured_date) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">測定日</div>
                <div class="text-xs text-gray-400 mt-1">
                  {{ originalRecord.academic_year }}年度
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Edit Form -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">測定データの修正</h2>
              <p class="mt-1 text-sm text-gray-500">
                必要な項目を修正してください。変更内容は履歴として保存されます。
              </p>
            </template>

            <div class="space-y-6">
              <!-- Basic Information -->
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Measured Date -->
                <div>
                  <BaseInput
                    type="date"
                    v-model="form.measured_date"
                    label="測定日"
                    required
                    :error="errors.measured_date"
                  />
                  <div v-if="form.measured_date !== originalRecord.measured_date" class="mt-1 text-sm text-blue-600">
                    変更前: {{ formatDate(originalRecord.measured_date) }}
                  </div>
                </div>

                <!-- Academic Year -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="form.academic_year"
                    label="年度"
                    required
                    :error="errors.academic_year"
                  >
                    <option value="">選択してください</option>
                    <option
                      v-for="year in academicYearOptions"
                      :key="year"
                      :value="year"
                    >
                      {{ year }}年度
                    </option>
                  </BaseInput>
                  <div v-if="form.academic_year !== originalRecord.academic_year" class="mt-1 text-sm text-blue-600">
                    変更前: {{ originalRecord.academic_year }}年度
                  </div>
                </div>
              </div>

              <!-- Measurement Data -->
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Height -->
                <div>
                  <BaseInput
                    type="number"
                    step="0.1"
                    v-model="form.height"
                    label="身長 (cm)"
                    required
                    placeholder="150.5"
                    :error="errors.height"
                    @input="calculateBMI"
                  />
                  <div v-if="form.height != originalRecord.height" class="mt-1 text-sm text-blue-600">
                    変更前: {{ originalRecord.height }}cm 
                    ({{ form.height > originalRecord.height ? '+' : '' }}{{ (form.height - originalRecord.height).toFixed(1) }}cm)
                  </div>
                </div>

                <!-- Weight -->
                <div>
                  <BaseInput
                    type="number"
                    step="0.1"
                    v-model="form.weight"
                    label="体重 (kg)"
                    required
                    placeholder="45.5"
                    :error="errors.weight"
                    @input="calculateBMI"
                  />
                  <div v-if="form.weight != originalRecord.weight" class="mt-1 text-sm text-blue-600">
                    変更前: {{ originalRecord.weight }}kg 
                    ({{ form.weight > originalRecord.weight ? '+' : '' }}{{ (form.weight - originalRecord.weight).toFixed(1) }}kg)
                  </div>
                </div>
              </div>

              <!-- BMI Display -->
              <div v-if="form.height && form.weight" class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- New BMI -->
                  <div class="text-center">
                    <div class="text-sm text-gray-500 mb-2">修正後のBMI</div>
                    <div class="text-3xl font-bold mb-2" :class="getBMIColor(calculatedBMI)">
                      {{ calculatedBMI }}
                    </div>
                    <BaseBadge :variant="getBMIVariant(calculatedBMI)">
                      {{ getBMICategory(calculatedBMI) }}
                    </BaseBadge>
                  </div>

                  <!-- Comparison -->
                  <div class="text-center">
                    <div class="text-sm text-gray-500 mb-2">変更量</div>
                    <div class="text-2xl font-bold mb-2" 
                         :class="bmiChange > 0 ? 'text-red-600' : bmiChange < 0 ? 'text-green-600' : 'text-gray-500'">
                      {{ bmiChange > 0 ? '+' : '' }}{{ bmiChange }}
                    </div>
                    <div class="text-sm text-gray-500">
                      現在: {{ originalRecord.bmi }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.notes"
                  label="備考"
                  placeholder="測定時の特記事項や修正理由を記入してください"
                  rows="3"
                  :error="errors.notes"
                />
                <div v-if="form.notes !== originalRecord.notes" class="mt-1 text-sm text-blue-600">
                  <span v-if="originalRecord.notes">変更前: {{ originalRecord.notes }}</span>
                  <span v-else>備考を追加</span>
                </div>
              </div>

              <!-- Edit Reason -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.edit_reason"
                  label="修正理由 *"
                  placeholder="なぜこの記録を修正するのか理由を記入してください"
                  rows="2"
                  required
                  :error="errors.edit_reason"
                />
                <div class="mt-1 text-xs text-gray-500">
                  修正理由は履歴として保存され、後から確認できます。
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Change Summary -->
          <BaseCard v-if="hasChanges">
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">
                <ExclamationTriangleIcon class="h-5 w-5 inline mr-2 text-amber-500" />
                変更内容の確認
              </h2>
            </template>

            <div class="space-y-4">
              <div class="bg-amber-50 border border-amber-200 rounded-md p-4">
                <div class="text-sm text-amber-800">
                  <p class="font-medium">以下の項目が変更されます：</p>
                  <ul class="mt-2 space-y-1">
                    <li v-if="form.measured_date !== originalRecord.measured_date">
                      • 測定日: {{ formatDate(originalRecord.measured_date) }} → {{ formatDate(form.measured_date) }}
                    </li>
                    <li v-if="form.academic_year !== originalRecord.academic_year">
                      • 年度: {{ originalRecord.academic_year }}年度 → {{ form.academic_year }}年度
                    </li>
                    <li v-if="form.height != originalRecord.height">
                      • 身長: {{ originalRecord.height }}cm → {{ form.height }}cm
                    </li>
                    <li v-if="form.weight != originalRecord.weight">
                      • 体重: {{ originalRecord.weight }}kg → {{ form.weight }}kg
                    </li>
                    <li v-if="calculatedBMI !== originalRecord.bmi">
                      • BMI: {{ originalRecord.bmi }} → {{ calculatedBMI }}
                    </li>
                    <li v-if="form.notes !== originalRecord.notes">
                      • 備考: {{ originalRecord.notes ? '修正' : '追加' }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <BaseButton
              type="button"
              variant="secondary"
              @click="$router.back()"
            >
              キャンセル
            </BaseButton>
            <BaseButton
              type="button"
              variant="secondary"
              @click="resetForm"
              :disabled="isSubmitting"
            >
              リセット
            </BaseButton>
            <BaseButton
              type="submit"
              variant="primary"
              :loading="isSubmitting"
              :disabled="!hasChanges"
            >
              変更を保存
            </BaseButton>
          </div>
        </div>
      </form>
    </div>

    <!-- Loading State -->
    <div v-else-if="isLoading" class="flex justify-center items-center h-64">
      <BaseSpinner size="lg" />
    </div>

    <!-- Error State -->
    <div v-else class="text-center py-12">
      <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">記録が見つかりません</h3>
      <p class="mt-1 text-sm text-gray-500">
        指定された健康記録が存在しないか、削除されている可能性があります。
      </p>
      <div class="mt-6">
        <BaseButton @click="$router.push('/health-records')">
          健康記録一覧に戻る
        </BaseButton>
      </div>
    </div>

    <!-- Right Sidebar -->
    <template #rightSidebar>
      <div class="space-y-6" v-if="record">
        <!-- Edit Guidelines -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">
              <InformationCircleIcon class="h-5 w-5 inline mr-2 text-blue-500" />
              修正ガイド
            </h3>
          </template>
          
          <div class="space-y-4 text-sm text-gray-600">
            <div>
              <h4 class="font-medium text-gray-700 mb-2">修正時の注意点</h4>
              <ul class="space-y-1 list-disc list-inside">
                <li>測定値は正確性を最優先に修正</li>
                <li>修正理由は必須項目です</li>
                <li>変更履歴は自動的に保存されます</li>
                <li>BMIは自動計算されます</li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium text-gray-700 mb-2">適正値の目安</h4>
              <ul class="space-y-1">
                <li><span class="text-yellow-600">やせ:</span> BMI 18.5未満</li>
                <li><span class="text-green-600">標準:</span> BMI 18.5-24.9</li>
                <li><span class="text-yellow-600">肥満:</span> BMI 25.0以上</li>
              </ul>
            </div>
          </div>
        </BaseCard>

        <!-- Student Info -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">学生情報</h3>
          </template>
          
          <div class="space-y-3">
            <div class="text-center">
              <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                <span class="text-lg font-medium text-blue-700">
                  {{ record.student?.name?.charAt(0) }}
                </span>
              </div>
              <h4 class="mt-2 font-medium text-gray-900">{{ record.student?.name }}</h4>
              <p class="text-sm text-gray-500">{{ record.student?.school_class?.name }}</p>
            </div>
            
            <div class="border-t pt-3 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">学生番号</span>
                <span class="font-medium">{{ record.student?.student_number }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">性別</span>
                <span class="font-medium">{{ record.student?.gender === 'male' ? '男' : '女' }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">年齢</span>
                <span class="font-medium">{{ calculateAge(record.student?.birth_date) }}歳</span>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Measurement History -->
        <BaseCard v-if="studentRecords.length > 1">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">測定履歴</h3>
          </template>
          
          <div class="space-y-3">
            <div
              v-for="historyRecord in studentRecords.slice(0, 5)"
              :key="historyRecord.id"
              class="flex items-center justify-between p-2 rounded"
              :class="{ 
                'bg-blue-50 border border-blue-200': historyRecord.id === record.id,
                'hover:bg-gray-50': historyRecord.id !== record.id
              }"
            >
              <div>
                <div class="text-sm font-medium text-gray-900">
                  {{ formatShortDate(historyRecord.measured_date) }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ historyRecord.height }}cm / {{ historyRecord.weight }}kg
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm font-medium text-gray-900">
                  {{ historyRecord.bmi }}
                </div>
                <BaseBadge :variant="getBMIVariant(historyRecord.bmi)" size="sm">
                  {{ getBMICategory(historyRecord.bmi) }}
                </BaseBadge>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Quick Actions -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">関連アクション</h3>
          </template>
          
          <div class="space-y-3">
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/health-records/${record.id}`)"
            >
              <EyeIcon class="h-4 w-4 mr-2" />
              詳細表示
            </BaseButton>
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/students/${record.student_id}`)"
            >
              <UserIcon class="h-4 w-4 mr-2" />
              学生詳細
            </BaseButton>
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push('/health-records/create', { query: { student_id: record.student_id } })"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              新しい測定
            </BaseButton>
          </div>
        </BaseCard>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseBadge,
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

const UserIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
  `
};

const CalendarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
  `
};

const ExclamationTriangleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
    </svg>
  `
};

const InformationCircleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const EyeIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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

export default {
  name: 'HealthRecordEdit',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseBadge,
    BaseSpinner,
    ChevronRightIcon,
    UserIcon,
    CalendarIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    EyeIcon,
    PlusIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const healthRecordStore = useHealthRecordStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const isSubmitting = ref(false);
    const record = ref(null);
    const originalRecord = ref({});
    const studentRecords = ref([]);
    
    // Form data
    const form = reactive({
      measured_date: '',
      academic_year: '',
      height: '',
      weight: '',
      notes: '',
      edit_reason: ''
    });
    
    // Errors
    const errors = ref({});
    
    // Computed
    const recordId = computed(() => parseInt(route.params.id));
    const currentYear = new Date().getFullYear();
    
    const academicYearOptions = computed(() => {
      return [currentYear - 1, currentYear, currentYear + 1];
    });
    
    const calculatedBMI = computed(() => {
      const height = parseFloat(form.height);
      const weight = parseFloat(form.weight);
      
      if (height && weight) {
        const heightInMeters = height / 100;
        return (weight / (heightInMeters * heightInMeters)).toFixed(1);
      }
      
      return null;
    });
    
    const bmiChange = computed(() => {
      if (calculatedBMI.value && originalRecord.value.bmi) {
        return (parseFloat(calculatedBMI.value) - parseFloat(originalRecord.value.bmi)).toFixed(1);
      }
      return 0;
    });
    
    const hasChanges = computed(() => {
      if (!record.value) return false;
      
      return (
        form.measured_date !== originalRecord.value.measured_date ||
        form.academic_year != originalRecord.value.academic_year ||
        form.height != originalRecord.value.height ||
        form.weight != originalRecord.value.weight ||
        form.notes !== (originalRecord.value.notes || '')
      );
    });
    
    // Methods
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };
    
    const formatShortDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('ja-JP', {
        month: 'numeric',
        day: 'numeric'
      });
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
    
    const getBMICategory = (bmi) => {
      if (!bmi) return '未測定';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return 'やせ';
      if (bmiValue < 25) return '標準';
      if (bmiValue < 30) return '肥満1度';
      return '肥満2度以上';
    };
    
    const getBMIVariant = (bmi) => {
      if (!bmi) return 'secondary';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return 'warning';
      if (bmiValue < 25) return 'success';
      if (bmiValue < 30) return 'warning';
      return 'danger';
    };
    
    const getBMIColor = (bmi) => {
      if (!bmi) return 'text-gray-900';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return 'text-yellow-600';
      if (bmiValue < 25) return 'text-green-600';
      if (bmiValue < 30) return 'text-yellow-600';
      return 'text-red-600';
    };
    
    const calculateBMI = () => {
      // BMI is calculated automatically via computed property
    };
    
    const initializeForm = () => {
      if (!record.value) return;
      
      form.measured_date = record.value.measured_date;
      form.academic_year = record.value.academic_year;
      form.height = record.value.height;
      form.weight = record.value.weight;
      form.notes = record.value.notes || '';
      form.edit_reason = '';
      
      // Store original values for comparison
      originalRecord.value = {
        measured_date: record.value.measured_date,
        academic_year: record.value.academic_year,
        height: record.value.height,
        weight: record.value.weight,
        bmi: record.value.bmi,
        notes: record.value.notes || ''
      };
    };
    
    const resetForm = () => {
      if (!record.value) return;
      
      initializeForm();
      errors.value = {};
    };
    
    const validateForm = () => {
      errors.value = {};
      
      if (!form.measured_date) {
        errors.value.measured_date = '測定日は必須です';
      }
      
      if (!form.academic_year) {
        errors.value.academic_year = '年度を選択してください';
      }
      
      if (!form.height) {
        errors.value.height = '身長は必須です';
      } else if (parseFloat(form.height) < 50 || parseFloat(form.height) > 250) {
        errors.value.height = '身長は50-250cmの範囲で入力してください';
      }
      
      if (!form.weight) {
        errors.value.weight = '体重は必須です';
      } else if (parseFloat(form.weight) < 10 || parseFloat(form.weight) > 200) {
        errors.value.weight = '体重は10-200kgの範囲で入力してください';
      }
      
      if (!form.edit_reason.trim()) {
        errors.value.edit_reason = '修正理由は必須です';
      }
      
      if (!hasChanges.value) {
        errors.value.general = '変更する項目がありません';
        return false;
      }
      
      return Object.keys(errors.value).length === 0;
    };
    
    const handleSubmit = async () => {
      if (!validateForm()) {
        notificationStore.addNotification({
          type: 'warning',
          title: '入力エラー',
          message: '入力内容を確認してください'
        });
        return;
      }
      
      isSubmitting.value = true;
      
      try {
        const updateData = {
          year: parseInt(form.academic_year),
          height: parseFloat(form.height),
          weight: parseFloat(form.weight),
          notes: form.notes || null,
          edit_reason: form.edit_reason
        };
        
        await healthRecordStore.updateHealthRecord(recordId.value, updateData);
        
        notificationStore.addNotification({
          type: 'success',
          title: '更新完了',
          message: '健康記録を更新しました'
        });
        
        router.push(`/health-records/${recordId.value}`);
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '更新エラー',
          message: error.response?.data?.message || '健康記録の更新に失敗しました'
        });
      } finally {
        isSubmitting.value = false;
      }
    };
    
    const fetchRecord = async () => {
      try {
        isLoading.value = true;
        
        const healthRecord = await healthRecordStore.fetchHealthRecord(recordId.value);
        if (!healthRecord) {
          record.value = null;
          return;
        }
        
        record.value = healthRecord;
        initializeForm();
        
        // Fetch student records for history
        if (healthRecord.student_id) {
          studentRecords.value = await healthRecordStore.getHealthRecordsByStudent(healthRecord.student_id);
        }
        
      } catch (error) {
        console.error('Failed to fetch health record:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '健康記録の取得に失敗しました'
        });
        record.value = null;
      } finally {
        isLoading.value = false;
      }
    };
    
    // Watchers
    watch(() => route.params.id, () => {
      if (route.params.id) {
        fetchRecord();
      }
    });
    
    // Lifecycle
    onMounted(() => {
      fetchRecord();
    });
    
    return {
      isLoading,
      isSubmitting,
      record,
      originalRecord,
      studentRecords,
      form,
      errors,
      academicYearOptions,
      calculatedBMI,
      bmiChange,
      hasChanges,
      formatDate,
      formatShortDate,
      calculateAge,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      calculateBMI,
      resetForm,
      handleSubmit
    };
  }
};
</script>