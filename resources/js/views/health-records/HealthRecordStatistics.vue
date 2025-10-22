<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            健康記録統計・分析
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            学年別・クラス別の健康記録データを分析
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="$router.push('/health-records')"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            一覧に戻る
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-6">
      <!-- Filters -->
      <BaseCard>
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">年度</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-6">
            <div class="sm:col-span-1">
              <BaseSelect
                v-model="selectedYear"
                label=""
                @change="loadStatistics"
              >
                <option 
                  v-for="year in availableYears" 
                  :key="year"
                  :value="year"
                >
                  {{ year }}年度
                </option>
              </BaseSelect>
            </div>
            <div class="sm:col-span-3 flex items-end">
              <BaseButton
                variant="primary"
                @click="loadStatistics"
                :loading="isLoading"
              >
                <ChartBarIcon class="h-4 w-4 mr-2" />
                統計を更新
              </BaseButton>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">フィルターと表示設定</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
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
              <div>
                <BaseSelect
                  v-model="selectedClassId"
                  label="クラス"
                  @change="loadStatistics"
                  :disabled="!selectedGrade"
                >
                  <option value="">すべてのクラス</option>
                  <option 
                    v-for="cls in filteredClasses" 
                    :key="cls.class_id"
                    :value="cls.class_id"
                  >
                    {{ cls.class_name }}
                  </option>
                </BaseSelect>
                <p v-if="!selectedGrade" class="mt-1 text-sm text-gray-500">
                  ※ 学年を選択してください
                </p>
              </div>
            </div>
            
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">表示項目</label>
              <div class="flex flex-wrap gap-3">
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    v-model="displayItems.height"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                  <span class="ml-2 text-sm text-gray-700">身長</span>
                </label>
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    v-model="displayItems.weight"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                  <span class="ml-2 text-sm text-gray-700">体重</span>
                </label>
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    v-model="displayItems.bmi"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                  <span class="ml-2 text-sm text-gray-700">BMI</span>
                </label>
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    v-model="displayItems.vision"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                  <span class="ml-2 text-sm text-gray-700">視力</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Overall Stats Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4" v-if="statistics">
        <BaseCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <DocumentTextIcon class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">総記録数</p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ statistics.total_records }}件
                </p>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowUpIcon class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">平均身長</p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ statistics.averages.height }}cm
                </p>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ScaleIcon class="h-8 w-8 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">平均体重</p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ statistics.averages.weight }}kg
                </p>
              </div>
            </div>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <HeartIcon class="h-8 w-8 text-red-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">平均BMI</p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ statistics.averages.bmi }}
                </p>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Charts Section -->
      <div v-if="statistics" class="space-y-6">
        <!-- Grade and Class Averages Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Grade Averages -->
          <BaseCard>
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">学年平均</h3>
              <div v-if="statistics.by_grade && statistics.by_grade.length > 0" class="space-y-4">
                <div 
                  v-for="grade in statistics.by_grade" 
                  :key="grade.grade"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">{{ grade.grade_name }}</h4>
                    <span class="text-sm text-gray-500">({{ grade.count }}名)</span>
                  </div>
                  <div class="grid grid-cols-2 gap-3 text-sm">
                    <div v-if="displayItems.height">
                      <span class="text-gray-500">身長</span>
                      <p class="font-medium text-gray-900">{{ grade.avg_height }}cm</p>
                    </div>
                    <div v-if="displayItems.weight">
                      <span class="text-gray-500">体重</span>
                      <p class="font-medium text-gray-900">{{ grade.avg_weight }}kg</p>
                    </div>
                    <div v-if="displayItems.bmi">
                      <span class="text-gray-500">BMI</span>
                      <p class="font-medium text-gray-900">{{ grade.avg_bmi }}</p>
                    </div>
                    <div v-if="displayItems.vision">
                      <span class="text-gray-500">視力</span>
                      <p class="font-medium text-gray-900">
                        左{{ grade.avg_vision_left || '--' }} / 右{{ grade.avg_vision_right || '--' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>データがありません</p>
              </div>
            </div>
          </BaseCard>

          <!-- Class Averages -->
          <BaseCard>
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">クラス平均</h3>
              <div v-if="statistics.by_class && statistics.by_class.length > 0" class="space-y-4">
                <div 
                  v-for="cls in statistics.by_class" 
                  :key="cls.class_id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">{{ cls.class_name }}</h4>
                    <span class="text-sm text-gray-500">({{ cls.count }}名)</span>
                  </div>
                  <div class="grid grid-cols-2 gap-3 text-sm">
                    <div v-if="displayItems.height">
                      <span class="text-gray-500">身長</span>
                      <p class="font-medium text-gray-900">{{ cls.avg_height || '---' }}cm</p>
                    </div>
                    <div v-if="displayItems.weight">
                      <span class="text-gray-500">体重</span>
                      <p class="font-medium text-gray-900">{{ cls.avg_weight || '---' }}kg</p>
                    </div>
                    <div v-if="displayItems.bmi">
                      <span class="text-gray-500">BMI</span>
                      <p class="font-medium text-gray-900">{{ cls.avg_bmi || '---' }}</p>
                    </div>
                    <div v-if="displayItems.vision">
                      <span class="text-gray-500">視力</span>
                      <p class="font-medium text-gray-900">
                        左{{ cls.avg_vision_left || '--' }} / 右{{ cls.avg_vision_right || '--' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>データがありません</p>
              </div>
            </div>
          </BaseCard>
        </div>

        <!-- Height Chart -->
        <BaseCard v-if="displayItems.height">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              平均身長の比較
            </h3>
            <div class="h-80">
              <Bar
                :data="heightChartData"
                :options="chartOptions"
              />
            </div>
          </div>
        </BaseCard>

        <!-- Weight Chart -->
        <BaseCard v-if="displayItems.weight">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              平均体重の比較
            </h3>
            <div class="h-80">
              <Bar
                :data="weightChartData"
                :options="chartOptions"
              />
            </div>
          </div>
        </BaseCard>

        <!-- BMI Chart -->
        <BaseCard v-if="displayItems.bmi">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              平均BMIの比較
            </h3>
            <div class="h-80">
              <Bar
                :data="bmiChartData"
                :options="chartOptions"
              />
            </div>
          </div>
        </BaseCard>

        <!-- Vision Chart -->
        <BaseCard v-if="displayItems.vision">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              平均視力の比較
            </h3>
            <div class="h-80">
              <Bar
                :data="visionChartData"
                :options="visionChartOptions"
              />
            </div>
          </div>
        </BaseCard>

        <!-- BMI Distribution -->
        <BaseCard v-if="displayItems.bmi">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              BMI分布
            </h3>
            <div class="h-80">
              <Doughnut
                :data="bmiDistributionData"
                :options="doughnutOptions"
              />
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && !statistics" class="text-center py-12">
        <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">データがありません</h3>
        <p class="mt-1 text-sm text-gray-500">
          選択した年度のデータが見つかりませんでした。
        </p>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';
import { Bar, Doughnut } from 'vue-chartjs';
import {
  AppLayout,
  BaseCard,
  BaseButton,
  BaseSelect
} from '@/components/index.js';

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

// Icons
const ArrowLeftIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
  `
};

const ChartBarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
  `
};

const DocumentTextIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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

const ScaleIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
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
  name: 'HealthRecordStatistics',
  components: {
    AppLayout,
    BaseCard,
    BaseButton,
    BaseSelect,
    Bar,
    Doughnut,
    ArrowLeftIcon,
    ChartBarIcon,
    DocumentTextIcon,
    ArrowUpIcon,
    ScaleIcon,
    HeartIcon
  },

  setup() {
    const router = useRouter();
    const isLoading = ref(false);
    const statistics = ref(null);
    const selectedYear = ref(new Date().getFullYear());
    const selectedGrade = ref('');
    const selectedClassId = ref('');
    const classes = ref([]);
    
    // Display items
    const displayItems = ref({
      height: true,
      weight: true,
      bmi: false,
      vision: false
    });

    // Available years (current year and previous 5 years)
    const availableYears = computed(() => {
      const currentYear = new Date().getFullYear();
      return Array.from({ length: 6 }, (_, i) => currentYear - i);
    });

    // Filtered classes based on selected grade
    const filteredClasses = computed(() => {
      if (!selectedGrade.value || !classes.value.length) return [];
      return classes.value.filter(cls => cls.grade == selectedGrade.value);
    });

    // Chart options
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              return `${context.parsed.y}${context.dataset.label}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: false
        }
      }
    };

    const visionChartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 2.0
        }
      }
    };

    const doughnutOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right'
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.label || '';
              const value = context.parsed || 0;
              const percentage = statistics.value?.bmi_percentages?.[
                ['underweight', 'normal', 'overweight', 'obese'][context.dataIndex]
              ] || 0;
              return `${label}: ${value}件 (${percentage}%)`;
            }
          }
        }
      }
    };

    // Height Chart Data - Combined grade and class
    const heightChartData = computed(() => {
      if (!statistics.value) return { labels: [], datasets: [] };
      
      const gradeData = statistics.value.by_grade || [];
      const classData = statistics.value.by_class || [];
      
      return {
        labels: [
          ...gradeData.map(item => item.grade_name),
          ...classData.map(item => item.class_name)
        ],
        datasets: [
          {
            label: 'cm',
            data: [
              ...gradeData.map(item => item.avg_height),
              ...classData.map(item => item.avg_height)
            ],
            backgroundColor: [
              ...Array(gradeData.length).fill('rgba(59, 130, 246, 0.5)'),
              ...Array(classData.length).fill('rgba(16, 185, 129, 0.5)')
            ],
            borderColor: [
              ...Array(gradeData.length).fill('rgb(59, 130, 246)'),
              ...Array(classData.length).fill('rgb(16, 185, 129)')
            ],
            borderWidth: 1
          }
        ]
      };
    });

    // Weight Chart Data - Combined grade and class
    const weightChartData = computed(() => {
      if (!statistics.value) return { labels: [], datasets: [] };
      
      const gradeData = statistics.value.by_grade || [];
      const classData = statistics.value.by_class || [];
      
      return {
        labels: [
          ...gradeData.map(item => item.grade_name),
          ...classData.map(item => item.class_name)
        ],
        datasets: [
          {
            label: 'kg',
            data: [
              ...gradeData.map(item => item.avg_weight),
              ...classData.map(item => item.avg_weight)
            ],
            backgroundColor: [
              ...Array(gradeData.length).fill('rgba(168, 85, 247, 0.5)'),
              ...Array(classData.length).fill('rgba(236, 72, 153, 0.5)')
            ],
            borderColor: [
              ...Array(gradeData.length).fill('rgb(168, 85, 247)'),
              ...Array(classData.length).fill('rgb(236, 72, 153)')
            ],
            borderWidth: 1
          }
        ]
      };
    });

    // BMI Chart Data - Combined grade and class
    const bmiChartData = computed(() => {
      if (!statistics.value) return { labels: [], datasets: [] };
      
      const gradeData = statistics.value.by_grade || [];
      const classData = statistics.value.by_class || [];
      
      return {
        labels: [
          ...gradeData.map(item => item.grade_name),
          ...classData.map(item => item.class_name)
        ],
        datasets: [
          {
            label: '',
            data: [
              ...gradeData.map(item => item.avg_bmi),
              ...classData.map(item => item.avg_bmi)
            ],
            backgroundColor: [
              ...Array(gradeData.length).fill('rgba(239, 68, 68, 0.5)'),
              ...Array(classData.length).fill('rgba(245, 158, 11, 0.5)')
            ],
            borderColor: [
              ...Array(gradeData.length).fill('rgb(239, 68, 68)'),
              ...Array(classData.length).fill('rgb(245, 158, 11)')
            ],
            borderWidth: 1
          }
        ]
      };
    });

    // Vision Chart Data - Combined grade and class
    const visionChartData = computed(() => {
      if (!statistics.value) return { labels: [], datasets: [] };
      
      const gradeData = statistics.value.by_grade || [];
      const classData = statistics.value.by_class || [];
      
      return {
        labels: [
          ...gradeData.map(item => item.grade_name),
          ...classData.map(item => item.class_name)
        ],
        datasets: [
          {
            label: '左眼',
            data: [
              ...gradeData.map(item => item.avg_vision_left),
              ...classData.map(item => item.avg_vision_left)
            ],
            backgroundColor: 'rgba(34, 197, 94, 0.5)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
          },
          {
            label: '右眼',
            data: [
              ...gradeData.map(item => item.avg_vision_right),
              ...classData.map(item => item.avg_vision_right)
            ],
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
          }
        ]
      };
    });

    // BMI Distribution Data
    const bmiDistributionData = computed(() => {
      if (!statistics.value) return { labels: [], datasets: [] };
      
      return {
        labels: ['低体重', '標準', '肥満(1度)', '肥満(2度以上)'],
        datasets: [
          {
            data: [
              statistics.value.bmi_distribution.underweight,
              statistics.value.bmi_distribution.normal,
              statistics.value.bmi_distribution.overweight,
              statistics.value.bmi_distribution.obese
            ],
            backgroundColor: [
              'rgba(59, 130, 246, 0.5)',
              'rgba(34, 197, 94, 0.5)',
              'rgba(251, 191, 36, 0.5)',
              'rgba(239, 68, 68, 0.5)'
            ],
            borderColor: [
              'rgb(59, 130, 246)',
              'rgb(34, 197, 94)',
              'rgb(251, 191, 36)',
              'rgb(239, 68, 68)'
            ],
            borderWidth: 1
          }
        ]
      };
    });

    // Load classes data
    const loadClasses = async () => {
      try {
        const response = await axios.get('/api/v1/classes');
        if (response.data.success) {
          classes.value = response.data.data;
        }
      } catch (error) {
        console.error('クラス一覧の取得に失敗しました:', error);
      }
    };

    // Load statistics data
    const loadStatistics = async () => {
      isLoading.value = true;
      try {
        const params = {
          academic_year: selectedYear.value
        };
        
        if (selectedGrade.value) {
          params.grade = selectedGrade.value;
        }
        
        if (selectedClassId.value) {
          params.class_id = selectedClassId.value;
        }
        
        const response = await axios.get('/api/v1/health-records/statistics', { params });
        
        if (response.data.success) {
          statistics.value = response.data.data;
        }
      } catch (error) {
        console.error('統計データの取得に失敗しました:', error);
        statistics.value = null;
      } finally {
        isLoading.value = false;
      }
    };

    // Lifecycle
    onMounted(() => {
      loadClasses();
      loadStatistics();
    });

    // Watch for grade changes to reset class filter
    watch(selectedGrade, (newValue) => {
      // Reset class selection when grade changes
      selectedClassId.value = '';
      loadStatistics();
    });

    return {
      isLoading,
      statistics,
      selectedYear,
      selectedGrade,
      selectedClassId,
      classes,
      filteredClasses,
      displayItems,
      availableYears,
      chartOptions,
      visionChartOptions,
      doughnutOptions,
      heightChartData,
      weightChartData,
      bmiChartData,
      visionChartData,
      bmiDistributionData,
      loadStatistics
    };
  }
};
</script>
