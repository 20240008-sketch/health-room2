<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <span class="text-sm font-medium text-gray-500">健康記録管理</span>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            健康記録一覧
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            学生の健康測定データを管理します
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
            variant="secondary"
            @click="exportHealthRecords"
          >
            <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
            エクスポート
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="$router.push('/health-records/create')"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            新しい記録
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <!-- Search -->
          <div class="sm:col-span-2">
            <div class="flex">
              <BaseInput
                v-model="searchQuery"
                placeholder="学生名・学生番号で検索..."
                @keyup.enter="handleSearch"
                @input="currentPage = 1"
                class="flex-1"
              >
                <template #prepend>
                  <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                </template>
              </BaseInput>
              <BaseButton
                variant="primary"
                @click="handleSearch"
                class="ml-2"
              >
                <MagnifyingGlassIcon class="h-4 w-4 mr-1" />
                検索
              </BaseButton>
            </div>
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

          <!-- Class Filter -->
          <div>
            <BaseInput
              type="select"
              v-model="filters.class_id"
              placeholder="クラスを選択"
              @change="applyFilters"
            >
              <option value="">すべてのクラス</option>
              <option
                v-for="schoolClass in allClasses"
                :key="schoolClass.id"
                :value="schoolClass.class_id"
              >
                {{ schoolClass.class_name }}
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

        <!-- Date Filters -->
        <div class="mt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">測定日で絞り込み</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <!-- Year -->
            <div>
              <BaseInput
                type="select"
                v-model="filters.year"
                label="年"
                @change="applyFilters"
              >
                <option value="">全て</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}年
                </option>
              </BaseInput>
            </div>
            
            <!-- Month -->
            <div>
              <BaseInput
                type="select"
                v-model="filters.month"
                label="月"
                @change="applyFilters"
              >
                <option value="">全て</option>
                <option v-for="month in 12" :key="month" :value="month">
                  {{ month }}月
                </option>
              </BaseInput>
            </div>
            
            <!-- Day -->
            <div>
              <BaseInput
                type="select"
                v-model="filters.day"
                label="日"
                @change="applyFilters"
              >
                <option value="">全て</option>
                <option v-for="day in availableDays" :key="day" :value="day">
                  {{ day }}日
                </option>
              </BaseInput>
            </div>
          </div>
        </div>

        <!-- Display Items Selection -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <h4 class="text-sm font-medium text-gray-700 mb-3">表示項目</h4>
          <div class="flex flex-wrap gap-4">
            <label class="inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="displayColumns.height"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
              />
              <span class="ml-2 text-sm text-gray-700">身長</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="displayColumns.weight"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
              />
              <span class="ml-2 text-sm text-gray-700">体重</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="displayColumns.vision"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
              />
              <span class="ml-2 text-sm text-gray-700">視力</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="displayColumns.bmi"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
              />
              <span class="ml-2 text-sm text-gray-700">BMI</span>
            </label>
          </div>
        </div>
      </div>
    </BaseCard>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <BaseCard>
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <HeartIcon class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  総記録数
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.totalRecords }}件
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
              <CalendarIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  今月の測定
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.thisMonthRecords }}件
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
              <ScaleIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  平均BMI
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.averageBMI }}
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
              <ChartBarIcon class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  測定対象学生
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.uniqueStudents }}人
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Health Records List -->
    <BaseCard>
      <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-medium text-gray-900">
            {{ filteredHealthRecords.length }}件の健康記録
          </h3>
          <div class="flex items-center space-x-2">
            <!-- View Toggle -->
            <div class="flex bg-gray-100 rounded-lg p-1">
              <button
                @click="viewMode = 'table'"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                  viewMode === 'table'
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-900'
                ]"
              >
                <Bars3Icon class="h-4 w-4" />
              </button>
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
            </div>

            <!-- Sort -->
            <BaseInput
              type="select"
              v-model="sortBy"
              @change="applySorting"
              class="w-48"
            >
              <option value="measured_date">測定日順</option>
              <option value="student_name">学生名順</option>
              <option value="bmi">BMI順</option>
              <option value="height">身長順</option>
              <option value="weight">体重順</option>
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
        v-else-if="filteredHealthRecords.length === 0"
        class="text-center py-12"
      >
        <HeartIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          健康記録が見つかりません
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasActiveFilters ? '検索条件を変更してください' : '最初の健康記録を作成しましょう' }}
        </p>
        <div class="mt-6" v-if="!hasActiveFilters">
          <BaseButton
            variant="primary"
            @click="$router.push('/health-records/create')"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            新しい記録
          </BaseButton>
        </div>
      </div>

      <!-- Table View -->
      <div v-else-if="viewMode === 'table'" class="overflow-hidden">
        <BaseTable
          :columns="tableColumns"
          :data="paginatedRecords"
          :loading="isLoading"
          @row-click="(row) => $router.push(`/health-records/${row.id}`)"
        >
          <template #cell(student_name)="{ item }">
            <div class="space-y-1">
              <div class="font-medium text-gray-900">
                {{ item.student?.name || '不明' }}
              </div>
              <div class="text-xs text-gray-500">
                出席番号: {{ item.student?.student_number || '未設定' }}
              </div>
            </div>
          </template>

          <template #cell(class_grade)="{ item }">
            <div class="text-sm text-gray-600">
              {{ item.student?.class_id || '未設定' }} - {{ item.student?.grade_id || '?' }}年
            </div>
          </template>

          <template #cell(measured_date)="{ item }">
            <div class="text-sm text-gray-900">
              {{ formatDate(item.measured_date) }}
            </div>
          </template>

          <template #cell(height)="{ item }">
            <div class="text-sm font-medium text-gray-900">
              {{ item.height || '-' }}
            </div>
          </template>

          <template #cell(weight)="{ item }">
            <div class="text-sm font-medium text-gray-900">
              {{ item.weight || '-' }}
            </div>
          </template>

          <template #cell(vision)="{ item }">
            <div class="text-sm text-gray-900">
              {{ formatVision(item) }}
            </div>
          </template>

          <template #cell(bmi)="{ item }">
            <div class="text-sm font-medium" :class="getBMIColor(item.bmi)">
              {{ item.bmi || '-' }}
            </div>
          </template>

          <template #actions="{ item }">
            <div class="flex space-x-1">
              <BaseButton
                size="sm"
                variant="secondary"
                @click.stop="$router.push(`/health-records/${item.id}/edit`)"
              >
                編集
              </BaseButton>
              <BaseButton
                size="sm"
                variant="primary"
                @click.stop="$router.push(`/health-records/${item.id}`)"
              >
                詳細
              </BaseButton>
              <BaseButton
                size="sm"
                variant="danger"
                @click.stop="confirmDelete(item)"
              >
                削除
              </BaseButton>
            </div>
          </template>
        </BaseTable>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200">
          <BasePagination
            :currentPage="currentPage"
            :totalPages="totalPages"
            :total="filteredHealthRecords.length"
            :itemsPerPage="itemsPerPage"
            @page-changed="currentPage = $event"
          />
        </div>
      </div>

      <!-- Grid View -->
      <div
        v-else
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 p-6"
      >
        <div
          v-for="record in paginatedRecords"
          :key="record.id"
          class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          @click="$router.push(`/health-records/${record.id}`)"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  {{ record.student?.name }}
                </h3>
                <p class="text-sm text-gray-500">
                  出席番号: {{ record.student?.student_number || '未設定' }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ record.student?.class_id }} | {{ record.student?.grade_id }}年
                </p>
              </div>
              <BaseBadge v-if="displayColumns.bmi" :variant="getBMIVariant(record.bmi)">
                BMI {{ record.bmi }}
              </BaseBadge>
            </div>

            <!-- Measurements -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div v-if="displayColumns.height" class="text-center">
                <div class="text-2xl font-bold text-gray-900">
                  {{ record.height || '-' }}
                </div>
                <div class="text-sm text-gray-500">身長 (cm)</div>
              </div>
              <div v-if="displayColumns.weight" class="text-center">
                <div class="text-2xl font-bold text-gray-900">
                  {{ record.weight || '-' }}
                </div>
                <div class="text-sm text-gray-500">体重 (kg)</div>
              </div>
              <!-- 視力 -->
              <div v-if="displayColumns.vision" class="text-center">
                <div class="text-lg font-bold text-gray-900">
                  {{ formatVision(record) }}
                </div>
                <div class="text-sm text-gray-500">視力（左/右）</div>
              </div>
            </div>

            <!-- Growth indicators -->
            <div v-if="record.height_growth || record.weight_growth" class="mb-4">
              <div class="text-xs text-gray-500 mb-1">前回から</div>
              <div class="flex justify-center space-x-4">
                <div v-if="record.height_growth" class="flex items-center text-sm">
                  <ArrowUpIcon v-if="record.height_growth > 0" class="h-4 w-4 text-green-500 mr-1" />
                  <ArrowDownIcon v-else class="h-4 w-4 text-red-500 mr-1" />
                  <span>{{ Math.abs(record.height_growth) }}cm</span>
                </div>
                <div v-if="record.weight_growth" class="flex items-center text-sm">
                  <ArrowUpIcon v-if="record.weight_growth > 0" class="h-4 w-4 text-green-500 mr-1" />
                  <ArrowDownIcon v-else class="h-4 w-4 text-red-500 mr-1" />
                  <span>{{ Math.abs(record.weight_growth) }}kg</span>
                </div>
              </div>
            </div>

            <!-- Date -->
            <div class="text-center text-sm text-gray-500 mb-4">
              {{ formatDate(record.measured_date) }}
            </div>

            <!-- Actions -->
            <div class="flex justify-center space-x-2">
              <BaseButton
                size="sm"
                variant="secondary"
                @click.stop="$router.push(`/health-records/${record.id}/edit`)"
              >
                編集
              </BaseButton>
              <BaseButton
                size="sm"
                variant="primary"
                @click.stop="$router.push(`/health-records/${record.id}`)"
              >
                詳細
              </BaseButton>
              <BaseButton
                size="sm"
                variant="danger"
                @click.stop="confirmDelete(record)"
              >
                削除
              </BaseButton>
            </div>
          </div>
        </div>

        <!-- Grid Pagination -->
        <div class="col-span-full">
          <BasePagination
            :currentPage="currentPage"
            :totalPages="totalPages"
            :total="filteredHealthRecords.length"
            :pageSize="itemsPerPage"
            @page-changed="currentPage = $event"
            class="justify-center"
          />
        </div>
      </div>
    </BaseCard>
    
    <!-- Delete Confirmation Modal -->
    <BaseModal
      :show="showDeleteModal"
      @close="closeDeleteModal"
      title="健康記録の削除確認"
      size="md"
    >
      <div class="space-y-4">
        <p class="text-gray-700">
          本当に削除してもよろしいですか？削除した記録は元に戻せません。
        </p>
        
        <div v-if="recordToDelete" class="bg-gray-50 p-4 rounded-lg space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">生徒名:</span>
            <span class="font-medium">{{ recordToDelete.student?.name }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">測定日:</span>
            <span class="font-medium">{{ formatDate(recordToDelete.measured_date) }}</span>
          </div>
          <div v-if="recordToDelete.height" class="flex items-center justify-between">
            <span class="text-sm text-gray-600">身長:</span>
            <span>{{ recordToDelete.height }} cm</span>
          </div>
          <div v-if="recordToDelete.weight" class="flex items-center justify-between">
            <span class="text-sm text-gray-600">体重:</span>
            <span>{{ recordToDelete.weight }} kg</span>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="closeDeleteModal"
            :disabled="isDeleting"
          >
            キャンセル
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="deleteRecord"
            :disabled="isDeleting"
          >
            <BaseSpinner v-if="isDeleting" size="sm" class="mr-2" />
            削除する
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, onActivated, reactive } from 'vue';
import { useHealthRecordStore } from '@/stores/healthRecord.js';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import axios from 'axios';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseSpinner,
  BaseTable,
  BaseBadge,
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

const MagnifyingGlassIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z"/>
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

const CalendarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
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

const ChartBarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
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

const DocumentArrowDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
    </svg>
  `
};

export default {
  name: 'HealthRecordIndex',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseSpinner,
    BaseTable,
    BaseBadge,
    BasePagination,
    BaseModal,
    PlusIcon,
    MagnifyingGlassIcon,
    HeartIcon,
    CalendarIcon,
    ScaleIcon,
    ChartBarIcon,
    ViewColumnsIcon,
    Bars3Icon,
    ArrowUpIcon,
    ArrowDownIcon,
    DocumentArrowDownIcon
  },
  
  setup() {
    const healthRecordStore = useHealthRecordStore();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const searchQuery = ref('');
    const sortBy = ref('measured_date');
    const viewMode = ref('table'); // 'table' or 'grid'
    const currentPage = ref(1);
    const itemsPerPage = ref(20);
    
    // Delete modal state
    const showDeleteModal = ref(false);
    const recordToDelete = ref(null);
    const isDeleting = ref(false);
    
    const filters = reactive({
      academic_year: '',
      class_id: '',
      year: '',
      month: '',
      day: ''
    });
    
    // Display columns control
    const displayColumns = reactive({
      height: true,
      weight: true,
      vision: true,
      bmi: true
    });
    
    // Computed
    const healthRecords = computed(() => healthRecordStore.healthRecords);
    const classes = computed(() => classStore.classes);
    
    const availableAcademicYears = computed(() => {
      const years = healthRecords.value.map(r => r.academic_year);
      return [...new Set(years)].sort((a, b) => b - a);
    });
    
    const availableYears = computed(() => {
      const years = healthRecords.value.map(record => {
        const date = new Date(record.measured_date);
        return date.getFullYear();
      });
      return [...new Set(years)].sort((a, b) => b - a);
    });
    
    const availableDays = computed(() => {
      let maxDays = 31;
      if (filters.month) {
        const month = parseInt(filters.month);
        if ([4, 6, 9, 11].includes(month)) {
          maxDays = 30;
        } else if (month === 2) {
          maxDays = 29; // うるう年も考慮して29日まで
        }
      }
      return Array.from({ length: maxDays }, (_, i) => i + 1);
    });
    
    const allClasses = computed(() => {
      return classes.value;
    });
    
    const filteredHealthRecords = computed(() => {
      let result = [...healthRecords.value];
      
      console.log('Total health records:', result.length);
      if (result.length > 0) {
        console.log('First record sample:', {
          id: result[0].id,
          student_id: result[0].student_id,
          student: result[0].student,
          student_name: result[0].student?.name,
          student_number: result[0].student?.student_number
        });
      }
      
      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase().trim();
        console.log('Searching for:', query);
        console.log('Total records before search:', result.length);
        result = result.filter(record => {
          const name = record.student?.name?.toLowerCase() || '';
          const studentNumber = String(record.student?.student_number || '').toLowerCase();
          const matches = name.includes(query) || studentNumber.includes(query);
          if (matches) {
            console.log('Match found:', {
              name: record.student?.name,
              number: record.student?.student_number,
              query: query
            });
          }
          return matches;
        });
        console.log('Records after search:', result.length);
      }
      
      // Academic year filter
      if (filters.academic_year) {
        result = result.filter(record => record.academic_year.toString() === filters.academic_year);
      }
      
      // Class filter
      if (filters.class_id) {
        result = result.filter(record => 
          record.student?.class_id?.toString() === filters.class_id
        );
      }
      
      // Date filters (year/month/day)
      if (filters.year) {
        result = result.filter(record => {
          const date = new Date(record.measured_date);
          return date.getFullYear().toString() === filters.year;
        });
      }
      
      if (filters.month) {
        result = result.filter(record => {
          const date = new Date(record.measured_date);
          return (date.getMonth() + 1).toString() === filters.month;
        });
      }
      
      if (filters.day) {
        result = result.filter(record => {
          const date = new Date(record.measured_date);
          return date.getDate().toString() === filters.day;
        });
      }
      
      // Sorting
      result.sort((a, b) => {
        switch (sortBy.value) {
          case 'measured_date':
            return new Date(b.measured_date) - new Date(a.measured_date);
          case 'student_name':
            return (a.student?.name || '').localeCompare(b.student?.name || '', 'ja');
          case 'bmi':
            return (b.bmi || 0) - (a.bmi || 0);
          case 'height':
            return (b.height || 0) - (a.height || 0);
          case 'weight':
            return (b.weight || 0) - (a.weight || 0);
          default:
            return 0;
        }
      });
      
      return result;
    });
    
    const paginatedRecords = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      const paginated = filteredHealthRecords.value.slice(start, end);
      console.log('Pagination:', {
        currentPage: currentPage.value,
        itemsPerPage: itemsPerPage.value,
        start: start,
        end: end,
        filteredTotal: filteredHealthRecords.value.length,
        paginatedCount: paginated.length
      });
      return paginated;
    });
    
    const totalPages = computed(() => {
      return Math.ceil(filteredHealthRecords.value.length / itemsPerPage.value);
    });
    
    const stats = computed(() => {
      const records = filteredHealthRecords.value;
      const totalRecords = records.length;
      
      const now = new Date();
      const thisMonth = records.filter(r => {
        const recordDate = new Date(r.measured_date);
        return recordDate.getFullYear() === now.getFullYear() &&
               recordDate.getMonth() === now.getMonth();
      });
      const thisMonthRecords = thisMonth.length;
      
      const validBMIs = records.filter(r => r.bmi && r.bmi > 0).map(r => r.bmi);
      const averageBMI = validBMIs.length > 0 
        ? (validBMIs.reduce((sum, bmi) => sum + bmi, 0) / validBMIs.length).toFixed(1)
        : '0.0';
      
      const uniqueStudents = new Set(records.map(r => r.student_id)).size;
      
      return {
        totalRecords,
        thisMonthRecords,
        averageBMI,
        uniqueStudents
      };
    });
    
    const hasActiveFilters = computed(() => {
      return searchQuery.value || 
             filters.academic_year || 
             filters.class_id ||
             filters.year ||
             filters.month ||
             filters.day;
    });
    
    const tableColumns = computed(() => {
      const baseColumns = [
        { key: 'student_name', title: '学生名・出席番号', label: '学生名・出席番号', sortable: true, width: '180px' },
        { key: 'class_grade', title: 'クラス・学年', label: 'クラス・学年', width: '120px' },
        { key: 'measured_date', title: '測定日', label: '測定日', sortable: true, width: '100px' }
      ];
      
      const dataColumns = [];
      if (displayColumns.height) {
        dataColumns.push({ key: 'height', title: '身長(cm)', label: '身長(cm)', sortable: true, width: '80px' });
      }
      if (displayColumns.weight) {
        dataColumns.push({ key: 'weight', title: '体重(kg)', label: '体重(kg)', sortable: true, width: '80px' });
      }
      if (displayColumns.vision) {
        dataColumns.push({ key: 'vision', title: '視力(左/右)', label: '視力(左/右)', width: '100px' });
      }
      if (displayColumns.bmi) {
        dataColumns.push({ key: 'bmi', title: 'BMI', label: 'BMI', sortable: true, width: '80px' });
      }
      
      return [
        ...baseColumns,
        ...dataColumns,
        { key: 'actions', title: '操作', label: '操作', width: '180px' }
      ];
    });
    
    // Methods
    const handleSearch = () => {
      currentPage.value = 1;
    };
    
    const applyFilters = () => {
      console.log('Applying filters:', {
        class_id: filters.class_id,
        academic_year: filters.academic_year
      });
      console.log('Available classes:', classes.value.length);
      console.log('Total health records:', healthRecords.value.length);
      console.log('Filtered health records:', filteredHealthRecords.value.length);
      currentPage.value = 1;
    };
    
    const applySorting = () => {
      currentPage.value = 1;
    };
    
    const resetFilters = () => {
      searchQuery.value = '';
      filters.academic_year = '';
      filters.class_id = '';
      filters.year = '';
      filters.month = '';
      filters.day = '';
      sortBy.value = 'measured_date';
      currentPage.value = 1;
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
      if (bmi < 18.5) return 'やせ';
      if (bmi < 25) return '標準';
      if (bmi < 30) return '肥満';
      return '高度肥満';
    };
    
    const getBMIVariant = (bmi) => {
      if (!bmi) return 'secondary';
      if (bmi < 18.5) return 'warning';
      if (bmi < 25) return 'success';
      if (bmi < 30) return 'warning';
      return 'danger';
    };
    
    const getBMIColor = (bmi) => {
      if (!bmi) return 'text-gray-900';
      if (bmi < 18.5) return 'text-yellow-600';
      if (bmi < 25) return 'text-green-600';
      if (bmi < 30) return 'text-yellow-600';
      return 'text-red-600';
    };
    
    const getVisionGrade = (vision) => {
      if (!vision) return '-';
      const v = parseFloat(vision);
      if (v >= 1.0) return 'A';
      if (v >= 0.7) return 'B';
      if (v >= 0.3) return 'C';
      return 'D';
    };
    
    const formatVision = (record) => {
      if (!record) return '-/-';
      
      // 左目
      let leftVision = '-';
      let leftIsCorrected = false;
      if (record.vision_left) {
        leftVision = getVisionGrade(record.vision_left);
      } else if (record.vision_left_corrected) {
        leftVision = getVisionGrade(record.vision_left_corrected);
        leftIsCorrected = true;
      }
      
      // 右目
      let rightVision = '-';
      let rightIsCorrected = false;
      if (record.vision_right) {
        rightVision = getVisionGrade(record.vision_right);
      } else if (record.vision_right_corrected) {
        rightVision = getVisionGrade(record.vision_right_corrected);
        rightIsCorrected = true;
      }
      
      // 矯正視力の場合は「矯」を付ける
      if (leftIsCorrected) leftVision = '矯' + leftVision;
      if (rightIsCorrected) rightVision = '矯' + rightVision;
      
      return leftVision + '/' + rightVision;
    };
    
    const exportToPDF = async () => {
      try {
        notificationStore.addNotification({
          type: 'info',
          title: 'PDF出力中',
          message: '健康記録のPDFを生成しています...'
        });

        // Build query parameters based on current filters
        const params = new URLSearchParams();
        if (filters.academic_year) params.append('academic_year', filters.academic_year);
        if (filters.class_id) params.append('class_id', filters.class_id);
        if (filters.year) params.append('year', filters.year);
        if (filters.month) params.append('month', filters.month);
        if (filters.day) params.append('day', filters.day);
        if (searchQuery.value) params.append('search', searchQuery.value);

        const response = await fetch(`/api/v1/health-records/export-pdf?${params.toString()}`, {
          method: 'GET',
        });

        if (!response.ok) {
          throw new Error('PDF生成に失敗しました');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `健康記録一覧_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        notificationStore.addNotification({
          type: 'success',
          title: 'PDF出力完了',
          message: '健康記録のPDFをダウンロードしました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'PDF出力エラー',
          message: error.message || 'PDFの生成に失敗しました'
        });
      }
    };
    
    const exportHealthRecords = () => {
      // Implement CSV export functionality
      const csvContent = [
        ['測定日', '学生番号', '氏名', 'クラス', '身長', '体重', 'BMI', 'BMI分類'],
        ...filteredHealthRecords.value.map(record => [
          record.measured_date,
          record.student?.student_number || '',
          record.student?.name || '',
          record.student?.school_class?.name || '',
          record.height,
          record.weight,
          record.bmi,
          getBMICategory(record.bmi)
        ])
      ].map(row => row.join(',')).join('\n');
      
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `健康記録一覧_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      notificationStore.addNotification({
        type: 'success',
        title: 'エクスポート完了',
        message: '健康記録をCSVファイルでダウンロードしました'
      });
    };
    
    // Delete functions
    const confirmDelete = (record) => {
      recordToDelete.value = record;
      showDeleteModal.value = true;
    };
    
    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      recordToDelete.value = null;
      isDeleting.value = false;
    };
    
    const deleteRecord = async () => {
      if (!recordToDelete.value) return;
      
      isDeleting.value = true;
      
      try {
        await axios.delete(`/api/v1/health-records/${recordToDelete.value.id}`);
        
        notificationStore.addNotification({
          type: 'success',
          title: '削除完了',
          message: '健康記録を削除しました'
        });
        
        // Refresh the health records list
        await healthRecordStore.fetchHealthRecords();
        
        closeDeleteModal();
      } catch (error) {
        console.error('削除エラー:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: '削除エラー',
          message: '健康記録の削除に失敗しました'
        });
        isDeleting.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      try {
        await Promise.all([
          healthRecordStore.fetchHealthRecords(),
          classStore.fetchClasses()
        ]);
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '健康記録の取得に失敗しました'
        });
      } finally {
        isLoading.value = false;
      }
    });
    
    // ページがアクティブになった時（他のページから戻ってきた時）にデータを再取得
    onActivated(async () => {
      try {
        await healthRecordStore.fetchHealthRecords();
      } catch (error) {
        console.error('データ再取得エラー:', error);
      }
    });
    
    return {
      isLoading,
      searchQuery,
      sortBy,
      viewMode,
      currentPage,
      itemsPerPage,
      filters,
      displayColumns,
      filteredHealthRecords,
      paginatedRecords,
      totalPages,
      stats,
      hasActiveFilters,
      tableColumns,
      availableAcademicYears,
      availableYears,
      availableDays,
      allClasses,
      handleSearch,
      applyFilters,
      applySorting,
      resetFilters,
      formatDate,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      getVisionGrade,
      formatVision,
      exportToPDF,
      exportHealthRecords,
      showDeleteModal,
      recordToDelete,
      isDeleting,
      confirmDelete,
      closeDeleteModal,
      deleteRecord
    };
  }
};
</script>