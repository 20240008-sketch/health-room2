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
                  <span class="ml-4 text-sm font-medium text-gray-500">新規測定</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            健康測定記録
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            学生の身長・体重を測定して記録します
          </p>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-4xl">
      <form @submit.prevent="handleSubmit">
        <div class="space-y-8">
          <!-- Student Selection -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">測定対象の選択</h2>
            </template>

            <div class="space-y-6">
              <!-- Selection Method -->
              <div>
                <label class="text-base font-medium text-gray-900">選択方法</label>
                <div class="mt-2 space-x-6 flex">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      name="selection_method"
                      value="individual"
                      v-model="selectionMethod"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-700">個別選択</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      name="selection_method"
                      value="bulk"
                      v-model="selectionMethod"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-700">一括測定</span>
                  </label>
                </div>
              </div>

              <!-- Individual Selection -->
              <div v-if="selectionMethod === 'individual'" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Student Search -->
                <div class="sm:col-span-2">
                  <BaseInput
                    v-model="studentSearch"
                    label="学生検索"
                    placeholder="学生名または学生番号で検索..."
                    @input="searchStudents"
                  >
                    <template #prepend>
                      <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                    </template>
                  </BaseInput>
                </div>

                <!-- Student Selection Dropdown -->
                <div class="sm:col-span-2" v-if="searchResults.length > 0">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    学生を選択してください
                  </label>
                  <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-md">
                    <div
                      v-for="student in searchResults"
                      :key="student.id"
                      class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
                      @click="selectStudent(student)"
                    >
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="font-medium text-gray-900">{{ student.name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ student.student_number }} | {{ student.school_class?.name }}
                          </div>
                        </div>
                        <div class="text-sm text-gray-400">
                          <span v-if="student.latest_health_record">
                            前回: {{ formatDate(student.latest_health_record.measured_date) }}
                          </span>
                          <span v-else>初回測定</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Selected Student Display -->
                <div v-if="selectedStudent && selectionMethod === 'individual'" class="sm:col-span-2">
                  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                          <span class="text-lg font-medium text-blue-700">
                            {{ selectedStudent.name.charAt(0) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">{{ selectedStudent.name }}</h3>
                        <p class="text-sm text-gray-500">
                          {{ selectedStudent.student_number }} | {{ selectedStudent.school_class?.name }}
                        </p>
                        <div v-if="selectedStudent.latest_health_record" class="mt-1 text-sm text-gray-600">
                          前回測定: {{ formatDate(selectedStudent.latest_health_record.measured_date) }} 
                          (身長 {{ selectedStudent.latest_health_record.height }}cm, 
                          体重 {{ selectedStudent.latest_health_record.weight }}kg)
                        </div>
                        <div v-else class="mt-1 text-sm text-gray-600">
                          初回測定です
                        </div>
                      </div>
                      <BaseButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="clearSelectedStudent"
                      >
                        変更
                      </BaseButton>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bulk Selection -->
              <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <!-- Academic Year -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="bulkFilters.academic_year"
                    label="年度"
                    required
                    @change="updateBulkSelection"
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
                </div>

                <!-- Grade -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="bulkFilters.grade"
                    label="学年"
                    @change="updateBulkSelection"
                  >
                    <option value="">すべての学年</option>
                    <option value="1">1年</option>
                    <option value="2">2年</option>
                    <option value="3">3年</option>
                  </BaseInput>
                </div>

                <!-- Class -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="bulkFilters.class_id"
                    label="クラス"
                    @change="updateBulkSelection"
                    :disabled="!bulkFilters.grade"
                  >
                    <option value="">すべてのクラス</option>
                    <option value="特進">特進</option>
                    <option value="進学">進学</option>
                    <option value="調理">調理</option>
                    <option value="福祉">福祉</option>
                    <option value="情会">情会</option>
                    <option value="総合１">総合１</option>
                    <option value="総合２">総合２</option>
                    <option value="総合３">総合３</option>
                  </BaseInput>
                </div>

                <!-- Students List for Bulk -->
                <div v-if="bulkStudents.length > 0" class="sm:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    測定対象学生 ({{ selectedStudents.length }}/{{ bulkStudents.length }}名)
                  </label>
                  <div class="bg-white border border-gray-300 rounded-md max-h-60 overflow-y-auto">
                    <div class="p-3 border-b border-gray-200 bg-gray-50">
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          :checked="allStudentsSelected"
                          @change="toggleAllStudents"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700">全て選択</span>
                      </label>
                    </div>
                    <div
                      v-for="student in bulkStudents"
                      :key="student.id"
                      class="p-3 border-b border-gray-200 last:border-b-0"
                    >
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          :value="student.id"
                          v-model="selectedStudentIds"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <div class="ml-3 flex-1">
                          <div class="font-medium text-gray-900">{{ student.name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ student.student_number }} | {{ student.school_class?.name }}
                          </div>
                        </div>
                        <div class="text-sm text-gray-400">
                          <span v-if="student.latest_health_record">
                            前回: {{ formatDate(student.latest_health_record.measured_date) }}
                          </span>
                          <span v-else>初回</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Measurement Data -->
          <BaseCard v-if="(selectedStudent && selectionMethod === 'individual') || (selectedStudents.length > 0 && selectionMethod === 'bulk')">
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">測定データ入力</h2>
            </template>

            <div class="space-y-6">
              <!-- Common Fields -->
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
                </div>
              </div>

              <!-- Individual Measurement -->
              <div v-if="selectionMethod === 'individual'" class="space-y-6">
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
                  </div>
                </div>

                <!-- BMI Display -->
                <div v-if="form.height && form.weight" class="bg-gray-50 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <span class="text-sm font-medium text-gray-700">BMI: </span>
                      <span class="text-2xl font-bold" :class="getBMIColor(calculatedBMI)">
                        {{ calculatedBMI }}
                      </span>
                      <BaseBadge :variant="getBMIVariant(calculatedBMI)" class="ml-2">
                        {{ getBMICategory(calculatedBMI) }}
                      </BaseBadge>
                    </div>
                    <div v-if="selectedStudent?.latest_health_record" class="text-sm text-gray-500">
                      前回BMI: {{ selectedStudent.latest_health_record.bmi }}
                    </div>
                  </div>
                </div>

                <!-- Growth Indicators -->
                <div v-if="selectedStudent?.latest_health_record && form.height && form.weight" class="bg-blue-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-700 mb-2">成長記録</h4>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center">
                      <ArrowUpIcon v-if="heightGrowth > 0" class="h-4 w-4 text-green-500 mr-2" />
                      <ArrowDownIcon v-else-if="heightGrowth < 0" class="h-4 w-4 text-red-500 mr-2" />
                      <span class="text-sm">
                        身長: {{ heightGrowth > 0 ? '+' : '' }}{{ heightGrowth }}cm
                      </span>
                    </div>
                    <div class="flex items-center">
                      <ArrowUpIcon v-if="weightGrowth > 0" class="h-4 w-4 text-green-500 mr-2" />
                      <ArrowDownIcon v-else-if="weightGrowth < 0" class="h-4 w-4 text-red-500 mr-2" />
                      <span class="text-sm">
                        体重: {{ weightGrowth > 0 ? '+' : '' }}{{ weightGrowth }}kg
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bulk Measurement -->
              <div v-else class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 mr-2" />
                    <div class="text-sm text-yellow-800">
                      <p class="font-medium">一括測定モード</p>
                      <p>各学生の身長・体重は個別に入力する必要があります。測定後に「詳細入力」ボタンから入力してください。</p>
                    </div>
                  </div>
                </div>
                
                <p class="text-sm text-gray-600">
                  選択された{{ selectedStudents.length }}名の学生に対して基本記録を作成し、後から個別に測定値を入力できます。
                </p>
              </div>

              <!-- Notes -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.notes"
                  label="備考"
                  placeholder="測定時の特記事項があれば記入してください"
                  rows="3"
                  :error="errors.notes"
                />
              </div>
            </div>
          </BaseCard>

          <!-- Form Actions -->
          <div v-if="(selectedStudent && selectionMethod === 'individual') || (selectedStudents.length > 0 && selectionMethod === 'bulk')" class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
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
            >
              {{ selectionMethod === 'individual' ? '記録を保存' : `${selectedStudents.length}件の記録を作成` }}
            </BaseButton>
          </div>
        </div>
      </form>
    </div>

    <!-- Right Sidebar -->
    <template #rightSidebar>
      <div class="space-y-6">
        <!-- Measurement Guide -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">
              <InformationCircleIcon class="h-5 w-5 inline mr-2 text-blue-500" />
              測定ガイド
            </h3>
          </template>
          
          <div class="space-y-4 text-sm text-gray-600">
            <div>
              <h4 class="font-medium text-gray-700 mb-2">身長測定</h4>
              <ul class="space-y-1 list-disc list-inside">
                <li>靴を脱いで測定</li>
                <li>背筋を伸ばして測定</li>
                <li>0.1cm単位で記録</li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium text-gray-700 mb-2">体重測定</h4>
              <ul class="space-y-1 list-disc list-inside">
                <li>軽装で測定</li>
                <li>体重計の中央に立つ</li>
                <li>0.1kg単位で記録</li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium text-gray-700 mb-2">BMI基準値</h4>
              <ul class="space-y-1">
                <li><span class="text-yellow-600">やせ:</span> 18.5未満</li>
                <li><span class="text-green-600">標準:</span> 18.5-24.9</li>
                <li><span class="text-yellow-600">肥満:</span> 25.0以上</li>
              </ul>
            </div>
          </div>
        </BaseCard>

        <!-- Quick Stats -->
        <BaseCard v-if="selectedStudent && selectionMethod === 'individual'">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">学生情報</h3>
          </template>
          
          <div class="space-y-3">
            <div class="text-center">
              <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                <span class="text-lg font-medium text-blue-700">
                  {{ selectedStudent.name.charAt(0) }}
                </span>
              </div>
              <h4 class="mt-2 font-medium text-gray-900">{{ selectedStudent.name }}</h4>
              <p class="text-sm text-gray-500">{{ selectedStudent.school_class?.name }}</p>
            </div>
            
            <div class="border-t pt-3 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">年齢</span>
                <span class="font-medium">{{ calculateAge(selectedStudent.birth_date) }}歳</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">性別</span>
                <span class="font-medium">{{ selectedStudent.gender === 'male' ? '男' : '女' }}</span>
              </div>
              <div v-if="selectedStudent.latest_health_record" class="flex justify-between text-sm">
                <span class="text-gray-500">前回測定</span>
                <span class="font-medium">{{ formatDate(selectedStudent.latest_health_record.measured_date) }}</span>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Bulk Summary -->
        <BaseCard v-if="selectedStudents.length > 0 && selectionMethod === 'bulk'">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">一括測定サマリー</h3>
          </template>
          
          <div class="space-y-3">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">{{ selectedStudents.length }}</div>
              <div class="text-sm text-gray-500">選択済み学生数</div>
            </div>
            
            <div class="border-t pt-3 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">年度</span>
                <span class="font-medium">{{ bulkFilters.academic_year }}年度</span>
              </div>
              <div v-if="bulkFilters.grade" class="flex justify-between">
                <span class="text-gray-500">学年</span>
                <span class="font-medium">{{ bulkFilters.grade }}年生</span>
              </div>
              <div v-if="selectedClass" class="flex justify-between">
                <span class="text-gray-500">クラス</span>
                <span class="font-medium">{{ selectedClass.name }}</span>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Recent Records -->
        <BaseCard v-if="recentRecords.length > 0">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">最近の記録</h3>
          </template>
          
          <div class="space-y-3">
            <div
              v-for="record in recentRecords.slice(0, 5)"
              :key="record.id"
              class="flex items-center space-x-3"
            >
              <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                <HeartIcon class="h-4 w-4 text-gray-600" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ record.student?.name }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(record.measured_date) }}</p>
              </div>
              <div class="text-xs text-gray-500">
                BMI {{ record.bmi }}
              </div>
            </div>
          </div>
        </BaseCard>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useStudentStore } from '@/stores/student.js';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseBadge
} from '@/components/index.js';

