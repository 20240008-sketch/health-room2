<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            ダッシュボード
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ formatDate(currentDate) }}の保健室システム概要
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <BaseButton
            variant="primary"
            @click="refreshData"
            :loading="isRefreshing"
          >
            <RefreshIcon class="h-4 w-4 mr-2" />
            更新
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Students -->
        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <UsersIcon class="h-6 w-6 text-blue-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    総学生数
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.totalStudents || 0 }}名
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <router-link
                to="/students"
                class="font-medium text-blue-600 hover:text-blue-500"
              >
                学生一覧を見る
              </router-link>
            </div>
          </div>
        </BaseCard>

        <!-- Total Classes -->
        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <BuildingIcon class="h-6 w-6 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    総クラス数
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.totalClasses || 0 }}クラス
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <router-link
                to="/classes"
                class="font-medium text-green-600 hover:text-green-500"
              >
                クラス一覧を見る
              </router-link>
            </div>
          </div>
        </BaseCard>

        <!-- Health Records Today -->
        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <HeartIcon class="h-6 w-6 text-red-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    本日の記録数
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.todayHealthRecords || 0 }}件
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <router-link
                to="/health-records"
                class="font-medium text-red-600 hover:text-red-500"
              >
                健康記録を見る
              </router-link>
            </div>
          </div>
        </BaseCard>

        <!-- Total Health Records -->
        <BaseCard class="overflow-hidden">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <DocumentIcon class="h-6 w-6 text-yellow-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    総記録数
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ statistics.totalHealthRecords || 0 }}件
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <router-link
                to="/health-records/statistics"
                class="font-medium text-yellow-600 hover:text-yellow-500"
              >
                統計を見る
              </router-link>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
        <!-- Recent Health Records -->
        <div class="xl:col-span-2">
          <BaseCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">
                  最近の健康記録
                </h2>
                <router-link
                  to="/health-records"
                  class="text-sm font-medium text-blue-600 hover:text-blue-500"
                >
                  すべて見る
                </router-link>
              </div>
            </template>

            <div class="flow-root">
              <ul v-if="recentHealthRecords.length > 0" class="-mb-8">
                <li
                  v-for="(record, recordIdx) in recentHealthRecords"
                  :key="record.id"
                >
                  <div class="relative pb-8">
                    <span
                      v-if="recordIdx !== recentHealthRecords.length - 1"
                      class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                      aria-hidden="true"
                    />
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                          <HeartIcon class="h-4 w-4 text-white" />
                        </span>
                      </div>
                      <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-center space-x-2">
                          <h3 class="text-sm font-medium text-gray-900">
                            {{ record.student?.name }}
                          </h3>
                          <BaseBadge variant="info" size="xs">
                            {{ record.student?.school_class?.name }}
                          </BaseBadge>
                        </div>
                        <div class="text-sm text-gray-500">
                          <p>身長: {{ record.height }}cm | 体重: {{ record.weight }}kg</p>
                          <p v-if="record.bmi" class="mt-1">
                            BMI: {{ record.bmi.toFixed(1) }}
                            <BaseBadge
                              :variant="getBmiStatus(record.bmi).variant"
                              size="xs"
                              class="ml-1"
                            >
                              {{ getBmiStatus(record.bmi).label }}
                            </BaseBadge>
                          </p>
                        </div>
                        <div class="text-xs text-gray-400">
                          {{ formatDateTime(record.recorded_date) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
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
                    @click="$router.push('/health-records/create')"
                  >
                    <PlusIcon class="h-4 w-4 mr-2" />
                    記録を追加
                  </BaseButton>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="space-y-8">
          <!-- Quick Actions -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">
                クイックアクション
              </h2>
            </template>

            <div class="space-y-3">
              <BaseButton
                variant="primary"
                size="sm"
                class="w-full justify-start"
                @click="$router.push('/students/create')"
              >
                <PlusIcon class="h-4 w-4 mr-2" />
                学生を追加
              </BaseButton>
              <BaseButton
                variant="success"
                size="sm"
                class="w-full justify-start"
                @click="$router.push('/health-records/create')"
              >
                <HeartIcon class="h-4 w-4 mr-2" />
                健康記録を追加
              </BaseButton>
              <BaseButton
                variant="secondary"
                size="sm"
                class="w-full justify-start"
                @click="$router.push('/classes/create')"
              >
                <BuildingIcon class="h-4 w-4 mr-2" />
                クラスを作成
              </BaseButton>
              <BaseButton
                variant="info"
                size="sm"
                class="w-full justify-start"
                @click="$router.push('/reports/health-checkup')"
              >
                <DocumentIcon class="h-4 w-4 mr-2" />
                レポート作成
              </BaseButton>
            </div>
          </BaseCard>

          <!-- BMI Distribution -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">
                BMI分布
              </h2>
            </template>

            <div class="space-y-4">
              <div
                v-for="category in bmiDistribution"
                :key="category.label"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-3 h-3 rounded-full mr-3',
                      category.color
                    ]"
                  />
                  <span class="text-sm text-gray-700">{{ category.label }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium text-gray-900">
                    {{ category.count }}名
                  </span>
                  <span class="text-xs text-gray-500">
                    ({{ category.percentage }}%)
                  </span>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useMainStore } from '@/stores/main.js';
import { useStudentStore } from '@/stores/student.js';
import { useClassStore } from '@/stores/class.js';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useStatisticsStore } from '@/stores/statistics.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseButton,
  BaseBadge
} from '@/components/index.js';

