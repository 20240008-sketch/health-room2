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
                  <span class="ml-4 text-sm font-medium text-gray-500">記録詳細</span>
                </div>
              </li>
            </ol>
          </nav>
          <div class="mt-2 flex items-center space-x-5">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              健康測定記録
            </h1>
            <BaseBadge
              :variant="getBMIVariant(record?.bmi)"
              v-if="record?.bmi"
            >
              BMI {{ record.bmi }} ({{ getBMICategory(record.bmi) }})
            </BaseBadge>
          </div>
          <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <UserIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ record?.student?.name }}
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <CalendarIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ formatDate(record?.measured_date) }}
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <AcademicCapIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ record?.academic_year }}年度
            </div>
          </div>
        </div>
        <div class="mt-5 flex lg:mt-0 lg:ml-4">
          <BaseButton
            variant="secondary"
            @click="$router.push(`/health-records/${record?.id}/edit`)"
            v-if="record"
          >
            <PencilIcon class="h-4 w-4 mr-2" />
            編集
          </BaseButton>
          <BaseButton
            variant="secondary"
            class="ml-3"
            @click="exportRecord"
            v-if="record"
          >
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            エクスポート
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-7xl space-y-6" v-if="record">
      <!-- Main Record Card -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">測定データ</h2>
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">記録ID:</span>
              <span class="text-sm font-mono text-gray-900">#{{ record.id }}</span>
            </div>
          </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Student Info -->
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-2xl font-medium text-blue-700">
                    {{ record.student?.name?.charAt(0) }}
                  </span>
                </div>
              </div>
              <div>
                <h3 class="text-xl font-medium text-gray-900">{{ record.student?.name }}</h3>
                <p class="text-sm text-gray-500">{{ record.student?.student_number }}</p>
                <p class="text-sm text-gray-500">{{ record.student?.school_class?.name }}</p>
              </div>
            </div>
            
            <div class="space-y-3 pt-4 border-t border-gray-200">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">性別</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ record.student?.gender === '男' ? '男性' : record.student?.gender === '女' ? '女性' : record.student?.gender || '不明' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">生年月日</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ formatDate(record.student?.birth_date) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">測定時年齢</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ calculateAge(record.student?.birth_date, record.measured_date) }}歳
                </span>
              </div>
            </div>
          </div>

          <!-- Measurement Data -->
          <div class="space-y-4">
            <h4 class="font-medium text-gray-900">測定値</h4>
            
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-gray-500">身長</div>
                    <div class="text-2xl font-bold text-gray-900">
                      {{ record.height }} <span class="text-lg text-gray-500">cm</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <div v-if="heightGrowth !== null" class="flex items-center text-sm">
                      <ArrowUpIcon v-if="heightGrowth > 0" class="h-4 w-4 text-green-500 mr-1" />
                      <ArrowDownIcon v-else-if="heightGrowth < 0" class="h-4 w-4 text-red-500 mr-1" />
                      <span :class="heightGrowth > 0 ? 'text-green-600' : heightGrowth < 0 ? 'text-red-600' : 'text-gray-500'">
                        {{ heightGrowth > 0 ? '+' : '' }}{{ heightGrowth }}cm
                      </span>
                    </div>
                    <div class="text-xs text-gray-500">前回比</div>
                  </div>
                </div>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-gray-500">体重</div>
                    <div class="text-2xl font-bold text-gray-900">
                      {{ record.weight }} <span class="text-lg text-gray-500">kg</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <div v-if="weightGrowth !== null" class="flex items-center text-sm">
                      <ArrowUpIcon v-if="weightGrowth > 0" class="h-4 w-4 text-green-500 mr-1" />
                      <ArrowDownIcon v-else-if="weightGrowth < 0" class="h-4 w-4 text-red-500 mr-1" />
                      <span :class="weightGrowth > 0 ? 'text-green-600' : weightGrowth < 0 ? 'text-red-600' : 'text-gray-500'">
                        {{ weightGrowth > 0 ? '+' : '' }}{{ weightGrowth }}kg
                      </span>
                    </div>
                    <div class="text-xs text-gray-500">前回比</div>
                  </div>
                </div>
              </div>
              
              <div v-if="record.vision_left || record.vision_right" class="bg-gray-50 rounded-lg p-4">
                <div class="space-y-3">
                  <div class="text-sm text-gray-500">視力</div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <div class="text-xs text-gray-500 mb-1">左</div>
                      <div class="text-xl font-bold text-gray-900">
                        {{ record.vision_left || '-' }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 mb-1">右</div>
                      <div class="text-xl font-bold text-gray-900">
                        {{ record.vision_right || '-' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- BMI Analysis -->
          <div class="space-y-4">
            <h4 class="font-medium text-gray-900">BMI分析</h4>
            
            <div class="bg-white border-2 border-dashed rounded-lg p-6 text-center" 
                 :class="getBMIBorderColor(record.bmi)">
              <div class="text-4xl font-bold mb-2" :class="getBMIColor(record.bmi)">
                {{ record.bmi }}
              </div>
              <BaseBadge :variant="getBMIVariant(record.bmi)" class="mb-3">
                {{ getBMICategory(record.bmi) }}
              </BaseBadge>
              <div class="text-sm text-gray-600">
                {{ getBMIDescription(record.bmi) }}
              </div>
            </div>
            
            <!-- BMI History Chart -->
            <div v-if="bmiHistory.length > 1" class="space-y-2">
              <h5 class="text-sm font-medium text-gray-700">BMI推移</h5>
              <div class="h-32 bg-gray-50 rounded-lg flex items-end justify-center space-x-2 p-4">
                <div
                  v-for="(bmi, index) in bmiHistory.slice(-6)"
                  :key="index"
                  class="flex flex-col items-center"
                >
                  <div
                    class="w-8 rounded-t-lg"
                    :class="getBMIColor(bmi.bmi)"
                    :style="{ height: `${Math.max(10, (bmi.bmi / 35) * 80)}px`, backgroundColor: 'currentColor' }"
                  ></div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ formatShortDate(bmi.measured_date) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Growth Chart -->
      <BaseCard v-if="growthHistory.length > 1">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">成長記録</h2>
        </template>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Height Chart -->
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-4">身長の推移</h3>
            <div class="h-48 bg-gray-50 rounded-lg p-4">
              <div class="h-full flex items-end justify-center space-x-3">
                <div
                  v-for="(record, index) in growthHistory.slice(-8)"
                  :key="index"
                  class="flex flex-col items-center space-y-2"
                >
                  <div class="text-xs text-gray-600">
                    {{ record.height }}cm
                  </div>
                  <div
                    class="w-6 bg-blue-500 rounded-t"
                    :style="{ height: `${Math.max(20, ((record.height - minHeight) / (maxHeight - minHeight)) * 120)}px` }"
                  ></div>
                  <div class="text-xs text-gray-500 transform rotate-45 origin-left">
                    {{ formatShortDate(record.measured_date) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Weight Chart -->
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-4">体重の推移</h3>
            <div class="h-48 bg-gray-50 rounded-lg p-4">
              <div class="h-full flex items-end justify-center space-x-3">
                <div
                  v-for="(record, index) in growthHistory.slice(-8)"
                  :key="index"
                  class="flex flex-col items-center space-y-2"
                >
                  <div class="text-xs text-gray-600">
                    {{ record.weight }}kg
                  </div>
                  <div
                    class="w-6 bg-green-500 rounded-t"
                    :style="{ height: `${Math.max(20, ((record.weight - minWeight) / (maxWeight - minWeight)) * 120)}px` }"
                  ></div>
                  <div class="text-xs text-gray-500 transform rotate-45 origin-left">
                    {{ formatShortDate(record.measured_date) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Comparison with Peers -->
      <BaseCard v-if="peerComparison">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">同級生との比較</h2>
        </template>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ peerComparison.averageHeight?.toFixed(1) }}cm</div>
            <div class="text-sm text-gray-500">クラス平均身長</div>
            <div class="mt-2">
              <span class="text-sm" 
                    :class="heightDiffFromAverage > 0 ? 'text-green-600' : heightDiffFromAverage < 0 ? 'text-red-600' : 'text-gray-500'">
                {{ heightDiffFromAverage > 0 ? '+' : '' }}{{ heightDiffFromAverage?.toFixed(1) }}cm
              </span>
            </div>
          </div>
          
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ peerComparison.averageWeight?.toFixed(1) }}kg</div>
            <div class="text-sm text-gray-500">クラス平均体重</div>
            <div class="mt-2">
              <span class="text-sm" 
                    :class="weightDiffFromAverage > 0 ? 'text-green-600' : weightDiffFromAverage < 0 ? 'text-red-600' : 'text-gray-500'">
                {{ weightDiffFromAverage > 0 ? '+' : '' }}{{ weightDiffFromAverage?.toFixed(1) }}kg
              </span>
            </div>
          </div>
          
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ peerComparison.averageBMI?.toFixed(1) }}</div>
            <div class="text-sm text-gray-500">クラス平均BMI</div>
            <div class="mt-2">
              <span class="text-sm" 
                    :class="bmiDiffFromAverage > 0 ? 'text-green-600' : bmiDiffFromAverage < 0 ? 'text-red-600' : 'text-gray-500'">
                {{ bmiDiffFromAverage > 0 ? '+' : '' }}{{ bmiDiffFromAverage?.toFixed(1) }}
              </span>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Additional Info -->
      <BaseCard v-if="record.notes || relatedRecords.length > 0">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">追加情報</h2>
        </template>
        
        <div class="space-y-6">
          <!-- Notes -->
          <div v-if="record.notes">
            <h3 class="text-sm font-medium text-gray-700 mb-2">備考</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ record.notes }}</p>
            </div>
          </div>
          
          <!-- Related Records -->
          <div v-if="relatedRecords.length > 0">
            <h3 class="text-sm font-medium text-gray-700 mb-4">関連する測定記録</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="relatedRecord in relatedRecords"
                :key="relatedRecord.id"
                class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 cursor-pointer transition-colors"
                @click="$router.push(`/health-records/${relatedRecord.id}`)"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-900">
                    {{ formatDate(relatedRecord.measured_date) }}
                  </span>
                  <BaseBadge :variant="getBMIVariant(relatedRecord.bmi)" size="sm">
                    BMI {{ relatedRecord.bmi }}
                  </BaseBadge>
                </div>
                <div class="text-xs text-gray-500 space-y-1">
                  <div>身長: {{ relatedRecord.height }}cm</div>
                  <div>体重: {{ relatedRecord.weight }}kg</div>
                  <div v-if="relatedRecord.vision_left || relatedRecord.vision_right">
                    視力: {{ relatedRecord.vision_left || '-' }} / {{ relatedRecord.vision_right || '-' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>
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
        <!-- Quick Actions -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">
              <CogIcon class="h-5 w-5 inline mr-2 text-gray-500" />
              クイックアクション
            </h3>
          </template>
          
          <div class="space-y-3">
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/health-records/${record.id}/edit`)"
            >
              <PencilIcon class="h-4 w-4 mr-2" />
              記録を編集
            </BaseButton>
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="exportRecord"
            >
              <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
              データをエクスポート
            </BaseButton>
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/students/${record.student_id}`)"
            >
              <UserIcon class="h-4 w-4 mr-2" />
              学生詳細を表示
            </BaseButton>
          </div>
        </BaseCard>

        <!-- BMI Reference -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">BMI基準値</h3>
          </template>
          
          <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center py-1">
              <span>やせ</span>
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                <span class="text-gray-600">&lt; 18.5</span>
              </div>
            </div>
            <div class="flex justify-between items-center py-1">
              <span>標準</span>
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                <span class="text-gray-600">18.5 - 24.9</span>
              </div>
            </div>
            <div class="flex justify-between items-center py-1">
              <span>肥満1度</span>
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                <span class="text-gray-600">25.0 - 29.9</span>
              </div>
            </div>
            <div class="flex justify-between items-center py-1">
              <span>肥満2度以上</span>
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                <span class="text-gray-600">&geq; 30.0</span>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Record History -->
        <BaseCard v-if="studentRecords.length > 1">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">測定履歴</h3>
          </template>
          
          <div class="space-y-3">
            <div
              v-for="historyRecord in studentRecords.slice(0, 5)"
              :key="historyRecord.id"
              class="flex items-center justify-between p-2 rounded hover:bg-gray-50 cursor-pointer"
              :class="{ 'bg-blue-50 border border-blue-200': historyRecord.id === record.id }"
              @click="$router.push(`/health-records/${historyRecord.id}`)"
            >
              <div>
                <div class="text-sm font-medium text-gray-900">
                  {{ formatShortDate(historyRecord.measured_date) }}
                </div>
                <div class="text-xs text-gray-500">
                  <div>{{ historyRecord.height }}cm / {{ historyRecord.weight }}kg</div>
                  <div v-if="historyRecord.vision_left || historyRecord.vision_right">
                    視力: {{ historyRecord.vision_left || '-' }} / {{ historyRecord.vision_right || '-' }}
                  </div>
                </div>
              </div>
              <BaseBadge :variant="getBMIVariant(historyRecord.bmi)" size="sm">
                {{ historyRecord.bmi }}
              </BaseBadge>
            </div>
            <div v-if="studentRecords.length > 5" class="text-center pt-2">
              <router-link 
                :to="`/students/${record.student_id}/health-records`"
                class="text-sm text-blue-600 hover:text-blue-500"
              >
                すべての記録を表示 ({{ studentRecords.length }}件)
              </router-link>
            </div>
          </div>
        </BaseCard>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useStudentStore } from '@/stores/student.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
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

const AcademicCapIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7a7 7 0 00-7-7"/>
    </svg>
  `
};

const PencilIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
    </svg>
  `
};

const ArrowDownTrayIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
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

const CogIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
  `
};

export default {
  name: 'HealthRecordShow',
  components: {
    AppLayout,
    BaseCard,
    BaseButton,
    BaseBadge,
    BaseSpinner,
    ChevronRightIcon,
    UserIcon,
    CalendarIcon,
    AcademicCapIcon,
    PencilIcon,
    ArrowDownTrayIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ExclamationTriangleIcon,
    CogIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const healthRecordStore = useHealthRecordStore();
    const studentStore = useStudentStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const record = ref(null);
    const studentRecords = ref([]);
    const peerComparison = ref(null);
    
    // Computed
    const recordId = computed(() => parseInt(route.params.id));
    
    const previousRecord = computed(() => {
      const sorted = studentRecords.value
        .filter(r => new Date(r.measured_date) < new Date(record.value?.measured_date))
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date));
      return sorted[0] || null;
    });
    
    const nextRecord = computed(() => {
      const sorted = studentRecords.value
        .filter(r => new Date(r.measured_date) > new Date(record.value?.measured_date))
        .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date));
      return sorted[0] || null;
    });
    
    const heightGrowth = computed(() => {
      if (!record.value || !previousRecord.value) return null;
      return (record.value.height - previousRecord.value.height).toFixed(1);
    });
    
    const weightGrowth = computed(() => {
      if (!record.value || !previousRecord.value) return null;
      return (record.value.weight - previousRecord.value.weight).toFixed(1);
    });
    
    const bmiHistory = computed(() => {
      return studentRecords.value
        .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date));
    });
    
    const growthHistory = computed(() => {
      return studentRecords.value
        .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date));
    });
    
    const minHeight = computed(() => {
      const heights = growthHistory.value.map(r => r.height);
      return Math.min(...heights) - 5;
    });
    
    const maxHeight = computed(() => {
      const heights = growthHistory.value.map(r => r.height);
      return Math.max(...heights) + 5;
    });
    
    const minWeight = computed(() => {
      const weights = growthHistory.value.map(r => r.weight);
      return Math.min(...weights) - 5;
    });
    
    const maxWeight = computed(() => {
      const weights = growthHistory.value.map(r => r.weight);
      return Math.max(...weights) + 5;
    });
    
    const heightDiffFromAverage = computed(() => {
      if (!record.value || !peerComparison.value) return null;
      return record.value.height - peerComparison.value.averageHeight;
    });
    
    const weightDiffFromAverage = computed(() => {
      if (!record.value || !peerComparison.value) return null;
      return record.value.weight - peerComparison.value.averageWeight;
    });
    
    const bmiDiffFromAverage = computed(() => {
      if (!record.value || !peerComparison.value) return null;
      return record.value.bmi - peerComparison.value.averageBMI;
    });
    
    const relatedRecords = computed(() => {
      return studentRecords.value
        .filter(r => r.id !== record.value?.id)
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date))
        .slice(0, 6);
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
    
    const calculateAge = (birthDate, measuredDate) => {
      if (!birthDate || !measuredDate) return 0;
      
      const birth = new Date(birthDate);
      const measured = new Date(measuredDate);
      let age = measured.getFullYear() - birth.getFullYear();
      const monthDiff = measured.getMonth() - birth.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && measured.getDate() < birth.getDate())) {
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
    
    const getBMIBorderColor = (bmi) => {
      if (!bmi) return 'border-gray-300';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return 'border-yellow-300';
      if (bmiValue < 25) return 'border-green-300';
      if (bmiValue < 30) return 'border-yellow-300';
      return 'border-red-300';
    };
    
    const getBMIDescription = (bmi) => {
      if (!bmi) return '';
      const bmiValue = parseFloat(bmi);
      if (bmiValue < 18.5) return '体重が少なめです。栄養バランスを確認してください。';
      if (bmiValue < 25) return '適正範囲内です。現状を維持しましょう。';
      if (bmiValue < 30) return '少し体重が多めです。運動や食事に注意しましょう。';
      return '医師に相談することをお勧めします。';
    };
    
    const exportRecord = () => {
      if (!record.value) return;
      
      const data = {
        記録ID: record.value.id,
        学生名: record.value.student?.name,
        学生番号: record.value.student?.student_number,
        クラス: record.value.student?.school_class?.name,
        測定日: record.value.measured_date,
        年度: record.value.academic_year,
        身長: `${record.value.height}cm`,
        体重: `${record.value.weight}kg`,
        BMI: record.value.bmi,
        BMI分類: getBMICategory(record.value.bmi),
        備考: record.value.notes || ''
      };
      
      const csv = Object.entries(data)
        .map(([key, value]) => `${key},${value}`)
        .join('\n');
      
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `health_record_${record.value.id}_${record.value.student?.name}.csv`;
      link.click();
      
      notificationStore.addNotification({
        type: 'success',
        title: 'エクスポート完了',
        message: '健康記録をCSVファイルでダウンロードしました'
      });
    };
    
    const fetchRecord = async () => {
      try {
        isLoading.value = true;
        
        // Fetch health record
        const healthRecord = await healthRecordStore.fetchHealthRecord(recordId.value);
        if (!healthRecord) {
          record.value = null;
          return;
        }
        
        record.value = healthRecord;
        
        // Fetch student records for comparison
        if (healthRecord.student_id) {
          studentRecords.value = await healthRecordStore.getHealthRecordsByStudent(healthRecord.student_id);
        }
        
        // Fetch peer comparison data
        if (healthRecord.student?.class_id) {
          peerComparison.value = await healthRecordStore.getPeerComparison(
            healthRecord.student.class_id,
            healthRecord.academic_year
          );
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
      record,
      studentRecords,
      peerComparison,
      previousRecord,
      nextRecord,
      heightGrowth,
      weightGrowth,
      bmiHistory,
      growthHistory,
      minHeight,
      maxHeight,
      minWeight,
      maxWeight,
      heightDiffFromAverage,
      weightDiffFromAverage,
      bmiDiffFromAverage,
      relatedRecords,
      formatDate,
      formatShortDate,
      calculateAge,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      getBMIBorderColor,
      getBMIDescription,
      exportRecord
    };
  }
};
</script>