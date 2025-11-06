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
                      {{ displayHeight || '-' }} <span v-if="displayHeight" class="text-lg text-gray-500">cm</span>
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
                      {{ displayWeight || '-' }} <span v-if="displayWeight" class="text-lg text-gray-500">kg</span>
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
              
              <div v-if="displayVisionLeft || displayVisionRight" class="bg-gray-50 rounded-lg p-4">
                <div class="space-y-3">
                  <div class="text-sm text-gray-500">視力</div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <div class="text-xs text-gray-500 mb-1">左</div>
                      <div class="text-xl font-bold text-gray-900">
                        {{ displayVisionLeft || '-' }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 mb-1">右</div>
                      <div class="text-xl font-bold text-gray-900">
                        {{ displayVisionRight || '-' }}
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
            
            <div v-if="displayBMI" class="bg-white border-2 border-dashed rounded-lg p-6 text-center" 
                 :class="getBMIBorderColor(displayBMI)">
              <div class="text-4xl font-bold mb-2" :class="getBMIColor(displayBMI)">
                {{ displayBMI }}
              </div>
              <BaseBadge :variant="getBMIVariant(displayBMI)" class="mb-3">
                {{ getBMICategory(displayBMI) }}
              </BaseBadge>
              <div class="text-sm text-gray-600">
                {{ getBMIDescription(displayBMI) }}
              </div>
            </div>
            <div v-else class="bg-gray-50 border-2 border-dashed rounded-lg p-6 text-center">
              <div class="text-lg text-gray-500">未測定</div>
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

      <!-- 検査結果 -->
      <BaseCard v-if="hasExamResults">
        <template #header>
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-base font-medium text-gray-900">検査結果（全{{ filteredExamResults.length }}件）</h2>
              <p class="mt-0.5 text-xs text-gray-500">この生徒のすべての検診結果を新しい順に表示しています</p>
            </div>
            <div class="relative">
              <BaseButton
                variant="secondary"
                size="sm"
                @click="showExamTypeFilter = !showExamTypeFilter"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                表示する項目を選択
                <span v-if="hasActiveExamFilter" class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ activeExamFilterCount }}
                </span>
              </BaseButton>
              
              <!-- Filter Dropdown -->
              <div
                v-if="showExamTypeFilter"
                class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
              >
                <div class="p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900">検査種別</h3>
                    <button
                      @click="clearExamTypeFilter"
                      class="text-xs text-blue-600 hover:text-blue-500"
                    >
                      クリア
                    </button>
                  </div>
                  <div class="space-y-2">
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.ophthalmology"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">眼科検診</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.otolaryngology"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">耳鼻科検診</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.internal_medicine"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">内科検診</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.hearing_test"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">聴力検査</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.tuberculosis_test"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">結核検査</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.urine_test"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">尿検査</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                      <input
                        type="checkbox"
                        v-model="examTypeFilter.ecg"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">心電図</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        
        <div class="space-y-2">
          <!-- 全ての検診結果を表示 -->
          <div 
            v-for="(exam, index) in filteredExamResults" 
            :key="`exam-${exam.recordId}-${index}`"
            class="pl-3 bg-gray-50 rounded p-2 border-l-4"
            :class="getBorderColorClass(exam.color)"
          >
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-xs font-semibold text-gray-900">{{ exam.type }}</h3>
              <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">{{ exam.date }}</span>
                <router-link 
                  :to="`/health-records/${exam.recordId}`"
                  class="text-xs text-blue-600 hover:text-blue-500"
                >
                  #{{ exam.recordId }}
                </router-link>
                <button
                  @click="openExamEditModal(exam)"
                  class="text-xs text-green-600 hover:text-green-500 font-medium"
                >
                  編集
                </button>
              </div>
            </div>
            
            <!-- 内科検診 -->
            <div v-if="exam.type === '内科検診'" class="grid grid-cols-2 gap-2 text-xs">
              <div v-if="exam.data.exam_result">
                <span class="text-gray-500">検診:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.exam_result }}</span>
              </div>
              <div v-if="exam.data.diagnosis">
                <span class="text-gray-500">診断:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.diagnosis }}</span>
              </div>
            </div>
            
            <!-- 耳鼻科検診 -->
            <div v-if="exam.type === '耳鼻科検診'" class="grid grid-cols-5 gap-2 text-xs">
              <div v-if="exam.data.category">
                <span class="text-gray-500">分類:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.category }}</span>
              </div>
              <div v-if="exam.data.exam_result">
                <span class="text-gray-500">検診:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.exam_result }}</span>
              </div>
              <div v-if="exam.data.findings">
                <span class="text-gray-500">所見:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.findings }}</span>
              </div>
              <div v-if="exam.data.diagnosis">
                <span class="text-gray-500">診断:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.diagnosis }}</span>
              </div>
              <div v-if="exam.data.treatment">
                <span class="text-gray-500">処置:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.treatment }}</span>
              </div>
            </div>
            
            <!-- 眼科検診 -->
            <div v-if="exam.type === '眼科検診'">
              <div class="grid grid-cols-3 gap-2 text-xs">
                <div v-if="exam.data.exam_result">
                  <span class="text-gray-500">検診:</span>
                  <span class="font-medium text-gray-900 ml-1">{{ exam.data.exam_result }}</span>
                </div>
                <div v-if="exam.data.diagnosis">
                  <span class="text-gray-500">診断:</span>
                  <span class="font-medium text-gray-900 ml-1">{{ exam.data.diagnosis }}</span>
                </div>
                <div v-if="exam.data.treatment">
                  <span class="text-gray-500">処置:</span>
                  <span class="font-medium text-gray-900 ml-1">{{ exam.data.treatment }}</span>
                </div>
              </div>
              <div v-if="exam.data.result" class="mt-1.5">
                <span class="text-xs text-gray-500">備考:</span>
                <span class="text-xs text-gray-700 ml-1">{{ exam.data.result }}</span>
              </div>
            </div>
            
            <!-- 聴力検査・結核検査・尿検査・心電図 -->
            <div v-if="['聴力検査', '結核検査', '尿検査', '心電図'].includes(exam.type)" class="grid grid-cols-3 gap-2 text-xs">
              <div v-if="exam.data.exam_result">
                <span class="text-gray-500">検診:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.exam_result }}</span>
              </div>
              <div v-if="exam.data.diagnosis">
                <span class="text-gray-500">診断:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.diagnosis }}</span>
              </div>
              <div v-if="exam.data.treatment">
                <span class="text-gray-500">処置:</span>
                <span class="font-medium text-gray-900 ml-1">{{ exam.data.treatment }}</span>
              </div>
            </div>
          </div>
          
          <!-- 検診結果がない場合 -->
          <div v-if="allExamResults.length === 0" class="text-center py-4 text-sm text-gray-500">
            検診結果はまだ記録されていません
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

      <!-- Growth History -->
      <BaseCard v-if="growthHistory.length > 1">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">身長・体重の推移</h2>
        </template>
        
        <div class="space-y-6">
          <!-- Summary Stats -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Height Growth -->
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-blue-900">身長の変化</h3>
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
              </div>
              <div class="space-y-2">
                <div class="flex items-baseline justify-between">
                  <span class="text-2xl font-bold text-blue-900">{{ displayHeight }}cm</span>
                  <span class="text-sm text-blue-600">現在</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">初回測定</span>
                  <span class="font-medium text-gray-900">{{ growthHistory[0].height }}cm</span>
                </div>
                <div class="flex items-center justify-between text-sm pt-2 border-t border-blue-200">
                  <span class="text-gray-600">総成長</span>
                  <span class="font-bold text-green-600">
                    +{{ (displayHeight - growthHistory[0].height).toFixed(1) }}cm
                  </span>
                </div>
              </div>
            </div>

            <!-- Weight Growth -->
            <div class="bg-green-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-green-900">体重の変化</h3>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
              </div>
              <div class="space-y-2">
                <div class="flex items-baseline justify-between">
                  <span class="text-2xl font-bold text-green-900">{{ displayWeight }}kg</span>
                  <span class="text-sm text-green-600">現在</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">初回測定</span>
                  <span class="font-medium text-gray-900">{{ growthHistory[0].weight }}kg</span>
                </div>
                <div class="flex items-center justify-between text-sm pt-2 border-t border-green-200">
                  <span class="text-gray-600">総変化</span>
                  <span class="font-bold" :class="(displayWeight - growthHistory[0].weight) >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ (displayWeight - growthHistory[0].weight) >= 0 ? '+' : '' }}{{ (displayWeight - growthHistory[0].weight).toFixed(1) }}kg
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Timeline Chart -->
          <div class="border-t pt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-4">測定履歴タイムライン</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">測定日</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">身長 (cm)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">変化</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">体重 (kg)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">変化</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BMI</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(rec, index) in growthHistory" :key="rec.id" 
                      :class="rec.id === record.id ? 'bg-blue-50' : ''">
                    <td class="px-4 py-3 text-sm text-gray-900">
                      {{ formatDate(rec.measured_date) }}
                      <span v-if="rec.id === record.id" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        現在
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ rec.height || '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      <span v-if="index > 0 && rec.height && growthHistory[index - 1].height" 
                            :class="(rec.height - growthHistory[index - 1].height) > 0 ? 'text-green-600' : (rec.height - growthHistory[index - 1].height) < 0 ? 'text-red-600' : 'text-gray-500'">
                        {{ (rec.height - growthHistory[index - 1].height) > 0 ? '+' : '' }}{{ (rec.height - growthHistory[index - 1].height).toFixed(1) }}
                      </span>
                      <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ rec.weight || '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      <span v-if="index > 0 && rec.weight && growthHistory[index - 1].weight" 
                            :class="(rec.weight - growthHistory[index - 1].weight) > 0 ? 'text-green-600' : (rec.weight - growthHistory[index - 1].weight) < 0 ? 'text-red-600' : 'text-gray-500'">
                        {{ (rec.weight - growthHistory[index - 1].weight) > 0 ? '+' : '' }}{{ (rec.weight - growthHistory[index - 1].weight).toFixed(1) }}
                      </span>
                      <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                      <span v-if="rec.bmi" :class="getBMIColor(rec.bmi)">
                        {{ rec.bmi }}
                      </span>
                      <span v-else class="text-gray-400">-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Visual Growth Chart -->
          <div class="border-t pt-6" v-if="growthHistory.length > 2">
            <h3 class="text-sm font-medium text-gray-700 mb-4">成長グラフ（直近6回）</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Height Chart -->
              <div>
                <div class="text-xs text-gray-500 mb-2">身長 (cm)</div>
                <div class="flex items-end space-x-2 h-40 border-b border-l border-gray-300 pl-2 pb-2">
                  <div v-for="rec in growthHistory.slice(-6)" :key="'h-' + rec.id" 
                       class="flex-1 flex flex-col items-center justify-end">
                    <div class="w-full bg-blue-500 rounded-t transition-all hover:bg-blue-600"
                         :style="{ height: `${((rec.height - minHeight) / (maxHeight - minHeight)) * 100}%` }"
                         :title="`${rec.height}cm (${formatDate(rec.measured_date)})`">
                    </div>
                    <div class="text-xs font-medium text-blue-900 mt-1">{{ rec.height }}</div>
                    <div class="text-xs text-gray-500">
                      {{ formatShortDate(rec.measured_date) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Weight Chart -->
              <div>
                <div class="text-xs text-gray-500 mb-2">体重 (kg)</div>
                <div class="flex items-end space-x-2 h-40 border-b border-l border-gray-300 pl-2 pb-2">
                  <div v-for="rec in growthHistory.slice(-6)" :key="'w-' + rec.id" 
                       class="flex-1 flex flex-col items-center justify-end">
                    <div class="w-full bg-green-500 rounded-t transition-all hover:bg-green-600"
                         :style="{ height: `${((rec.weight - minWeight) / (maxWeight - minWeight)) * 100}%` }"
                         :title="`${rec.weight}kg (${formatDate(rec.measured_date)})`">
                    </div>
                    <div class="text-xs font-medium text-green-900 mt-1">{{ rec.weight }}</div>
                    <div class="text-xs text-gray-500">
                      {{ formatShortDate(rec.measured_date) }}
                    </div>
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
    
    <!-- 検診データ編集モーダル -->
    <Teleport to="body">
      <div
        v-if="showExamEditModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="closeExamEditModal"
      >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <!-- Backdrop -->
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeExamEditModal"></div>
          
          <!-- Modal -->
          <div class="relative inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">
                  {{ editingExam?.type }} - 編集
                </h3>
                <button
                  @click="closeExamEditModal"
                  class="text-white hover:text-gray-200 transition-colors"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <p class="mt-1 text-sm text-blue-100">{{ editingExam?.date }}</p>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
              <!-- 耳鼻科検診 -->
              <div v-if="editingExam?.type === '耳鼻科検診'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">分類</label>
                  <select
                    v-model="editFormData.category"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="異常なし">異常なし</option>
                    <option value="未検診">未検診</option>
                    <option value="耳">耳</option>
                    <option value="鼻">鼻</option>
                    <option value="喉">喉</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                  <select
                    v-model="editFormData.exam_result"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="!editFormData.category || editFormData.category === '異常なし' || editFormData.category === '未検診'"
                  >
                    <option value="">選択してください</option>
                    <template v-if="editFormData.category === '耳'">
                      <option value="耳垢">耳垢</option>
                      <option value="外耳炎">外耳炎</option>
                      <option value="滲出性中耳炎">滲出性中耳炎</option>
                      <option value="慢性中耳炎">慢性中耳炎</option>
                      <option value="難聴の疑い">難聴の疑い</option>
                      <option value="外耳道異物">外耳道異物</option>
                      <option value="中耳炎">中耳炎</option>
                    </template>
                    <template v-else-if="editFormData.category === '鼻'">
                      <option value="アレルギー性鼻炎">アレルギー性鼻炎</option>
                      <option value="慢性鼻炎">慢性鼻炎</option>
                      <option value="副鼻腔炎">副鼻腔炎</option>
                      <option value="鼻中隔わん曲症">鼻中隔わん曲症</option>
                      <option value="扁桃肥大">扁桃肥大</option>
                    </template>
                    <template v-else-if="editFormData.category === '喉'">
                      <option value="慢性扁桃炎">慢性扁桃炎</option>
                      <option value="アデノイド">アデノイド</option>
                      <option value="言語異常">言語異常</option>
                      <option value="口内炎">口内炎</option>
                      <option value="舌小帯異常">舌小帯異常</option>
                      <option value="舌異常">舌異常</option>
                      <option value="さ声">さ声</option>
                    </template>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">所見</label>
                  <select
                    v-model="editFormData.findings"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                  <select
                    v-model="editFormData.diagnosis"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="!editFormData.category || editFormData.category === '異常なし' || editFormData.category === '未検診'"
                  >
                    <option value="">選択してください</option>
                    <template v-if="editFormData.category === '耳'">
                      <option value="異常なし">異常なし</option>
                      <option value="耳垢栓塞">耳垢栓塞</option>
                      <option value="滲出性中耳炎">滲出性中耳炎</option>
                      <option value="慢性中耳炎鼓膜穿孔">慢性中耳炎鼓膜穿孔</option>
                      <option value="伝音性難聴">伝音性難聴</option>
                      <option value="感音性難聴">感音性難聴</option>
                    </template>
                    <template v-else-if="editFormData.category === '鼻'">
                      <option value="異常なし">異常なし</option>
                      <option value="慢性鼻炎">慢性鼻炎</option>
                      <option value="アレルギー性鼻炎">アレルギー性鼻炎</option>
                      <option value="副鼻腔炎">副鼻腔炎</option>
                      <option value="鼻中隔わん曲症">鼻中隔わん曲症</option>
                      <option value="急性鼻炎">急性鼻炎</option>
                      <option value="鼻カタル">鼻カタル</option>
                      <option value="鼻炎">鼻炎</option>
                      <option value="併合性鼻副鼻腔炎">併合性鼻副鼻腔炎</option>
                      <option value="鼻出血">鼻出血</option>
                      <option value="肥厚性鼻炎">肥厚性鼻炎</option>
                    </template>
                    <template v-else-if="editFormData.category === '喉'">
                      <option value="異常なし">異常なし</option>
                      <option value="扁桃肥大">扁桃肥大</option>
                      <option value="扁桃炎">扁桃炎</option>
                      <option value="アデノイド">アデノイド</option>
                      <option value="音声言語異常">音声言語異常</option>
                      <option value="口内炎">口内炎</option>
                      <option value="舌小帯異常">舌小帯異常</option>
                      <option value="舌異常">舌異常</option>
                    </template>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">処置</label>
                  <select
                    v-model="editFormData.treatment"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="!editFormData.category || editFormData.category === '異常なし' || editFormData.category === '未検診'"
                  >
                    <option value="">選択してください</option>
                    <option value="必要なし">必要なし</option>
                    <option value="治療中">治療中</option>
                    <option value="経過観察">経過観察</option>
                    <option value="治療完了">治療完了</option>
                  </select>
                </div>
              </div>
              
              <!-- 内科検診 -->
              <div v-if="editingExam?.type === '内科検診'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                  <select
                    v-model="editFormData.exam_result"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="異常なし">異常なし</option>
                    <option value="未検診">未検診</option>
                    <option value="心雑音の疑い">心雑音の疑い</option>
                    <option value="肥満">肥満</option>
                    <option value="やせ">やせ</option>
                    <option value="アトピー性皮膚炎">アトピー性皮膚炎</option>
                    <option value="貧血の疑い">貧血の疑い</option>
                    <option value="喘息">喘息</option>
                    <option value="低身長の疑い">低身長の疑い</option>
                    <option value="要精検">要精検</option>
                    <option value="不整脈">不整脈</option>
                    <option value="徐脈">徐脈</option>
                    <option value="その他">その他</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                  <select
                    v-model="editFormData.diagnosis"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="異常なし">異常なし</option>
                    <option value="心雑音">心雑音</option>
                    <option value="肥満">肥満</option>
                    <option value="思春期やせ症">思春期やせ症</option>
                    <option value="脊柱側わん症">脊柱側わん症</option>
                    <option value="胸郭変形">胸郭変形</option>
                    <option value="アトピー性皮膚炎">アトピー性皮膚炎</option>
                    <option value="貧血">貧血</option>
                    <option value="喘息">喘息</option>
                    <option value="低身長">低身長</option>
                    <option value="機能性雑音">機能性雑音</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">処置</label>
                  <select
                    v-model="editFormData.treatment"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="必要なし">必要なし</option>
                    <option value="治療中">治療中</option>
                    <option value="経過観察">経過観察</option>
                    <option value="治療完了">治療完了</option>
                  </select>
                </div>
              </div>
              
              <!-- 眼科検診 -->
              <div v-if="editingExam?.type === '眼科検診'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                  <select
                    v-model="editFormData.exam_result"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="異常なし">異常なし</option>
                    <option value="未検診">未検診</option>
                    <option value="アレルギー性結膜炎">アレルギー性結膜炎</option>
                    <option value="結膜炎">結膜炎</option>
                    <option value="外斜位の疑い">外斜位の疑い</option>
                    <option value="内斜位の疑い">内斜位の疑い</option>
                    <option value="眼瞼炎">眼瞼炎</option>
                    <option value="眼瞼膜炎">眼瞼膜炎</option>
                    <option value="睫毛内反">睫毛内反</option>
                    <option value="麦粒腫">麦粒腫</option>
                    <option value="霰粒腫">霰粒腫</option>
                    <option value="その他">その他</option>
                    <option value="眼瞼脂肪腫">眼瞼脂肪腫</option>
                    <option value="コンタクト検診">コンタクト検診</option>
                    <option value="眼瞼皮膚炎">眼瞼皮膚炎</option>
                    <option value="マイボーム腺梗塞">マイボーム腺梗塞</option>
                    <option value="眼窩脂肪腫">眼窩脂肪腫</option>
                    <option value="眼瞼内反症">眼瞼内反症</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">眼科診断</label>
                  <select
                    v-model="editFormData.diagnosis"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="異常なし">異常なし</option>
                    <option value="アレルギー性結膜炎">アレルギー性結膜炎</option>
                    <option value="結膜炎">結膜炎</option>
                    <option value="外斜位">外斜位</option>
                    <option value="内斜位">内斜位</option>
                    <option value="眼瞼炎">眼瞼炎</option>
                    <option value="眼瞼膜炎">眼瞼膜炎</option>
                    <option value="睫毛内反">睫毛内反</option>
                    <option value="麦粒瞳">麦粒瞳</option>
                    <option value="霰粒瞳">霰粒瞳</option>
                    <option value="その他">その他</option>
                    <option value="コンタクト検診">コンタクト検診</option>
                    <option value="眼瞼脂肪腫">眼瞼脂肪腫</option>
                    <option value="マイボーム腺梗塞">マイボーム腺梗塞</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">処置</label>
                  <select
                    v-model="editFormData.treatment"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="経過観察">経過観察</option>
                    <option value="点眼治療">点眼治療</option>
                    <option value="眼鏡処方">眼鏡処方</option>
                    <option value="眼鏡適合">眼鏡適合</option>
                    <option value="眼鏡更新">眼鏡更新</option>
                    <option value="コンタクトレンズ処方">コンタクトレンズ処方</option>
                    <option value="その他">その他</option>
                    <option value="治療中">治療中</option>
                    <option value="治療済み">治療済み</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">備考</label>
                  <textarea
                    v-model="editFormData.result"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="その他の所見や特記事項を入力してください"
                  ></textarea>
                </div>
              </div>
              
              <!-- 聴力検査・結核検査・尿検査・心電図 -->
              <div v-if="['聴力検査', '結核検査', '尿検査', '心電図'].includes(editingExam?.type)" class="space-y-4">
                <!-- 聴力検査 -->
                <template v-if="editingExam?.type === '聴力検査'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                    <select
                      v-model="editFormData.exam_result"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="未検査">未検査</option>
                      <option value="難聴の疑い・両">難聴の疑い・両</option>
                      <option value="難聴の疑い・右">難聴の疑い・右</option>
                      <option value="難聴の疑い・左">難聴の疑い・左</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                    <select
                      v-model="editFormData.diagnosis"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="感音難聴">感音難聴</option>
                      <option value="伝音難聴">伝音難聴</option>
                      <option value="難聴">難聴</option>
                    </select>
                  </div>
                </template>

                <!-- 結核検査 -->
                <template v-if="editingExam?.type === '結核検査'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                    <select
                      v-model="editFormData.exam_result"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="未検査">未検査</option>
                      <option value="要検討者">要検討者</option>
                      <option value="精密検査">精密検査</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                    <select
                      v-model="editFormData.diagnosis"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="経過観察">経過観察</option>
                      <option value="精密検査">精密検査</option>
                    </select>
                  </div>
                </template>

                <!-- 尿検査 -->
                <template v-if="editingExam?.type === '尿検査'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                    <select
                      v-model="editFormData.exam_result"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="未検査">未検査</option>
                      <option value="蛋白±">蛋白±</option>
                      <option value="蛋白＋">蛋白＋</option>
                      <option value="蛋白＋＋">蛋白＋＋</option>
                      <option value="糖±">糖±</option>
                      <option value="糖＋">糖＋</option>
                      <option value="糖＋＋">糖＋＋</option>
                      <option value="潜血±">潜血±</option>
                      <option value="潜血＋">潜血＋</option>
                      <option value="潜血＋＋">潜血＋＋</option>
                      <option value="再検異常なし">再検異常なし</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                    <select
                      v-model="editFormData.diagnosis"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="蛋白尿">蛋白尿</option>
                      <option value="糖尿">糖尿</option>
                      <option value="血尿">血尿</option>
                      <option value="起立性蛋白尿">起立性蛋白尿</option>
                      <option value="慢性腎炎">慢性腎炎</option>
                      <option value="ネフローゼ症候群">ネフローゼ症候群</option>
                      <option value="急性腎炎">急性腎炎</option>
                      <option value="IgA腎症">IgA腎症</option>
                      <option value="糖尿病">糖尿病</option>
                      <option value="尿路感染症">尿路感染症</option>
                      <option value="膀胱炎">膀胱炎</option>
                      <option value="腎盂腎炎">腎盂腎炎</option>
                      <option value="尿路結石">尿路結石</option>
                      <option value="腎機能障害">腎機能障害</option>
                      <option value="その他">その他</option>
                    </select>
                  </div>
                </template>

                <!-- 心電図 -->
                <template v-if="editingExam?.type === '心電図'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">検診結果</label>
                    <select
                      v-model="editFormData.exam_result"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="未検診">未検診</option>
                      <option value="心室性期外収縮">心室性期外収縮</option>
                      <option value="不完全右脚ブロック">不完全右脚ブロック</option>
                      <option value="不整脈">不整脈</option>
                      <option value="左軸偏位">左軸偏位</option>
                      <option value="ＳＴ−Ｔ異常">ＳＴ−Ｔ異常</option>
                      <option value="ＷＰＷ症候群">ＷＰＷ症候群</option>
                      <option value="先天性心疾患">先天性心疾患</option>
                      <option value="川崎病既往">川崎病既往</option>
                      <option value="心雑音">心雑音</option>
                      <option value="問診より">問診より</option>
                      <option value="ＱＴ延長症候群">ＱＴ延長症候群</option>
                      <option value="完全房室ブロック">完全房室ブロック</option>
                      <option value="第2度房室ブロック">第2度房室ブロック</option>
                      <option value="洞性除脈">洞性除脈</option>
                      <option value="上室性期外収縮">上室性期外収縮</option>
                      <option value="洞性頻脈">洞性頻脈</option>
                      <option value="第1度房室ブロック">第1度房室ブロック</option>
                      <option value="ST低下">ST低下</option>
                      <option value="経過観察">経過観察</option>
                      <option value="班時計回転">班時計回転</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">診断結果</label>
                    <select
                      v-model="editFormData.diagnosis"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="異常なし">異常なし</option>
                      <option value="心室性期外収縮">心室性期外収縮</option>
                      <option value="不完全右脚ブロック">不完全右脚ブロック</option>
                      <option value="不整脈">不整脈</option>
                      <option value="左軸偏位">左軸偏位</option>
                      <option value="ＳＴ−Ｔ異常">ＳＴ−Ｔ異常</option>
                      <option value="ＷＰＷ症候群">ＷＰＷ症候群</option>
                      <option value="先天性心疾患">先天性心疾患</option>
                      <option value="川崎病既往">川崎病既往</option>
                      <option value="心雑音">心雑音</option>
                      <option value="問診より">問診より</option>
                      <option value="ＱＴ延長症候群">ＱＴ延長症候群</option>
                      <option value="完全房室ブロック">完全房室ブロック</option>
                      <option value="第2度房室ブロック">第2度房室ブロック</option>
                      <option value="洞性除脈">洞性除脈</option>
                      <option value="上室性期外収縮">上室性期外収縮</option>
                      <option value="洞性頻脈">洞性頻脈</option>
                      <option value="第1度房室ブロック">第1度房室ブロック</option>
                    </select>
                  </div>
                </template>

                <!-- 共通の処置フィールド -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">処置</label>
                  <select
                    v-model="editFormData.treatment"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">選択してください</option>
                    <option value="経過観察">経過観察</option>
                    <option value="治療不要">治療不要</option>
                    <option value="治療中">治療中</option>
                    <option value="治療完了">治療完了</option>
                    <option value="管理不要">管理不要</option>
                    <option value="要管理Ａ">要管理Ａ</option>
                    <option value="要管理Ｂ">要管理Ｂ</option>
                    <option value="要管理Ｃ">要管理Ｃ</option>
                    <option value="要管理Ｄ">要管理Ｄ</option>
                    <option value="要管理Ｅ">要管理Ｅ</option>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
              <BaseButton
                variant="danger"
                @click="deleteExamItem"
                :disabled="isLoading"
              >
                消去
              </BaseButton>
              <div class="flex space-x-3">
                <BaseButton
                  variant="secondary"
                  @click="closeExamEditModal"
                >
                  キャンセル
                </BaseButton>
                <BaseButton
                  variant="primary"
                  @click="saveExamEdit"
                  :disabled="isLoading"
                >
                  {{ isLoading ? '保存中...' : '保存' }}
                </BaseButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
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
    
    // Exam type filter state
    const showExamTypeFilter = ref(false);
    const examTypeFilter = ref({
      ophthalmology: false,
      otolaryngology: false,
      internal_medicine: false,
      hearing_test: false,
      tuberculosis_test: false,
      urine_test: false,
      ecg: false
    });
    
    // Exam edit modal state
    const showExamEditModal = ref(false);
    const editingExam = ref(null);
    const editFormData = ref({});
    
    // Computed
    const recordId = computed(() => parseInt(route.params.id));
    
    const previousRecord = computed(() => {
      const sorted = studentRecords.value
        .filter(r => new Date(r.measured_date) < new Date(record.value?.measured_date))
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date));
      return sorted[0] || null;
    });
    
    // 過去の測定データから最新の身長・体重を取得
    const latestHeightWeightRecord = computed(() => {
      if (!record.value) return null;
      const sorted = studentRecords.value
        .filter(r => {
          // 現在のレコード以前で、身長と体重が両方存在するレコード
          return new Date(r.measured_date) <= new Date(record.value.measured_date) 
            && r.height && r.weight;
        })
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date));
      return sorted[0] || null;
    });
    
    // 過去の測定データから最新の視力を取得
    const latestVisionRecord = computed(() => {
      if (!record.value) return null;
      const sorted = studentRecords.value
        .filter(r => {
          // 現在のレコード以前で、視力が存在するレコード
          return new Date(r.measured_date) <= new Date(record.value.measured_date)
            && (r.vision_left || r.vision_right);
        })
        .sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date));
      return sorted[0] || null;
    });
    
    // 表示用のデータ（現在のレコードに値がなければ過去のデータを使用）
    const displayHeight = computed(() => {
      return record.value?.height || latestHeightWeightRecord.value?.height || null;
    });
    
    const displayWeight = computed(() => {
      return record.value?.weight || latestHeightWeightRecord.value?.weight || null;
    });
    
    const displayVisionLeft = computed(() => {
      return record.value?.vision_left || latestVisionRecord.value?.vision_left || null;
    });
    
    const displayVisionRight = computed(() => {
      return record.value?.vision_right || latestVisionRecord.value?.vision_right || null;
    });
    
    const displayBMI = computed(() => {
      if (displayHeight.value && displayWeight.value) {
        return (displayWeight.value / ((displayHeight.value / 100) ** 2)).toFixed(2);
      }
      return record.value?.bmi || null;
    });
    
    const nextRecord = computed(() => {
      const sorted = studentRecords.value
        .filter(r => new Date(r.measured_date) > new Date(record.value?.measured_date))
        .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date));
      return sorted[0] || null;
    });
    
    const heightGrowth = computed(() => {
      if (!displayHeight.value || !previousRecord.value?.height) return null;
      return (displayHeight.value - previousRecord.value.height).toFixed(1);
    });
    
    const weightGrowth = computed(() => {
      if (!displayWeight.value || !previousRecord.value?.weight) return null;
      return (displayWeight.value - previousRecord.value.weight).toFixed(1);
    });
    
    const bmiHistory = computed(() => {
      return studentRecords.value
        .filter(r => r.height && r.weight) // 身長と体重が両方存在するレコードのみ
        .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date));
    });
    
    const growthHistory = computed(() => {
      return studentRecords.value
        .filter(r => r.height || r.weight) // 身長または体重が存在するレコードのみ
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
      if (!displayHeight.value || !peerComparison.value) return null;
      return displayHeight.value - peerComparison.value.averageHeight;
    });
    
    const weightDiffFromAverage = computed(() => {
      if (!displayWeight.value || !peerComparison.value) return null;
      return displayWeight.value - peerComparison.value.averageWeight;
    });
    
    const bmiDiffFromAverage = computed(() => {
      if (!displayBMI.value || !peerComparison.value) return null;
      return displayBMI.value - peerComparison.value.averageBMI;
    });
    
    // 検査結果のパース
    const otolaryngologyItems = computed(() => {
      if (!record.value?.otolaryngology_result) return [];
      try {
        return JSON.parse(record.value.otolaryngology_result);
      } catch (e) {
        return [];
      }
    });
    
    const internalMedicineItems = computed(() => {
      if (!record.value?.internal_medicine_result) return [];
      try {
        return JSON.parse(record.value.internal_medicine_result);
      } catch (e) {
        return [];
      }
    });
    
    const hearingTestItems = computed(() => {
      if (!record.value?.hearing_test_result) return [];
      try {
        return JSON.parse(record.value.hearing_test_result);
      } catch (e) {
        return [];
      }
    });
    
    const tuberculosisTestItems = computed(() => {
      if (!record.value?.tuberculosis_test_result) return [];
      try {
        return JSON.parse(record.value.tuberculosis_test_result);
      } catch (e) {
        return [];
      }
    });
    
    const urineTestItems = computed(() => {
      if (!record.value?.urine_test_result) return [];
      try {
        return JSON.parse(record.value.urine_test_result);
      } catch (e) {
        return [];
      }
    });
    
    const ecgItems = computed(() => {
      if (!record.value?.ecg_result) return [];
      try {
        return JSON.parse(record.value.ecg_result);
      } catch (e) {
        return [];
      }
    });
    
    // 全ての健康記録から検診結果を集約（新しい順）
    const allExamResults = computed(() => {
      const results = [];
      
      // studentRecordsを新しい順にソート
      const sortedRecords = [...studentRecords.value].sort((a, b) => 
        new Date(b.measured_date) - new Date(a.measured_date)
      );
      
      sortedRecords.forEach(rec => {
        const recordDate = formatDate(rec.measured_date);
        
        // 内科検診
        if (rec.internal_medicine_result) {
          try {
            const items = JSON.parse(rec.internal_medicine_result);
            items.forEach(item => {
              if (item.exam_result || item.diagnosis) {
                results.push({
                  type: '内科検診',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'purple',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
        
        // 耳鼻科検診
        if (rec.otolaryngology_result) {
          try {
            const items = JSON.parse(rec.otolaryngology_result);
            items.forEach(item => {
              if (item.category || item.exam_result || item.diagnosis) {
                results.push({
                  type: '耳鼻科検診',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'green',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
        
        // 眼科検診
        if (rec.ophthalmology_exam_result || rec.ophthalmology_diagnosis || rec.ophthalmology_treatment || rec.ophthalmology_result) {
          results.push({
            type: '眼科検診',
            date: recordDate,
            recordId: rec.id,
            color: 'blue',
            data: {
              exam_result: rec.ophthalmology_exam_result,
              diagnosis: rec.ophthalmology_diagnosis,
              treatment: rec.ophthalmology_treatment,
              result: rec.ophthalmology_result
            }
          });
        }
        
        // 聴力検査
        if (rec.hearing_test_result) {
          try {
            const items = JSON.parse(rec.hearing_test_result);
            items.forEach(item => {
              if (item.exam_result || item.diagnosis) {
                results.push({
                  type: '聴力検査',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'indigo',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
        
        // 結核検査
        if (rec.tuberculosis_test_result) {
          try {
            const items = JSON.parse(rec.tuberculosis_test_result);
            items.forEach(item => {
              if (item.exam_result || item.diagnosis) {
                results.push({
                  type: '結核検査',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'yellow',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
        
        // 尿検査
        if (rec.urine_test_result) {
          try {
            const items = JSON.parse(rec.urine_test_result);
            items.forEach(item => {
              if (item.exam_result || item.diagnosis) {
                results.push({
                  type: '尿検査',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'pink',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
        
        // 心電図
        if (rec.ecg_result) {
          try {
            const items = JSON.parse(rec.ecg_result);
            items.forEach(item => {
              if (item.exam_result || item.diagnosis) {
                results.push({
                  type: '心電図',
                  date: recordDate,
                  recordId: rec.id,
                  color: 'red',
                  data: item
                });
              }
            });
          } catch (e) {}
        }
      });
      
      return results;
    });
    
    // フィルタリングされた検査結果
    const filteredExamResults = computed(() => {
      // フィルターが何も選択されていない場合は全て表示
      const hasAnyFilter = Object.values(examTypeFilter.value).some(v => v === true);
      
      if (!hasAnyFilter) {
        return allExamResults.value;
      }
      
      // 選択された項目のみ表示
      return allExamResults.value.filter(exam => {
        const typeMap = {
          '眼科検診': examTypeFilter.value.ophthalmology,
          '耳鼻科検診': examTypeFilter.value.otolaryngology,
          '内科検診': examTypeFilter.value.internal_medicine,
          '聴力検査': examTypeFilter.value.hearing_test,
          '結核検査': examTypeFilter.value.tuberculosis_test,
          '尿検査': examTypeFilter.value.urine_test,
          '心電図': examTypeFilter.value.ecg
        };
        
        return typeMap[exam.type] === true;
      });
    });
    
    // フィルターが有効かどうか
    const hasActiveExamFilter = computed(() => {
      return Object.values(examTypeFilter.value).some(v => v === true);
    });
    
    // 有効なフィルター数
    const activeExamFilterCount = computed(() => {
      return Object.values(examTypeFilter.value).filter(v => v === true).length;
    });
    
    // 検査結果があるかどうか（全ての記録を含む）
    const hasExamResults = computed(() => {
      return allExamResults.value.length > 0;
    });
    
    const relatedRecords = computed(() => {
      return studentRecords.value
        .filter(r => {
          // 現在のレコードは除外
          if (r.id === record.value?.id) return false;
          // 身長または体重が存在するレコードのみ表示
          return r.height || r.weight;
        })
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
      if (!birthDate) return 0;
      
      const birth = new Date(birthDate);
      // measuredDateがない場合は現在の日付を使用
      const measured = measuredDate ? new Date(measuredDate) : new Date();
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
    
    const getBorderColorClass = (color) => {
      const colorMap = {
        'purple': 'border-purple-500',
        'green': 'border-green-500',
        'blue': 'border-blue-500',
        'indigo': 'border-indigo-500',
        'yellow': 'border-yellow-500',
        'pink': 'border-pink-500',
        'red': 'border-red-500'
      };
      return colorMap[color] || 'border-gray-500';
    };
    
    const clearExamTypeFilter = () => {
      examTypeFilter.value = {
        ophthalmology: false,
        otolaryngology: false,
        internal_medicine: false,
        hearing_test: false,
        tuberculosis_test: false,
        urine_test: false,
        ecg: false
      };
    };
    
    const openExamEditModal = (exam) => {
      editingExam.value = exam;
      editFormData.value = { ...exam.data };
      showExamEditModal.value = true;
    };
    
    const closeExamEditModal = () => {
      showExamEditModal.value = false;
      editingExam.value = null;
      editFormData.value = {};
    };
    
    const saveExamEdit = async () => {
      if (!editingExam.value) return;
      
      try {
        isLoading.value = true;
        
        // 現在のレコードを取得
        const currentRecord = studentRecords.value.find(r => r.id === editingExam.value.recordId);
        if (!currentRecord) {
          throw new Error('レコードが見つかりません');
        }
        
        // 既存のレコードデータを全て含める（バリデーション通過のため）
        const baseUpdateData = {
          year: currentRecord.year,
          student_id: currentRecord.student_id,
          measured_date: currentRecord.measured_date,
          height: currentRecord.height,
          weight: currentRecord.weight,
          vision_left: currentRecord.vision_left,
          vision_right: currentRecord.vision_right,
          vision_left_corrected: currentRecord.vision_left_corrected,
          vision_right_corrected: currentRecord.vision_right_corrected,
          ophthalmology_exam_result: currentRecord.ophthalmology_exam_result,
          ophthalmology_diagnosis: currentRecord.ophthalmology_diagnosis,
          ophthalmology_treatment: currentRecord.ophthalmology_treatment,
          ophthalmology_result: currentRecord.ophthalmology_result,
          otolaryngology_result: currentRecord.otolaryngology_result,
          internal_medicine_result: currentRecord.internal_medicine_result,
          hearing_test_result: currentRecord.hearing_test_result,
          tuberculosis_test_result: currentRecord.tuberculosis_test_result,
          urine_test_result: currentRecord.urine_test_result,
          ecg_result: currentRecord.ecg_result,
        };
        
        // 検診タイプに応じて適切なフィールドを更新
        let fieldName = '';
        let currentData = [];
        
        switch (editingExam.value.type) {
          case '内科検診':
            fieldName = 'internal_medicine_result';
            currentData = currentRecord.internal_medicine_result 
              ? JSON.parse(currentRecord.internal_medicine_result) 
              : [];
            break;
          case '耳鼻科検診':
            fieldName = 'otolaryngology_result';
            currentData = currentRecord.otolaryngology_result 
              ? JSON.parse(currentRecord.otolaryngology_result) 
              : [];
            break;
          case '聴力検査':
            fieldName = 'hearing_test_result';
            currentData = currentRecord.hearing_test_result 
              ? JSON.parse(currentRecord.hearing_test_result) 
              : [];
            break;
          case '結核検査':
            fieldName = 'tuberculosis_test_result';
            currentData = currentRecord.tuberculosis_test_result 
              ? JSON.parse(currentRecord.tuberculosis_test_result) 
              : [];
            break;
          case '尿検査':
            fieldName = 'urine_test_result';
            currentData = currentRecord.urine_test_result 
              ? JSON.parse(currentRecord.urine_test_result) 
              : [];
            break;
          case '心電図':
            fieldName = 'ecg_result';
            currentData = currentRecord.ecg_result 
              ? JSON.parse(currentRecord.ecg_result) 
              : [];
            break;
          case '眼科検診':
            // 眼科検診は配列ではなく個別フィールド
            baseUpdateData.ophthalmology_exam_result = editFormData.value.exam_result || null;
            baseUpdateData.ophthalmology_diagnosis = editFormData.value.diagnosis || null;
            baseUpdateData.ophthalmology_treatment = editFormData.value.treatment || null;
            baseUpdateData.ophthalmology_result = editFormData.value.result || null;
            
            await healthRecordStore.updateHealthRecord(editingExam.value.recordId, baseUpdateData);
            closeExamEditModal();
            await fetchRecord();
            
            notificationStore.addNotification({
              type: 'success',
              title: '更新完了',
              message: '検診データを更新しました'
            });
            return;
        }
        
        // 配列形式の検診データの場合、該当アイテムを更新
        if (fieldName) {
          // 編集対象のアイテムを見つけて更新
          const index = currentData.findIndex(item => 
            JSON.stringify(item) === JSON.stringify(editingExam.value.data)
          );
          
          if (index !== -1) {
            currentData[index] = editFormData.value;
          } else {
            // 見つからない場合は追加
            currentData.push(editFormData.value);
          }
          
          // 更新対象のフィールドに新しいデータをセット
          baseUpdateData[fieldName] = JSON.stringify(currentData);
          
          await healthRecordStore.updateHealthRecord(editingExam.value.recordId, baseUpdateData);
          closeExamEditModal();
          await fetchRecord();
          
          notificationStore.addNotification({
            type: 'success',
            title: '更新完了',
            message: '検診データを更新しました'
          });
        }
        
      } catch (error) {
        console.error('Failed to update exam data:', error);
        console.error('Error response:', error.response?.data);
        notificationStore.addNotification({
          type: 'danger',
          title: '更新エラー',
          message: error.response?.data?.message || '検診データの更新に失敗しました'
        });
      } finally {
        isLoading.value = false;
      }
    };
    
    const deleteExamItem = async () => {
      if (!editingExam.value) return;
      
      if (!confirm('この検診データを削除してもよろしいですか？')) {
        return;
      }
      
      try {
        isLoading.value = true;
        
        // 現在のレコードを取得
        const currentRecord = studentRecords.value.find(r => r.id === editingExam.value.recordId);
        if (!currentRecord) {
          throw new Error('レコードが見つかりません');
        }
        
        // 既存のレコードデータを全て含める（バリデーション通過のため）
        const baseUpdateData = {
          year: currentRecord.year,
          student_id: currentRecord.student_id,
          measured_date: currentRecord.measured_date,
          height: currentRecord.height,
          weight: currentRecord.weight,
          vision_left: currentRecord.vision_left,
          vision_right: currentRecord.vision_right,
          vision_left_corrected: currentRecord.vision_left_corrected,
          vision_right_corrected: currentRecord.vision_right_corrected,
          ophthalmology_exam_result: currentRecord.ophthalmology_exam_result,
          ophthalmology_diagnosis: currentRecord.ophthalmology_diagnosis,
          ophthalmology_treatment: currentRecord.ophthalmology_treatment,
          ophthalmology_result: currentRecord.ophthalmology_result,
          otolaryngology_result: currentRecord.otolaryngology_result,
          internal_medicine_result: currentRecord.internal_medicine_result,
          hearing_test_result: currentRecord.hearing_test_result,
          tuberculosis_test_result: currentRecord.tuberculosis_test_result,
          urine_test_result: currentRecord.urine_test_result,
          ecg_result: currentRecord.ecg_result,
        };
        
        // 検診タイプに応じて適切なフィールドを更新
        let fieldName = '';
        let currentData = [];
        
        switch (editingExam.value.type) {
          case '内科検診':
            fieldName = 'internal_medicine_result';
            currentData = currentRecord.internal_medicine_result 
              ? JSON.parse(currentRecord.internal_medicine_result) 
              : [];
            break;
          case '耳鼻科検診':
            fieldName = 'otolaryngology_result';
            currentData = currentRecord.otolaryngology_result 
              ? JSON.parse(currentRecord.otolaryngology_result) 
              : [];
            break;
          case '聴力検査':
            fieldName = 'hearing_test_result';
            currentData = currentRecord.hearing_test_result 
              ? JSON.parse(currentRecord.hearing_test_result) 
              : [];
            break;
          case '結核検査':
            fieldName = 'tuberculosis_test_result';
            currentData = currentRecord.tuberculosis_test_result 
              ? JSON.parse(currentRecord.tuberculosis_test_result) 
              : [];
            break;
          case '尿検査':
            fieldName = 'urine_test_result';
            currentData = currentRecord.urine_test_result 
              ? JSON.parse(currentRecord.urine_test_result) 
              : [];
            break;
          case '心電図':
            fieldName = 'ecg_result';
            currentData = currentRecord.ecg_result 
              ? JSON.parse(currentRecord.ecg_result) 
              : [];
            break;
          case '眼科検診':
            // 眼科検診は配列ではなく個別フィールド
            baseUpdateData.ophthalmology_exam_result = null;
            baseUpdateData.ophthalmology_diagnosis = null;
            baseUpdateData.ophthalmology_treatment = null;
            baseUpdateData.ophthalmology_result = null;
            
            await healthRecordStore.updateHealthRecord(editingExam.value.recordId, baseUpdateData);
            closeExamEditModal();
            await fetchRecord();
            
            notificationStore.addNotification({
              type: 'success',
              title: '削除完了',
              message: '検診データを削除しました'
            });
            return;
        }
        
        // 配列形式の検診データの場合、該当アイテムを削除
        if (fieldName) {
          // 編集対象のアイテムを見つけて削除
          const index = currentData.findIndex(item => 
            JSON.stringify(item) === JSON.stringify(editingExam.value.data)
          );
          
          if (index !== -1) {
            currentData.splice(index, 1);
          }
          
          // 更新対象のフィールドに新しいデータをセット
          baseUpdateData[fieldName] = currentData.length > 0 ? JSON.stringify(currentData) : null;
          
          await healthRecordStore.updateHealthRecord(editingExam.value.recordId, baseUpdateData);
          closeExamEditModal();
          await fetchRecord();
          
          notificationStore.addNotification({
            type: 'success',
            title: '削除完了',
            message: '検診データを削除しました'
          });
        }
        
      } catch (error) {
        console.error('Failed to delete exam data:', error);
        console.error('Error response:', error.response?.data);
        notificationStore.addNotification({
          type: 'danger',
          title: '削除エラー',
          message: error.response?.data?.message || '検診データの削除に失敗しました'
        });
      } finally {
        isLoading.value = false;
      }
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
      latestHeightWeightRecord,
      latestVisionRecord,
      displayHeight,
      displayWeight,
      displayVisionLeft,
      displayVisionRight,
      displayBMI,
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
      otolaryngologyItems,
      internalMedicineItems,
      hearingTestItems,
      tuberculosisTestItems,
      urineTestItems,
      ecgItems,
      allExamResults,
      filteredExamResults,
      hasExamResults,
      relatedRecords,
      showExamTypeFilter,
      examTypeFilter,
      hasActiveExamFilter,
      activeExamFilterCount,
      showExamEditModal,
      editingExam,
      editFormData,
      formatDate,
      formatShortDate,
      calculateAge,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      getBMIBorderColor,
      getBMIDescription,
      getBorderColorClass,
      clearExamTypeFilter,
      openExamEditModal,
      closeExamEditModal,
      saveExamEdit,
      deleteExamItem,
      exportRecord
    };
  }
};
</script>