// Icons
const ChevronRightIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
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

const ArrowUpIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
    </svg>
  `
};

const ArrowDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
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

const HeartIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
  `
};

export default {
  name: 'HealthRecordCreate',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseBadge,
    ChevronRightIcon,
    MagnifyingGlassIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    HeartIcon
  },
  
  setup() {
    const router = useRouter();
    const route = useRoute();
    const healthRecordStore = useHealthRecordStore();
    const studentStore = useStudentStore();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isSubmitting = ref(false);
    const selectionMethod = ref('individual'); // 'individual' or 'bulk'
    const studentSearch = ref('');
    const selectedStudent = ref(null);
    const selectedStudentIds = ref([]);
    const searchResults = ref([]);
    
    // Form data
    const form = reactive({
      measured_date: new Date().toISOString().split('T')[0],
      academic_year: new Date().getFullYear(),
      height: '',
      weight: '',
      notes: ''
    });
    
    const bulkFilters = reactive({
      academic_year: new Date().getFullYear().toString(),
      grade: '',
      class_id: ''
    });
    
    // Errors
    const errors = ref({});
    
    // Computed
    const students = computed(() => studentStore.students);
    const classes = computed(() => classStore.classes);
    const healthRecords = computed(() => healthRecordStore.healthRecords);
    const currentYear = new Date().getFullYear();
    
    const academicYearOptions = computed(() => {
      return [currentYear - 1, currentYear, currentYear + 1];
    });
    
    const filteredClasses = computed(() => {
      if (!bulkFilters.grade) return classes.value;
      return classes.value.filter(c => c.grade.toString() === bulkFilters.grade);
    });
    
    const bulkStudents = computed(() => {
      let result = [...students.value];
      
      if (bulkFilters.grade) {
        result = result.filter(s => s.school_class?.grade?.toString() === bulkFilters.grade);
      }
      
      if (bulkFilters.class_id) {
        result = result.filter(s => s.school_class?.name === bulkFilters.class_id);
      }
      
      return result.sort((a, b) => a.name.localeCompare(b.name, 'ja'));
    });
    
    const selectedStudents = computed(() => {
      return students.value.filter(s => selectedStudentIds.value.includes(s.id));
    });
    
    const selectedClass = computed(() => {
      return classes.value.find(c => c.id.toString() === bulkFilters.class_id);
    });
    
    const allStudentsSelected = computed(() => {
      return bulkStudents.value.length > 0 && 
             selectedStudentIds.value.length === bulkStudents.value.length;
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
    
    const heightGrowth = computed(() => {
      if (selectedStudent.value?.latest_health_record && form.height) {
        return (parseFloat(form.height) - selectedStudent.value.latest_health_record.height).toFixed(1);
      }
      return 0;
    });
    
    const weightGrowth = computed(() => {
      if (selectedStudent.value?.latest_health_record && form.weight) {
        return (parseFloat(form.weight) - selectedStudent.value.latest_health_record.weight).toFixed(1);
      }
      return 0;
    });
    
    const recentRecords = computed(() => {
      return healthRecords.value
        .slice()
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date))
        .slice(0, 5);
    });
    
    // Methods
    const searchStudents = () => {
      if (!studentSearch.value.trim()) {
        searchResults.value = [];
        return;
      }
      
      const query = studentSearch.value.toLowerCase();
      searchResults.value = students.value
        .filter(student => 
          student.name.toLowerCase().includes(query) ||
          student.student_number.toLowerCase().includes(query)
        )
        .slice(0, 10);
    };
    
    const selectStudent = (student) => {
      selectedStudent.value = student;
      studentSearch.value = '';
      searchResults.value = [];
    };
    
    const clearSelectedStudent = () => {
      selectedStudent.value = null;
      form.height = '';
      form.weight = '';
    };
    
    const updateBulkSelection = () => {
      selectedStudentIds.value = [];
    };
    
    const toggleAllStudents = () => {
      if (allStudentsSelected.value) {
        selectedStudentIds.value = [];
      } else {
        selectedStudentIds.value = bulkStudents.value.map(s => s.id);
      }
    };
    
    const calculateBMI = () => {
      // BMI is calculated automatically via computed property
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
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric'
      });
    };
    
    const getBMICategory = (bmi) => {
      if (!bmi) return '未測定';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return 'やせ';
      if (bmiValue < 25) return '標準';
      if (bmiValue < 30) return '肥満';
      return '高度肥満';
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
    
    const validateForm = () => {
      errors.value = {};
      
      if (!form.measured_date) {
        errors.value.measured_date = '測定日は必須です';
      }
      
      if (!form.academic_year) {
        errors.value.academic_year = '年度を選択してください';
      }
      
      if (selectionMethod.value === 'individual') {
        if (!selectedStudent.value) {
          errors.value.student = '学生を選択してください';
          return false;
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
      } else {
        if (selectedStudentIds.value.length === 0) {
          errors.value.students = '学生を選択してください';
          return false;
        }
      }
      
      return Object.keys(errors.value).length === 0;
    };
    
    const resetForm = () => {
      form.measured_date = new Date().toISOString().split('T')[0];
      form.academic_year = new Date().getFullYear();
      form.height = '';
      form.weight = '';
      form.notes = '';
      
      selectedStudent.value = null;
      selectedStudentIds.value = [];
      studentSearch.value = '';
      searchResults.value = [];
      
      bulkFilters.academic_year = new Date().getFullYear().toString();
      bulkFilters.grade = '';
      bulkFilters.class_id = '';
      
      errors.value = {};
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
        if (selectionMethod.value === 'individual') {
          // Individual record creation
          const recordData = {
            student_id: selectedStudent.value.id,
            measured_date: form.measured_date,
            academic_year: parseInt(form.academic_year),
            height: parseFloat(form.height),
            weight: parseFloat(form.weight),
            bmi: parseFloat(calculatedBMI.value),
            notes: form.notes || null
          };
          
          const newRecord = await healthRecordStore.createHealthRecord(recordData);
          
          notificationStore.addNotification({
            type: 'success',
            title: '記録作成完了',
            message: `${selectedStudent.value.name}さんの健康記録を作成しました`
          });
          
          router.push(`/health-records/${newRecord.id}`);
        } else {
          // Bulk record creation
          const recordsData = selectedStudentIds.value.map(studentId => ({
            student_id: studentId,
            measured_date: form.measured_date,
            academic_year: parseInt(form.academic_year),
            height: null, // To be filled later
            weight: null, // To be filled later
            bmi: null, // To be calculated later
            notes: form.notes || null
          }));
          
          await healthRecordStore.createBulkHealthRecords(recordsData);
          
          notificationStore.addNotification({
            type: 'success',
            title: '一括記録作成完了',
            message: `${selectedStudentIds.value.length}名の健康記録を作成しました`
          });
          
          router.push('/health-records');
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '作成エラー',
          message: error.response?.data?.message || '健康記録の作成に失敗しました'
        });
      } finally {
        isSubmitting.value = false;
      }
    };
    
    // Watchers
    watch(() => bulkFilters.grade, () => {
      bulkFilters.class_id = '';
      updateBulkSelection();
    });
    
    // Lifecycle
    onMounted(async () => {
      try {
        await Promise.all([
          studentStore.fetchStudents(),
          classStore.fetchClasses(),
          healthRecordStore.fetchHealthRecords()
        ]);
        
        // Pre-select from query params
        if (route.query.student_id) {
          const student = students.value.find(s => s.id.toString() === route.query.student_id);
          if (student) {
            selectStudent(student);
          }
        }
        
        if (route.query.class_id) {
          const classId = route.query.class_id.toString();
          const schoolClass = classes.value.find(c => c.id.toString() === classId);
          if (schoolClass) {
            selectionMethod.value = 'bulk';
            bulkFilters.grade = schoolClass.grade.toString();
            bulkFilters.class_id = classId;
          }
        }
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '初期データの取得に失敗しました'
        });
      }
    });
    
    return {
      isSubmitting,
      selectionMethod,
      studentSearch,
      selectedStudent,
      selectedStudentIds,
      searchResults,
      form,
      bulkFilters,
      errors,
      academicYearOptions,
      filteredClasses,
      bulkStudents,
      selectedStudents,
      selectedClass,
      allStudentsSelected,
      calculatedBMI,
      heightGrowth,
      weightGrowth,
      recentRecords,
      searchStudents,
      selectStudent,
      clearSelectedStudent,
      updateBulkSelection,
      toggleAllStudents,
      calculateBMI,
      calculateAge,
      formatDate,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      resetForm,
      handleSubmit
    };
  }
};
</script>