// Icons
const RefreshIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
  `
};

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
    </svg>
  `
};

const BuildingIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
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

const DocumentIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
  name: 'Dashboard',
  components: {
    AppLayout,
    BaseCard,
    BaseButton,
    BaseBadge,
    RefreshIcon,
    UsersIcon,
    BuildingIcon,
    HeartIcon,
    DocumentIcon,
    PlusIcon
  },
  
  setup() {
    const mainStore = useMainStore();
    const studentStore = useStudentStore();
    const classStore = useClassStore();
    const healthRecordStore = useHealthRecordStore();
    const statisticsStore = useStatisticsStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isRefreshing = ref(false);
    const currentDate = ref(new Date());
    
    // Computed
    const statistics = computed(() => statisticsStore.dashboardStats);
    const recentHealthRecords = computed(() => healthRecordStore.recentRecords);
    const bmiDistribution = computed(() => {
      const dist = statisticsStore.bmiDistribution;
      const total = dist.underweight + dist.normal + dist.overweight + dist.obese;
      
      return [
        {
          label: '低体重',
          count: dist.underweight,
          percentage: total > 0 ? Math.round((dist.underweight / total) * 100) : 0,
          color: 'bg-blue-400'
        },
        {
          label: '標準',
          count: dist.normal,
          percentage: total > 0 ? Math.round((dist.normal / total) * 100) : 0,
          color: 'bg-green-400'
        },
        {
          label: '過体重',
          count: dist.overweight,
          percentage: total > 0 ? Math.round((dist.overweight / total) * 100) : 0,
          color: 'bg-yellow-400'
        },
        {
          label: '肥満',
          count: dist.obese,
          percentage: total > 0 ? Math.round((dist.obese / total) * 100) : 0,
          color: 'bg-red-400'
        }
      ];
    });
    
    // Methods
    const formatDate = (date) => {
      return new Intl.DateTimeFormat('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
      }).format(date);
    };
    
    const formatDateTime = (dateString) => {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('ja-JP', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
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
    
    const refreshData = async () => {
      isRefreshing.value = true;
      
      try {
        await Promise.all([
          statisticsStore.fetchDashboardStats(),
          healthRecordStore.fetchRecentRecords(),
          statisticsStore.fetchBmiDistribution()
        ]);
        
        notificationStore.addNotification({
          type: 'success',
          title: 'データ更新完了',
          message: 'ダッシュボードのデータを更新しました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ更新エラー',
          message: 'データの更新に失敗しました'
        });
      } finally {
        isRefreshing.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      await refreshData();
    });
    
    return {
      isRefreshing,
      currentDate,
      statistics,
      recentHealthRecords,
      bmiDistribution,
      formatDate,
      formatDateTime,
      getBmiStatus,
      refreshData
    };
  }
};
</script>