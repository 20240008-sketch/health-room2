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
                            {{ student.student_number }}番 | {{ getClassGrade(student) }}年{{ getClassName(student) }}
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
                          {{ selectedStudent.student_number }}番 | {{ getClassGrade(selectedStudent) }}年{{ getClassName(selectedStudent) }}
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
              <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
                  >
                    <option value="">すべてのクラス</option>
                    <option 
                      v-for="schoolClass in filteredClasses" 
                      :key="schoolClass.class_id" 
                      :value="schoolClass.class_id"
                    >
                      {{ schoolClass.class_name }}
                    </option>
                  </BaseInput>
                </div>

                <!-- Gender -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="bulkFilters.gender"
                    label="性別"
                    @change="updateBulkSelection"
                  >
                    <option value="">すべて</option>
                    <option value="男">男</option>
                    <option value="女">女</option>
                  </BaseInput>
                </div>

                <!-- Students List for Bulk -->
                <div v-if="bulkStudents.length > 0" class="sm:col-span-2 lg:col-span-4">
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
                            {{ student.student_number }}番 | {{ getClassGrade(student) }}年{{ getClassName(student) }}
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
              <!-- Measurement Items Selection -->
              <div>
                <label class="text-base font-medium text-gray-900 mb-3 block">測定項目を選択</label>
                <div class="space-y-3">
                  <!-- Basic Measurements -->
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.height"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">身長 (cm)</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.weight"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">体重 (kg)</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.vision"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">視力</span>
                  </label>
                  
                  <!-- Medical Examinations -->
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.ophthalmology"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">眼科検診</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.otolaryngology"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">耳鼻科検診</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.internal_medicine"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">内科検診</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.hearing_test"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">聴力検査</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.tuberculosis_test"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">結核検査</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.urine_test"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">尿検査</span>
                  </label>
                  <label class="flex items-center p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="measurementItems.ecg"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-700">心電図</span>
                  </label>
                </div>
              </div>

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
                  <div v-if="measurementItems.height">
                    <BaseInput
                      ref="heightInput"
                      type="number"
                      step="0.1"
                      v-model="form.height"
                      label="身長 (cm)"
                      :required="measurementItems.height"
                      placeholder="150.5"
                      :error="errors.height"
                      @input="calculateBMI"
                      @keydown.enter.prevent="focusNextField('heightInput')"
                    />
                  </div>

                  <!-- Weight -->
                  <div v-if="measurementItems.weight">
                    <BaseInput
                      ref="weightInput"
                      type="number"
                      step="0.1"
                      v-model="form.weight"
                      label="体重 (kg)"
                      :required="measurementItems.weight"
                      placeholder="45.5"
                      :error="errors.weight"
                      @input="calculateBMI"
                      @keydown.enter.prevent="focusNextField('weightInput')"
                    />
                  </div>

                  <!-- Vision -->
                  <div v-if="measurementItems.vision" class="sm:col-span-2">
                    <div class="space-y-4">
                      <!-- Naked Vision -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">裸眼視力（任意）</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <BaseInput
                            ref="visionLeftInput"
                            type="select"
                            v-model="form.vision_left"
                            label="左"
                            :error="errors.vision_left"
                            @change="focusNextField('visionLeftInput')"
                          >
                            <option value="">選択してください</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </BaseInput>
                          <BaseInput
                            ref="visionRightInput"
                            type="select"
                            v-model="form.vision_right"
                            label="右"
                            :error="errors.vision_right"
                            @change="focusNextField('visionRightInput')"
                          >
                            <option value="">選択してください</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </BaseInput>
                        </div>
                      </div>
                      
                      <!-- Corrected Vision -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">矯正視力（任意）</label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <BaseInput
                            ref="visionLeftCorrectedInput"
                            type="select"
                            v-model="form.vision_left_corrected"
                            label="左"
                            :error="errors.vision_left_corrected"
                            @change="focusNextField('visionLeftCorrectedInput')"
                          >
                            <option value="">選択してください</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </BaseInput>
                          <BaseInput
                            ref="visionRightCorrectedInput"
                            type="select"
                            v-model="form.vision_right_corrected"
                            label="右"
                            :error="errors.vision_right_corrected"
                            @change="focusNextField('visionRightCorrectedInput')"
                          >
                            <option value="">選択してください</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </BaseInput>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Ophthalmology -->
                  <div v-if="measurementItems.ophthalmology" class="sm:col-span-2 space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 border-b pb-2">眼科検診</h4>
                    
                    <!-- 学生情報表示 -->
                    <div v-if="selectedStudent" class="bg-gray-50 p-3 rounded-md">
                      <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                          <span class="text-gray-600">出席番号:</span>
                          <span class="ml-2 font-medium">{{ selectedStudent.student_number }}</span>
                        </div>
                        <div>
                          <span class="text-gray-600">氏名:</span>
                          <span class="ml-2 font-medium">{{ selectedStudent.name }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- 検診結果 -->
                    <div>
                      <BaseInput
                        type="select"
                        v-model="form.ophthalmology_exam_result"
                        label="検診結果"
                        :error="errors.ophthalmology_exam_result"
                      >
                        <option value="">選択してください</option>
                        <option value="異常なし">異常なし</option>
                        <option value="要観察">要観察</option>
                        <option value="要精密検査">要精密検査</option>
                        <option value="治療中">治療中</option>
                      </BaseInput>
                    </div>

                    <!-- 診断結果 -->
                    <div>
                      <BaseInput
                        type="select"
                        v-model="form.ophthalmology_diagnosis"
                        label="診断結果"
                        :error="errors.ophthalmology_diagnosis"
                      >
                        <option value="">選択してください</option>
                        <option value="正常">正常</option>
                        <option value="近視">近視</option>
                        <option value="遠視">遠視</option>
                        <option value="乱視">乱視</option>
                        <option value="弱視">弱視</option>
                        <option value="斜視">斜視</option>
                        <option value="結膜炎">結膜炎</option>
                        <option value="その他">その他</option>
                      </BaseInput>
                    </div>

                    <!-- 処置 -->
                    <div>
                      <BaseInput
                        type="select"
                        v-model="form.ophthalmology_treatment"
                        label="処置"
                        :error="errors.ophthalmology_treatment"
                      >
                        <option value="">選択してください</option>
                        <option value="なし">なし</option>
                        <option value="経過観察">経過観察</option>
                        <option value="眼鏡処方">眼鏡処方</option>
                        <option value="医療機関受診勧奨">医療機関受診勧奨</option>
                        <option value="治療継続">治療継続</option>
                      </BaseInput>
                    </div>

                    <!-- 備考欄（既存のフィールド） -->
                    <div>
                      <BaseInput
                        type="textarea"
                        v-model="form.ophthalmology_result"
                        label="備考"
                        placeholder="その他の所見や特記事項を入力してください"
                        :error="errors.ophthalmology_result"
                        rows="2"
                      />
                    </div>
                  </div>

                  <!-- Otolaryngology -->
                  <div v-if="measurementItems.otolaryngology" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.otolaryngology_result"
                      label="耳鼻科検診結果"
                      placeholder="検診結果を入力してください"
                      :error="errors.otolaryngology_result"
                      rows="2"
                    />
                  </div>

                  <!-- Internal Medicine -->
                  <div v-if="measurementItems.internal_medicine" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.internal_medicine_result"
                      label="内科検診結果"
                      placeholder="検診結果を入力してください"
                      :error="errors.internal_medicine_result"
                      rows="2"
                    />
                  </div>

                  <!-- Hearing Test -->
                  <div v-if="measurementItems.hearing_test" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.hearing_test_result"
                      label="聴力検査結果"
                      placeholder="検査結果を入力してください"
                      :error="errors.hearing_test_result"
                      rows="2"
                    />
                  </div>

                  <!-- Tuberculosis Test -->
                  <div v-if="measurementItems.tuberculosis_test" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.tuberculosis_test_result"
                      label="結核検査結果"
                      placeholder="検査結果を入力してください"
                      :error="errors.tuberculosis_test_result"
                      rows="2"
                    />
                  </div>

                  <!-- Urine Test -->
                  <div v-if="measurementItems.urine_test" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.urine_test_result"
                      label="尿検査結果"
                      placeholder="検査結果を入力してください"
                      :error="errors.urine_test_result"
                      rows="2"
                    />
                  </div>

                  <!-- ECG -->
                  <div v-if="measurementItems.ecg" class="sm:col-span-2">
                    <BaseInput
                      type="textarea"
                      v-model="form.ecg_result"
                      label="心電図検査結果"
                      placeholder="検査結果を入力してください"
                      :error="errors.ecg_result"
                      rows="2"
                    />
                  </div>
                </div>

                <!-- BMI Display -->
                <div v-if="measurementItems.height && measurementItems.weight && form.height && form.weight" class="bg-gray-50 rounded-lg p-4">
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
                <div v-if="selectedStudent?.latest_health_record && measurementItems.height && measurementItems.weight && form.height && form.weight" class="bg-blue-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-700 mb-2">成長記録</h4>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center" v-if="measurementItems.height">
                      <ArrowUpIcon v-if="heightGrowth > 0" class="h-4 w-4 text-green-500 mr-2" />
                      <ArrowDownIcon v-else-if="heightGrowth < 0" class="h-4 w-4 text-red-500 mr-2" />
                      <span class="text-sm">
                        身長: {{ heightGrowth > 0 ? '+' : '' }}{{ heightGrowth }}cm
                      </span>
                    </div>
                    <div class="flex items-center" v-if="measurementItems.weight">
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
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                  <div class="flex">
                    <InformationCircleIcon class="h-5 w-5 text-blue-600 mr-2" />
                    <div class="text-sm text-blue-800">
                      <p class="font-medium">一括測定モード</p>
                      <p>選択された{{ selectedStudents.length }}名の学生に対して測定データを入力できます。</p>
                    </div>
                  </div>
                </div>
                
                <!-- Card Format for Ophthalmology Exam -->
                <div v-if="measurementItems.ophthalmology" class="space-y-4">
                  <h4 class="text-sm font-semibold text-gray-900">眼科検診 - 学生ごとの入力</h4>
                  <div 
                    v-for="student in selectedStudents" 
                    :key="student.id"
                    class="border border-gray-300 rounded-lg p-4 bg-white space-y-4"
                  >
                    <!-- Student Info Header -->
                    <div class="bg-gray-50 p-3 rounded-md border-l-4 border-blue-500">
                      <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                          <span class="text-gray-600">出席番号:</span>
                          <span class="ml-2 font-semibold">{{ student.student_number }}</span>
                        </div>
                        <div>
                          <span class="text-gray-600">氏名:</span>
                          <span class="ml-2 font-semibold">{{ student.name }}</span>
                        </div>
                        <div class="col-span-2 text-xs text-gray-500">
                          {{ getStudentClassDisplay(student) }} | {{ student.student_id }}
                        </div>
                      </div>
                    </div>

                    <!-- Ophthalmology Fields -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                      <!-- 検診結果 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">検診結果</label>
                        <select
                          v-model="bulkMeasurements[student.id].ophthalmology_exam_result"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2"
                        >
                          <option value="">選択してください</option>
                          <option value="異常なし">異常なし</option>
                          <option value="要観察">要観察</option>
                          <option value="要精密検査">要精密検査</option>
                          <option value="治療中">治療中</option>
                        </select>
                      </div>

                      <!-- 診断結果 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">診断結果</label>
                        <select
                          v-model="bulkMeasurements[student.id].ophthalmology_diagnosis"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2"
                        >
                          <option value="">選択してください</option>
                          <option value="正常">正常</option>
                          <option value="近視">近視</option>
                          <option value="遠視">遠視</option>
                          <option value="乱視">乱視</option>
                          <option value="弱視">弱視</option>
                          <option value="斜視">斜視</option>
                          <option value="結膜炎">結膜炎</option>
                          <option value="その他">その他</option>
                        </select>
                      </div>

                      <!-- 処置 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">処置</label>
                        <select
                          v-model="bulkMeasurements[student.id].ophthalmology_treatment"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2"
                        >
                          <option value="">選択してください</option>
                          <option value="なし">なし</option>
                          <option value="経過観察">経過観察</option>
                          <option value="眼鏡処方">眼鏡処方</option>
                          <option value="医療機関受診勧奨">医療機関受診勧奨</option>
                          <option value="治療継続">治療継続</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Table Format for Other Measurements -->
                <div v-if="!measurementItems.ophthalmology" class="overflow-x-auto border border-gray-300 rounded-lg">
                  <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="sticky left-0 z-10 bg-gray-50 px-2 py-2 text-left text-xs font-semibold text-gray-900 border-r border-gray-300 w-16">
                          出席<br/>番号
                        </th>
                        <th scope="col" class="sticky z-10 bg-gray-50 px-2 py-2 text-left text-xs font-semibold text-gray-900 border-r border-gray-300 w-28" style="left: 4rem;">
                          名前
                        </th>
                        <th v-if="measurementItems.height" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          身長<br/>(cm)
                        </th>
                        <th v-if="measurementItems.weight" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          体重<br/>(kg)
                        </th>
                        <th v-if="measurementItems.vision" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          裸眼視力<br/>左
                        </th>
                        <th v-if="measurementItems.vision" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          裸眼視力<br/>右
                        </th>
                        <th v-if="measurementItems.vision" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          矯正視力<br/>左
                        </th>
                        <th v-if="measurementItems.vision" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[90px]">
                          矯正視力<br/>右
                        </th>
                        <th v-if="measurementItems.height && measurementItems.weight" scope="col" class="px-2 py-2 text-center text-xs font-semibold text-gray-900 min-w-[80px]">
                          BMI
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                      <tr
                        v-for="student in selectedStudents"
                        :key="student.id"
                        class="hover:bg-gray-50"
                      >
                        <!-- Student Number (Sticky) -->
                        <td class="sticky left-0 z-10 bg-white whitespace-nowrap px-2 py-2 text-xs font-medium text-gray-900 border-r border-gray-300 w-16 text-center">
                          {{ student.student_number }}
                        </td>
                        
                        <!-- Student Name (Sticky) -->
                        <td class="sticky z-10 bg-white px-2 py-2 text-xs text-gray-900 border-r border-gray-300 w-28" style="left: 4rem;">
                          <div>
                            <div class="font-medium leading-tight">{{ student.name }}</div>
                            <div class="text-[10px] text-gray-500 leading-tight">
                              {{ getStudentClassDisplay(student) }}
                            </div>
                            <div class="text-[10px] text-gray-400 leading-tight font-mono">
                              {{ student.student_id }}
                            </div>
                          </div>
                        </td>
                        
                        <!-- Height -->
                        <td v-if="measurementItems.height" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <input
                            :ref="el => setBulkInputRef(el, student.id, 'height')"
                            type="number"
                            step="0.1"
                            v-model="bulkMeasurements[student.id].height"
                            placeholder="150.5"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @input="calculateBulkBMI(student.id)"
                            @keydown.enter.prevent="focusBulkNextField(student.id, 'height')"
                          />
                        </td>
                        
                        <!-- Weight -->
                        <td v-if="measurementItems.weight" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <input
                            :ref="el => setBulkInputRef(el, student.id, 'weight')"
                            type="number"
                            step="0.1"
                            v-model="bulkMeasurements[student.id].weight"
                            placeholder="45.5"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @input="calculateBulkBMI(student.id)"
                            @keydown.enter.prevent="focusBulkNextField(student.id, 'weight')"
                          />
                        </td>
                        
                        <!-- Vision Left -->
                        <td v-if="measurementItems.vision" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <select
                            :ref="el => setBulkInputRef(el, student.id, 'vision_left')"
                            v-model="bulkMeasurements[student.id].vision_left"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @change="focusBulkNextField(student.id, 'vision_left')"
                          >
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </select>
                        </td>
                        
                        <!-- Vision Right -->
                        <td v-if="measurementItems.vision" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <select
                            :ref="el => setBulkInputRef(el, student.id, 'vision_right')"
                            v-model="bulkMeasurements[student.id].vision_right"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @change="focusBulkNextField(student.id, 'vision_right')"
                          >
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </select>
                        </td>
                        
                        <!-- Vision Left Corrected -->
                        <td v-if="measurementItems.vision" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <select
                            :ref="el => setBulkInputRef(el, student.id, 'vision_left_corrected')"
                            v-model="bulkMeasurements[student.id].vision_left_corrected"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @change="focusBulkNextField(student.id, 'vision_left_corrected')"
                          >
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </select>
                        </td>
                        
                        <!-- Vision Right Corrected -->
                        <td v-if="measurementItems.vision" class="whitespace-nowrap px-1 py-2 text-xs text-gray-900">
                          <select
                            :ref="el => setBulkInputRef(el, student.id, 'vision_right_corrected')"
                            v-model="bulkMeasurements[student.id].vision_right_corrected"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs text-center py-1.5"
                            @change="focusBulkNextField(student.id, 'vision_right_corrected')"
                          >
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                          </select>
                        </td>
                        
                        <!-- BMI -->
                        <td v-if="measurementItems.height && measurementItems.weight" class="whitespace-nowrap px-2 py-2 text-xs text-center">
                          <span v-if="bulkMeasurements[student.id].bmi" :class="getBMIColor(bulkMeasurements[student.id].bmi)" class="font-semibold">
                            {{ bulkMeasurements[student.id].bmi }}
                          </span>
                          <span v-else class="text-gray-400">-</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <BaseInput
                  ref="notesInput"
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
                <span class="font-medium">{{ selectedStudent.gender === 'male' || selectedStudent.gender === '男' ? '男' : selectedStudent.gender === 'female' || selectedStudent.gender === '女' ? '女' : '-' }}</span>
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
              <div v-if="bulkFilters.gender" class="flex justify-between">
                <span class="text-gray-500">性別</span>
                <span class="font-medium">{{ bulkFilters.gender }}</span>
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
    
    // Measurement items selection
    const measurementItems = reactive({
      height: true,
      weight: true,
      vision: false,
      ophthalmology: false,
      otolaryngology: false,
      internal_medicine: false,
      hearing_test: false,
      tuberculosis_test: false,
      urine_test: false,
      ecg: false
    });
    
    // Form data
    const form = reactive({
      measured_date: new Date().toISOString().split('T')[0],
      academic_year: new Date().getFullYear(),
      height: '',
      weight: '',
      vision_left: '',
      vision_right: '',
      vision_left_corrected: '',
      vision_right_corrected: '',
      ophthalmology_result: '',
      ophthalmology_exam_result: '',
      ophthalmology_diagnosis: '',
      ophthalmology_treatment: '',
      otolaryngology_result: '',
      internal_medicine_result: '',
      hearing_test_result: '',
      tuberculosis_test_result: '',
      urine_test_result: '',
      ecg_result: '',
      notes: ''
    });
    
    const bulkFilters = reactive({
      academic_year: new Date().getFullYear().toString(),
      grade: '',
      class_id: '',
      gender: ''
    });
    
    // Bulk measurements storage - stores measurements for each student
    const bulkMeasurements = reactive({});
    
    // Refs for input fields
    const heightInput = ref(null);
    const weightInput = ref(null);
    const visionLeftInput = ref(null);
    const visionRightInput = ref(null);
    const visionLeftCorrectedInput = ref(null);
    const visionRightCorrectedInput = ref(null);
    const notesInput = ref(null);
    const bulkInputRefs = reactive({});
    
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
      const currentYear = new Date().getFullYear();
      let result = classes.value.filter(c => c.year === currentYear);
      
      if (bulkFilters.grade) {
        result = result.filter(c => c.grade.toString() === bulkFilters.grade);
      }
      
      return result.sort((a, b) => {
        // First sort by grade, then by class_name
        if (a.grade !== b.grade) {
          return a.grade - b.grade;
        }
        return a.class_name.localeCompare(b.class_name, 'ja');
      });
    });
    
    const bulkStudents = computed(() => {
      let result = [...students.value];
      
      if (bulkFilters.grade) {
        result = result.filter(s => {
          // Use the grade from school_classes via class_id
          const studentClass = classes.value.find(c => c.class_id === s.class_id);
          return studentClass && studentClass.grade.toString() === bulkFilters.grade;
        });
      }
      
      if (bulkFilters.class_id) {
        result = result.filter(s => s.class_id === bulkFilters.class_id);
      }
      
      if (bulkFilters.gender) {
        result = result.filter(s => s.gender === bulkFilters.gender);
      }
      
      // Sort by student_number (ascending)
      return result.sort((a, b) => {
        const aNum = parseInt(a.student_number) || 0;
        const bNum = parseInt(b.student_number) || 0;
        return aNum - bNum;
      });
    });
    
    const selectedStudents = computed(() => {
      const selected = students.value.filter(s => selectedStudentIds.value.includes(s.id));
      
      // Sort by student_number (ascending)
      return selected.sort((a, b) => {
        const aNum = parseInt(a.student_number) || 0;
        const bNum = parseInt(b.student_number) || 0;
        return aNum - bNum;
      });
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
          student.name_kana.toLowerCase().includes(query) ||
          String(student.student_number).includes(query) ||
          (student.student_id && student.student_id.toLowerCase().includes(query))
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
        // Initialize bulk measurements for all selected students
        initializeBulkMeasurements();
      }
    };
    
    const toggleStudentSelection = (studentId) => {
      const index = selectedStudentIds.value.indexOf(studentId);
      if (index > -1) {
        selectedStudentIds.value.splice(index, 1);
        // Remove from bulk measurements
        delete bulkMeasurements[studentId];
      } else {
        selectedStudentIds.value.push(studentId);
        // Initialize measurement for this student
        initializeBulkMeasurement(studentId);
      }
    };
    
    const initializeBulkMeasurements = () => {
      selectedStudentIds.value.forEach(id => {
        initializeBulkMeasurement(id);
      });
    };
    
    const initializeBulkMeasurement = (studentId) => {
      if (!bulkMeasurements[studentId]) {
        bulkMeasurements[studentId] = {
          height: '',
          weight: '',
          vision_left: '',
          vision_right: '',
          vision_left_corrected: '',
          vision_right_corrected: '',
          ophthalmology_exam_result: '',
          ophthalmology_diagnosis: '',
          ophthalmology_treatment: '',
          bmi: null
        };
      }
    };
    
    const calculateBulkBMI = (studentId) => {
      const measurement = bulkMeasurements[studentId];
      if (!measurement) return;
      
      const height = parseFloat(measurement.height);
      const weight = parseFloat(measurement.weight);
      
      if (height && weight) {
        const heightInMeters = height / 100;
        measurement.bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);
      } else {
        measurement.bmi = null;
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
    
    const getClassName = (student) => {
      if (!student.class_id) return '未設定';
      const schoolClass = classes.value.find(c => c.class_id === student.class_id);
      return schoolClass ? schoolClass.class_name : '未設定';
    };
    
    const getClassGrade = (student) => {
      if (!student.class_id) return '?';
      const schoolClass = classes.value.find(c => c.class_id === student.class_id);
      return schoolClass ? schoolClass.grade : '?';
    };
    
    // Focus management for individual mode
    const focusNextField = (currentField) => {
      if (currentField === 'heightInput') {
        // Height -> Weight or Vision Left
        if (measurementItems.weight && weightInput.value) {
          weightInput.value.$el.querySelector('input').focus();
        } else if (measurementItems.vision && visionLeftInput.value) {
          visionLeftInput.value.$el.querySelector('input').focus();
        }
        // Don't jump to notes - user can tab there manually if needed
      } else if (currentField === 'weightInput') {
        // Weight -> Vision Left (skip notes)
        if (measurementItems.vision && visionLeftInput.value) {
          visionLeftInput.value.$el.querySelector('input').focus();
        }
        // Don't jump to notes - user can tab there manually if needed
      } else if (currentField === 'visionLeftInput') {
        // Vision Left -> Vision Right
        if (visionRightInput.value) {
          visionRightInput.value.$el.querySelector('input').focus();
        }
      } else if (currentField === 'visionRightInput') {
        // Vision Right -> Vision Left Corrected
        if (visionLeftCorrectedInput.value) {
          visionLeftCorrectedInput.value.$el.querySelector('input').focus();
        }
        // Don't jump to notes
      } else if (currentField === 'visionLeftCorrectedInput') {
        // Vision Left Corrected -> Vision Right Corrected
        if (visionRightCorrectedInput.value) {
          visionRightCorrectedInput.value.$el.querySelector('input').focus();
        }
      } else if (currentField === 'visionRightCorrectedInput') {
        // Last field - don't jump to notes
        // User can manually tab to notes or submit button
      }
    };
    
    // Bulk input refs management
    const setBulkInputRef = (el, studentId, fieldType) => {
      if (el) {
        if (!bulkInputRefs[studentId]) {
          bulkInputRefs[studentId] = {};
        }
        bulkInputRefs[studentId][fieldType] = el;
      }
    };
    
    // Focus management for bulk mode
    const focusBulkNextField = (currentStudentId, currentField) => {
      const studentIds = selectedStudentIds.value;
      const currentIndex = studentIds.indexOf(currentStudentId);
      
      // Define field order
      const fieldOrder = ['height', 'weight', 'vision_left', 'vision_right', 'vision_left_corrected', 'vision_right_corrected'];
      const currentFieldIndex = fieldOrder.indexOf(currentField);
      
      // Try to focus next field in the same student
      for (let i = currentFieldIndex + 1; i < fieldOrder.length; i++) {
        const nextFieldName = fieldOrder[i];
        
        // Check if this field is enabled
        if (nextFieldName === 'height' && !measurementItems.height) continue;
        if (nextFieldName === 'weight' && !measurementItems.weight) continue;
        if (['vision_left', 'vision_right', 'vision_left_corrected', 'vision_right_corrected'].includes(nextFieldName) && !measurementItems.vision) continue;
        
        // Try to focus this field
        const nextFieldRef = bulkInputRefs[currentStudentId]?.[nextFieldName];
        if (nextFieldRef) {
          // Direct input element (table format)
          nextFieldRef.focus();
          return;
        }
      }
      
      // No more fields for current student, move to next student
      moveToNextStudent(currentIndex, studentIds);
    };
    
    // Helper function to move to next student's first field
    const moveToNextStudent = (currentIndex, studentIds) => {
      if (currentIndex >= studentIds.length - 1) {
        // Last student - do nothing or could focus submit button
        return;
      }
      
      const nextStudentId = studentIds[currentIndex + 1];
      
      // Define field order to try
      const fieldOrder = ['height', 'weight', 'vision_left'];
      
      // Try to focus first available field for next student
      for (const fieldName of fieldOrder) {
        // Check if this field is enabled
        if (fieldName === 'height' && !measurementItems.height) continue;
        if (fieldName === 'weight' && !measurementItems.weight) continue;
        if (fieldName === 'vision_left' && !measurementItems.vision) continue;
        
        // Try to focus this field
        const nextFieldRef = bulkInputRefs[nextStudentId]?.[fieldName];
        if (nextFieldRef) {
          try {
            // Direct input element (table format)
            nextFieldRef.focus();
            return;
          } catch (e) {
            console.error('Error focusing field:', e);
          }
        }
      }
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
    
    const getStudentClassDisplay = (student) => {
      // Try to find the class from classes array using class_id
      const studentClass = classes.value.find(c => c.class_id === student.class_id);
      
      if (studentClass) {
        return studentClass.class_name || studentClass.name;
      }
      
      // Fallback: use grade_id if available
      if (student.grade_id) {
        return `${student.grade_id}年`;
      }
      
      return 'クラス未設定';
    };
    
    const validateForm = () => {
      errors.value = {};
      
      if (!form.measured_date) {
        errors.value.measured_date = '測定日は必須です';
      }
      
      if (!form.academic_year) {
        errors.value.academic_year = '年度を選択してください';
      }
      
      // 少なくとも1つの測定項目が選択されているか確認
      if (!measurementItems.height && !measurementItems.weight && !measurementItems.vision) {
        notificationStore.showError('少なくとも1つの測定項目を選択してください');
        return false;
      }
      
      if (selectionMethod.value === 'individual') {
        if (!selectedStudent.value) {
          errors.value.student = '学生を選択してください';
          return false;
        }
        
        // 選択された項目のバリデーション
        if (measurementItems.height && form.height) {
          if (parseFloat(form.height) < 50 || parseFloat(form.height) > 300) {
            errors.value.height = '身長は50-300cmの範囲で入力してください';
          }
        }
        
        if (measurementItems.weight && form.weight) {
          if (parseFloat(form.weight) < 10 || parseFloat(form.weight) > 200) {
            errors.value.weight = '体重は10-200kgの範囲で入力してください';
          }
        }
        
        if (measurementItems.vision) {
          if (form.vision_left && !['A', 'B', 'C', 'D'].includes(form.vision_left)) {
            errors.value.vision_left = '視力はA, B, C, Dのいずれかを選択してください';
          }
          if (form.vision_right && !['A', 'B', 'C', 'D'].includes(form.vision_right)) {
            errors.value.vision_right = '視力はA, B, C, Dのいずれかを選択してください';
          }
          if (form.vision_left_corrected && !['A', 'B', 'C', 'D'].includes(form.vision_left_corrected)) {
            errors.value.vision_left_corrected = '矯正視力はA, B, C, Dのいずれかを選択してください';
          }
          if (form.vision_right_corrected && !['A', 'B', 'C', 'D'].includes(form.vision_right_corrected)) {
            errors.value.vision_right_corrected = '矯正視力はA, B, C, Dのいずれかを選択してください';
          }
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
      form.vision_left = '';
      form.vision_right = '';
      form.vision_left_corrected = '';
      form.vision_right_corrected = '';
      form.notes = '';
      
      selectedStudent.value = null;
      selectedStudentIds.value = [];
      studentSearch.value = '';
      searchResults.value = [];
      
      bulkFilters.academic_year = new Date().getFullYear().toString();
      bulkFilters.grade = '';
      bulkFilters.class_id = '';
      bulkFilters.gender = '';
      
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
            student_id: selectedStudent.value.student_id,
            year: parseInt(form.academic_year), // academic_year → year
            measured_date: form.measured_date || null,
            height: measurementItems.height && form.height ? parseFloat(form.height) : null,
            weight: measurementItems.weight && form.weight ? parseFloat(form.weight) : null,
            vision_left: measurementItems.vision && form.vision_left ? form.vision_left : null,
            vision_right: measurementItems.vision && form.vision_right ? form.vision_right : null,
            vision_left_corrected: measurementItems.vision && form.vision_left_corrected ? form.vision_left_corrected : null,
            vision_right_corrected: measurementItems.vision && form.vision_right_corrected ? form.vision_right_corrected : null,
            ophthalmology_result: measurementItems.ophthalmology && form.ophthalmology_result ? form.ophthalmology_result : null,
            ophthalmology_exam_result: measurementItems.ophthalmology && form.ophthalmology_exam_result ? form.ophthalmology_exam_result : null,
            ophthalmology_diagnosis: measurementItems.ophthalmology && form.ophthalmology_diagnosis ? form.ophthalmology_diagnosis : null,
            ophthalmology_treatment: measurementItems.ophthalmology && form.ophthalmology_treatment ? form.ophthalmology_treatment : null,
            otolaryngology_result: measurementItems.otolaryngology && form.otolaryngology_result ? form.otolaryngology_result : null,
            internal_medicine_result: measurementItems.internal_medicine && form.internal_medicine_result ? form.internal_medicine_result : null,
            hearing_test_result: measurementItems.hearing_test && form.hearing_test_result ? form.hearing_test_result : null,
            tuberculosis_test_result: measurementItems.tuberculosis_test && form.tuberculosis_test_result ? form.tuberculosis_test_result : null,
            urine_test_result: measurementItems.urine_test && form.urine_test_result ? form.urine_test_result : null,
            ecg_result: measurementItems.ecg && form.ecg_result ? form.ecg_result : null,
            notes: form.notes || null
          };
          
          console.log('=== Individual Record Submission ===');
          console.log('Form data:', form);
          console.log('form.academic_year:', form.academic_year, 'Type:', typeof form.academic_year);
          console.log('parseInt(form.academic_year):', parseInt(form.academic_year));
          console.log('Selected student:', selectedStudent.value);
          console.log('Record data to submit:', recordData);
          console.log('===================================');
          
          const newRecord = await healthRecordStore.createHealthRecord(recordData);
          
          notificationStore.addNotification({
            type: 'success',
            title: '記録作成完了',
            message: `${selectedStudent.value.name}さんの健康記録を作成しました`
          });
          
          router.push(`/health-records/${newRecord.id}`);
        } else {
          // Bulk record creation with measurements
          const recordsData = selectedStudentIds.value.map(studentId => {
            const student = bulkStudents.value.find(s => s.id === studentId);
            const measurement = bulkMeasurements[studentId] || {};
            return {
              student_id: student.student_id,
              year: parseInt(form.academic_year), // academic_year → year
              measured_date: form.measured_date || null,
              height: measurementItems.height && measurement.height ? parseFloat(measurement.height) : null,
              weight: measurementItems.weight && measurement.weight ? parseFloat(measurement.weight) : null,
              vision_left: measurementItems.vision && measurement.vision_left ? measurement.vision_left : null,
              vision_right: measurementItems.vision && measurement.vision_right ? measurement.vision_right : null,
              vision_left_corrected: measurementItems.vision && measurement.vision_left_corrected ? measurement.vision_left_corrected : null,
              vision_right_corrected: measurementItems.vision && measurement.vision_right_corrected ? measurement.vision_right_corrected : null,
              ophthalmology_result: measurementItems.ophthalmology && form.ophthalmology_result ? form.ophthalmology_result : null,
              ophthalmology_exam_result: measurementItems.ophthalmology && measurement.ophthalmology_exam_result ? measurement.ophthalmology_exam_result : null,
              ophthalmology_diagnosis: measurementItems.ophthalmology && measurement.ophthalmology_diagnosis ? measurement.ophthalmology_diagnosis : null,
              ophthalmology_treatment: measurementItems.ophthalmology && measurement.ophthalmology_treatment ? measurement.ophthalmology_treatment : null,
              otolaryngology_result: measurementItems.otolaryngology && form.otolaryngology_result ? form.otolaryngology_result : null,
              internal_medicine_result: measurementItems.internal_medicine && form.internal_medicine_result ? form.internal_medicine_result : null,
              hearing_test_result: measurementItems.hearing_test && form.hearing_test_result ? form.hearing_test_result : null,
              tuberculosis_test_result: measurementItems.tuberculosis_test && form.tuberculosis_test_result ? form.tuberculosis_test_result : null,
              urine_test_result: measurementItems.urine_test && form.urine_test_result ? form.urine_test_result : null,
              ecg_result: measurementItems.ecg && form.ecg_result ? form.ecg_result : null,
              notes: form.notes || null
            };
          });
          
          const result = await healthRecordStore.createBulkHealthRecords(recordsData);
          
          notificationStore.addNotification({
            type: 'success',
            title: '一括記録処理完了',
            message: `${selectedStudentIds.value.length}名の健康記録を処理しました`
          });
          
          router.push('/health-records');
        }
      } catch (error) {
        console.error('=== Health Record Creation Error ===');
        console.error('Error:', error);
        console.error('Response:', error.response);
        console.error('Response data:', error.response?.data);
        console.error('Validation errors:', error.response?.data?.errors);
        console.error('===================================');
        
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
    
    // Watch selectedStudentIds to initialize/cleanup bulk measurements
    watch(selectedStudentIds, (newIds, oldIds) => {
      if (selectionMethod.value === 'bulk') {
        // Initialize measurements for newly added students
        newIds.forEach(id => {
          if (!bulkMeasurements[id]) {
            initializeBulkMeasurement(id);
          }
        });
        
        // Clean up measurements for removed students
        if (oldIds) {
          oldIds.forEach(id => {
            if (!newIds.includes(id) && bulkMeasurements[id]) {
              delete bulkMeasurements[id];
            }
          });
        }
      }
    });
    
    // Watch measurementItems for debugging
    watch(() => measurementItems.ophthalmology, (newVal) => {
      console.log('Ophthalmology checkbox changed:', newVal);
      console.log('measurementItems:', measurementItems);
    });
    
    // Lifecycle
    onMounted(async () => {
      try {
        await Promise.all([
          studentStore.fetchAllStudents(), // 全件取得
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
      measurementItems,
      form,
      bulkFilters,
      bulkMeasurements,
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
      heightInput,
      weightInput,
      visionLeftInput,
      visionRightInput,
      visionLeftCorrectedInput,
      visionRightCorrectedInput,
      notesInput,
      searchStudents,
      selectStudent,
      clearSelectedStudent,
      updateBulkSelection,
      toggleAllStudents,
      toggleStudentSelection,
      initializeBulkMeasurements,
      initializeBulkMeasurement,
      calculateBulkBMI,
      calculateBMI,
      calculateAge,
      formatDate,
      focusNextField,
      setBulkInputRef,
      focusBulkNextField,
      getBMICategory,
      getBMIVariant,
      getBMIColor,
      getStudentClassDisplay,
      getClassName,
      getClassGrade,
      resetForm,
      handleSubmit
    };
  }
};
</script>

<style scoped>
/* Sticky columns styling */
.sticky {
  position: sticky;
  background-color: white;
}

.sticky.left-0 {
  left: 0;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

/* Header sticky background */
thead .sticky.bg-gray-50 {
  background-color: rgb(249 250 251);
}

/* Row hover effect with sticky columns */
tbody tr:hover .sticky {
  background-color: rgb(249 250 251);
}

/* Second sticky column with dynamic left position */
.sticky[style*="left: 4rem"] {
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

/* Table input styling */
table input[type="number"] {
  min-width: 70px;
}

/* Remove number input arrows for cleaner look */
table input[type="number"]::-webkit-inner-spin-button,
table input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

table input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

/* Compact table cells */
table td,
table th {
  line-height: 1.2;
}
</style>