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
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-4">
      <!-- Tab Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            @click="activeTab = 'attendance'"
            :class="[
              activeTab === 'attendance'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            出席情報
          </button>
          <button
            @click="switchToNursingTab"
            :class="[
              activeTab === 'nursing'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            保健室利用状況
          </button>
        </nav>
      </div>

      <!-- Attendance Tab Content -->
      <div v-show="activeTab === 'attendance'">
        <!-- Date and Class Selection -->
        <BaseCard>
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
        </template>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Date Selection (Scroll Type) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              日付 <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
              <select
                v-model="dateComponents.year"
                @change="updateDate"
                class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
              </select>
              <select
                v-model="dateComponents.month"
                @change="updateDate"
                class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
              </select>
              <select
                v-model="dateComponents.day"
                @change="updateDate"
                class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="d in daysInMonth" :key="d" :value="d">{{ d }}日</option>
              </select>
            </div>
          </div>
          
          <BaseInput
            type="select"
            v-model="form.class_id"
            label="クラス"
            @change="loadStudents"
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

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              学生名検索
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="名前、出席番号..."
                @input="loadStudentsBySearch"
              />
              <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button
                  @click="searchQuery = ''"
                  class="text-gray-400 hover:text-gray-500"
                  type="button"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <p v-if="students.length > 0 && searchQuery" class="mt-1 text-xs text-gray-500">
              {{ filteredStudents.length }}人 / {{ students.length }}人
            </p>
          </div>
        </div>
      </BaseCard>

      <!-- Student Attendance Input -->
      <BaseCard v-if="students.length > 0">
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              出席状況入力
              <span v-if="searchQuery" class="ml-2 text-sm font-normal text-gray-500">
                ({{ filteredStudents.length }}人 / {{ students.length }}人)
              </span>
            </h2>
            <div class="flex space-x-2">
              <BaseButton
                variant="secondary"
                size="sm"
                @click="setAllStatus('present')"
              >
                全員出席
              </BaseButton>
            </div>
          </div>
        </template>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  出席番号
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  学生名
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  状態
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  到着時刻
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  退出時刻
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  備考
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="filteredStudents.length === 0" class="bg-yellow-50">
                <td colspan="6" class="px-6 py-8 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="h-12 w-12 text-yellow-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900">検索条件に一致する学生が見つかりません</p>
                    <p class="mt-1 text-sm text-gray-500">「{{ searchQuery }}」で検索中</p>
                    <button
                      @click="searchQuery = ''"
                      class="mt-3 text-sm text-blue-600 hover:text-blue-500 font-medium"
                    >
                      検索をクリア
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-for="student in filteredStudents" :key="student.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ student.student_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ student.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select
                    v-model="attendanceData[student.id].status"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    @change="onStatusChange(student.id)"
                  >
                    <option value="present">出席</option>
                    <option value="absent">欠席</option>
                    <option value="late">遅刻</option>
                    <option value="early_leave">早退</option>
                    <option value="sick_leave">病欠</option>
                    <option value="official_leave">公欠</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    v-if="['late'].includes(attendanceData[student.id].status)"
                    type="time"
                    v-model="attendanceData[student.id].arrival_time"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    v-if="['early_leave'].includes(attendanceData[student.id].status)"
                    type="time"
                    v-model="attendanceData[student.id].departure_time"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4">
                  <input
                    type="text"
                    v-model="attendanceData[student.id].notes"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="備考"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="cancel"
          >
            キャンセル
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="saveAttendance"
            :disabled="saving"
          >
            {{ saving ? '保存中...' : '保存' }}
          </BaseButton>
        </div>
      </BaseCard>

      <!-- Empty State -->
      <BaseCard v-else-if="form.class_id">
        <div class="text-center py-12">
          <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">学生がいません</h3>
          <p class="mt-1 text-sm text-gray-500">
            選択されたクラスに学生が登録されていません
          </p>
        </div>
      </BaseCard>
      </div>

      <!-- Nursing Visit Tab Content -->
      <div v-show="activeTab === 'nursing'">
        <!-- Date Selection -->
        <BaseCard>
          <template #header>
            <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
          </template>

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-1">
            <!-- Date Selection (Scroll Type) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                日付 <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-3 gap-2 max-w-md">
                <select
                  v-model="nursingDateComponents.year"
                  @change="updateNursingDate"
                  class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
                </select>
                <select
                  v-model="nursingDateComponents.month"
                  @change="updateNursingDate"
                  class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
                </select>
                <select
                  v-model="nursingDateComponents.day"
                  @change="updateNursingDate"
                  class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option v-for="d in nursingDaysInMonth" :key="d" :value="d">{{ d }}日</option>
                </select>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Nursing Visit Records -->
        <BaseCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">保健室来室記録</h2>
              <BaseButton
                variant="primary"
                size="sm"
                @click="addNursingVisit"
              >
                来室記録追加
              </BaseButton>
            </div>
          </template>

          <!-- Nursing Visit Records -->
          <div class="space-y-3">
            <div v-if="nursingVisits.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
              <p class="text-sm text-gray-500">
                来室記録がありません。「来室記録追加」ボタンから記録を追加してください。
              </p>
            </div>
            
            <!-- Individual Visit Card -->
            <div
              v-for="(visit, index) in nursingVisits"
              :key="index"
              class="bg-white border border-gray-300 rounded-lg p-3 space-y-3"
            >
              <!-- Card Header -->
              <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">来室記録 #{{ index + 1 }}</h3>
                <button
                  @click="removeNursingVisit(index)"
                  type="button"
                  class="text-red-600 hover:text-red-900"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>

              <!-- Time -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    来室時間 <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="time"
                    v-model="visit.time"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>
              </div>

              <!-- Student Information -->
              <div class="space-y-3 bg-gray-50 p-3 rounded-md">
                <h4 class="text-sm font-medium text-gray-700">学生情報</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">クラス <span class="text-red-500">*</span></label>
                    <select
                      v-model="visit.selectedClass"
                      @change="onClassChange(index)"
                      class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">選択してください</option>
                      <option
                        v-for="cls in classes"
                        :key="cls.id"
                        :value="cls.class_id"
                      >
                        {{ cls.name }}
                      </option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                      出席番号 <span class="text-red-500">*</span>
                    </label>
                    <input
                      type="number"
                      v-model.number="visit.selected_student_number"
                      @input="onStudentNumberSelect(index)"
                      class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :disabled="!visit.selectedClass"
                      placeholder="番号を入力"
                      min="1"
                      max="99"
                    />
                    <p v-if="visit.selectedClass && visit.selected_student_number && !visit.student_id" class="mt-1 text-xs text-red-600">
                      該当する学生が見つかりません
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">学生名</label>
                    <div class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm bg-white sm:text-sm min-h-[38px] flex items-center">
                      <span v-if="visit.student_id" class="text-gray-900 font-medium">
                        {{ getStudentById(visit.student_id)?.name }} ({{ getGenderLabel(getStudentById(visit.student_id)?.gender) }})
                      </span>
                      <span v-else class="text-gray-400 text-xs">
                        クラスと出席番号を選択
                      </span>
                    </div>
                  </div>
                </div>
                
                <!-- Selected Student Display -->
                <div v-if="visit.student_id" class="p-2 bg-blue-50 rounded-md">
                  <div class="flex items-center justify-between">
                    <div class="text-xs">
                      <span class="font-medium text-gray-900">{{ getStudentById(visit.student_id)?.name }}</span>
                      <div class="text-gray-600 mt-1">
                        {{ getStudentById(visit.student_id)?.schoolClass?.grade || '-' }}年 
                        {{ getStudentById(visit.student_id)?.schoolClass?.name?.replace(/[0-9]年/, '') || '-' }}組 
                        {{ getStudentById(visit.student_id)?.student_number || '-' }}番 
                        ({{ getGenderLabel(getStudentById(visit.student_id)?.gender) }})
                      </div>
                    </div>
                    <button
                      @click="clearStudent(index)"
                      type="button"
                      class="ml-2 text-gray-400 hover:text-gray-600"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Category and Type -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    分類 <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="visit.category"
                    @change="onCategoryChange(index)"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="internal">内科</option>
                    <option value="surgical">外科</option>
                    <option value="other">その他</option>
                    <option value="absence">欠席</option>
                    <option value="late">遅刻他</option>
                  </select>
                </div>
                
                <!-- 種別 (内科・外科・その他の場合のみ) -->
                <div v-if="visit.category && visit.category !== 'absence' && visit.category !== 'late'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    種別
                  </label>
                  <select
                    v-model="visit.type_detail"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :disabled="!visit.category"
                  >
                    <option value="">選択してください</option>
                    <template v-if="visit.category === 'internal'">
                      <option value="headache">頭痛</option>
                      <option value="stomachache">腹痛</option>
                      <option value="stomach_pain">胃痛</option>
                      <option value="cold">かぜ症状</option>
                      <option value="diarrhea">下痢</option>
                      <option value="nausea">吐き気・嘔吐</option>
                      <option value="constipation">便秘</option>
                      <option value="menstrual_pain">月経痛</option>
                      <option value="toothache">歯痛</option>
                      <option value="sleep_deprivation">睡眠不足</option>
                      <option value="feeling_sick">気分が悪い</option>
                      <option value="asthma">喘息</option>
                      <option value="poor_health">体調不良</option>
                      <option value="hyperventilation">過呼吸</option>
                      <option value="fatigue">倦怠感</option>
                      <option value="dizziness">めまい・貧血</option>
                      <option value="seizure">発作</option>
                      <option value="fever">発熱</option>
                      <option value="other">その他</option>
                    </template>
                    <template v-else-if="visit.category === 'surgical'">
                      <option value="scrape">すり傷</option>
                      <option value="cut">切傷</option>
                      <option value="stab">刺傷</option>
                      <option value="bruise">打撲・打ち身</option>
                      <option value="sprain">捻挫</option>
                      <option value="finger_jam">突き指</option>
                      <option value="muscle_pain">筋肉痛</option>
                      <option value="nosebleed">鼻出血</option>
                      <option value="eye_pain">眼痛</option>
                      <option value="back_pain">腰痛</option>
                      <option value="fracture">骨折・脱臼</option>
                      <option value="hives">じんましん</option>
                      <option value="suppuration">化膿</option>
                      <option value="ear_pain">耳痛</option>
                      <option value="burn">火傷</option>
                      <option value="insect_bite">虫さされ</option>
                      <option value="nail_detachment">爪剥離</option>
                      <option value="skin_condition">皮むけ・皮膚疾患</option>
                      <option value="foot_pain">足痛</option>
                      <option value="pain">痛み</option>
                      <option value="concussion">脳震盪</option>
                      <option value="wound_disinfection">傷口消毒</option>
                      <option value="tooth_extraction">抜歯・歯が欠ける</option>
                      <option value="other">その他</option>
                    </template>
                    <template v-else-if="visit.category === 'other'">
                      <option value="mental_counseling">こころ(相談)</option>
                      <option value="physical_counseling">からだ(相談)</option>
                      <option value="somehow">何となく</option>
                      <option value="psychogenic">心因性</option>
                      <option value="call_waiting">呼び出し・待機</option>
                      <option value="measurement">計測等</option>
                      <option value="infirmary_attendance">保健室登校</option>
                      <option value="other_counseling">その他の相談</option>
                      <option value="safe_place">居場所</option>
                      <option value="observation">経過観察</option>
                      <option value="infirmary_exam">保健室受験</option>
                      <option value="other">その他</option>
                    </template>
                  </select>
                </div>

                <!-- 原因・理由 (欠席・遅刻他の場合) -->
                <div v-if="visit.category === 'absence' || visit.category === 'late'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    原因・理由
                  </label>
                  <select
                    v-model="visit.absence_reason"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <template v-if="visit.category === 'absence'">
                      <option value="headache">頭痛</option>
                      <option value="cold">かぜ症状</option>
                      <option value="stomachache">腹痛</option>
                      <option value="diarrhea">下痢</option>
                      <option value="fever">発熱</option>
                      <option value="sleep_deprivation">睡眠不足</option>
                      <option value="asthma">喘息</option>
                      <option value="nausea">吐き気・嘔吐</option>
                      <option value="nephritis">腎炎</option>
                      <option value="injury">外傷</option>
                      <option value="ent_disease">耳鼻疾患</option>
                      <option value="influenza">インフルエンザ</option>
                      <option value="chickenpox">水痘</option>
                      <option value="mumps">耳下腺炎</option>
                      <option value="epidemic_keratitis">はやり目</option>
                      <option value="other_infectious">その他の伝染病</option>
                      <option value="accident">事故欠</option>
                      <option value="unknown">理由不明</option>
                      <option value="mourning">忌引</option>
                      <option value="housework">家事</option>
                      <option value="poor_health">体調不良</option>
                      <option value="hospitalization">入院</option>
                      <option value="truancy">不登校</option>
                      <option value="hospital_visit">通院</option>
                      <option value="psychogenic">心因性</option>
                      <option value="laziness">怠惰</option>
                      <option value="appendicitis">虫垂炎</option>
                      <option value="covid19">コロナウイルス感染症</option>
                      <option value="orthostatic">起立性調節障害</option>
                      <option value="other">その他</option>
                    </template>
                    <template v-else-if="visit.category === 'late'">
                      <option value="hospital_visit">通院</option>
                      <option value="poor_health">体調不良</option>
                      <option value="carelessness">不注意</option>
                      <option value="truancy_tendency">不登校傾向</option>
                      <option value="counseling_room">相談室登校</option>
                      <option value="other">その他</option>
                    </template>
                  </select>
                </div>
              </div>

              <!-- 外科専用フィールド -->
              <div v-if="visit.category === 'surgical'" class="space-y-2 bg-red-50 p-2.5 rounded-md">
                <h4 class="text-sm font-medium text-gray-700">外科関連情報</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- 怪我の部位 -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      怪我の部位
                    </label>
                    <select
                      v-model="visit.injury_location"
                      class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">選択してください</option>
                      <option value="hand_wrist">手・手首</option>
                      <option value="arm">腕</option>
                      <option value="finger_hand">指（手）</option>
                      <option value="finger_foot">指（足）</option>
                      <option value="foot_ankle">足・足首</option>
                      <option value="leg">脚</option>
                      <option value="knee">膝</option>
                      <option value="head">頭</option>
                      <option value="face">顔</option>
                      <option value="abdomen">腹</option>
                      <option value="chest">胸</option>
                      <option value="waist">腰</option>
                      <option value="back_shoulder_neck">背中・肩・首</option>
                      <option value="tooth">歯</option>
                      <option value="eye">眼</option>
                      <option value="ear">耳</option>
                      <option value="nose">鼻</option>
                      <option value="other">その他</option>
                    </select>
                  </div>

                  <!-- 発生場所 -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      発生場所
                    </label>
                    <select
                      v-model="visit.injury_place"
                      class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">選択してください</option>
                      <option value="classroom">教室</option>
                      <option value="hallway">廊下</option>
                      <option value="stairs">階段</option>
                      <option value="gymnasium">体育館</option>
                      <option value="ground">グランド</option>
                      <option value="schoolyard">校庭</option>
                      <option value="music_room">音楽室</option>
                      <option value="art_room">美術室</option>
                      <option value="science_room">理科室</option>
                      <option value="auditorium">講堂</option>
                      <option value="home_economics">家庭科室</option>
                      <option value="cooking_room">調理室</option>
                      <option value="computer_room">コンピュータ室</option>
                      <option value="commute_route">通学路</option>
                      <option value="toilet">トイレ</option>
                      <option value="unknown">不明</option>
                      <option value="bicycle_parking">自転車置き場</option>
                      <option value="martial_arts">格技室</option>
                      <option value="practice_room">実習室</option>
                      <option value="cafeteria">食堂</option>
                      <option value="other">その他</option>
                    </select>
                  </div>
                </div>

                <!-- 外科の処置 -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    処置
                  </label>
                  <select
                    v-model="visit.surgical_treatment"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="disinfection">処置（消毒）</option>
                    <option value="icing">処置（アイシング・湿布）</option>
                    <option value="warm_compress">温罨法</option>
                    <option value="foreign_removal">異物除去</option>
                    <option value="parent_contact">保護者連絡</option>
                    <option value="hospital_instruction">病院受診を指示</option>
                    <option value="hospital_transport">病院へ搬送</option>
                    <option value="observation">経過観察</option>
                    <option value="rest_observation">経過観察（安静）</option>
                    <option value="teacher_contact">担任・部活顧問連絡</option>
                    <option value="bandaid">カットバン貼付</option>
                    <option value="ointment">塗り薬</option>
                    <option value="other">その他</option>
                  </select>
                </div>
              </div>

              <!-- Occurrence Time and Conditional Fields -->
              <div class="space-y-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    発生時
                  </label>
                  <select
                    v-model="visit.occurrence_time"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="during_class">授業中</option>
                    <option value="self_study">自習中</option>
                    <option value="before_school">始業前</option>
                    <option value="break">休憩時間</option>
                    <option value="lunch">昼休み</option>
                    <option value="cleaning">掃除中</option>
                    <option value="after_school">放課後</option>
                    <option value="club">部活動</option>
                    <option value="commute">登下校中</option>
                    <option value="event">行事</option>
                    <option value="exam">試験中</option>
                    <option value="supplementary">補習</option>
                    <option value="extracurricular">課外授業</option>
                    <option value="other">その他</option>
                  </select>
                </div>

                <!-- Subject (shown when occurrence_time is class-related) -->
                <div v-if="['during_class', 'exam', 'supplementary', 'extracurricular'].includes(visit.occurrence_time)">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    教科
                  </label>
                  <select
                    v-model="visit.subject"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="japanese">国語</option>
                    <option value="social_studies">社会</option>
                    <option value="mathematics">数学</option>
                    <option value="science">理科</option>
                    <option value="english">英語</option>
                    <option value="music">音楽</option>
                    <option value="art">美術</option>
                    <option value="technology">技術</option>
                    <option value="home_economics">家庭科</option>
                    <option value="health_pe">保健体育</option>
                    <option value="lhr">LHR</option>
                    <option value="integrated">総合</option>
                    <option value="commerce">商業</option>
                    <option value="welfare">福祉</option>
                    <option value="industrial">工業</option>
                    <option value="information">情報</option>
                    <option value="research">課題研究</option>
                    <option value="life_culture">生活教養</option>
                    <option value="calligraphy">書道</option>
                    <option value="elective">選択</option>
                    <option value="childcare">保育</option>
                    <option value="other">その他</option>
                  </select>
                </div>

                <!-- Club (shown when occurrence_time is club) -->
                <div v-if="visit.occurrence_time === 'club'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    部活
                  </label>
                  <select
                    v-model="visit.club"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="volleyball">バレー</option>
                    <option value="basketball">バスケット</option>
                    <option value="tennis">テニス</option>
                    <option value="soccer">サッカー</option>
                    <option value="baseball">野球</option>
                    <option value="track_field">陸上</option>
                    <option value="art">美術</option>
                    <option value="brass_band">吹奏楽</option>
                    <option value="judo">柔道</option>
                    <option value="cycling">自転車競技</option>
                    <option value="golf">ゴルフ</option>
                    <option value="table_tennis">卓球</option>
                    <option value="archery">弓道</option>
                    <option value="other">その他</option>
                  </select>
                </div>

                <!-- Event (shown when occurrence_time is event) -->
                <div v-if="visit.occurrence_time === 'event'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    行事
                  </label>
                  <select
                    v-model="visit.event"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="school_trip">修学旅行</option>
                    <option value="class_match">クラスマッチ</option>
                    <option value="monthly_address">月頭訓話</option>
                    <option value="sports_day">運動会</option>
                    <option value="field_training">郊外研修・実習</option>
                    <option value="culture_festival">文化祭</option>
                    <option value="lecture">講演会</option>
                    <option value="assembly">全校・学年別集会</option>
                    <option value="entrance_ceremony">入学式</option>
                    <option value="graduation_ceremony">卒業式</option>
                    <option value="student_assembly">生徒総会</option>
                    <option value="opening_ceremony">始業式</option>
                    <option value="closing_ceremony">終業式</option>
                    <option value="graduation_rehearsal">卒業式予行</option>
                    <option value="clean_operation">クリーン作戦</option>
                    <option value="open_school">オープンスクール</option>
                    <option value="other">その他</option>
                  </select>
                </div>
              </div>

              <!-- Internal Medicine Specific Fields -->
              <div v-if="visit.category === 'internal'" class="space-y-2 bg-blue-50 p-2.5 rounded-md">
                <h4 class="text-sm font-medium text-gray-700">内科関連情報</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- Breakfast -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      朝食
                    </label>
                    <select
                      v-model="visit.breakfast"
                      class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">選択してください</option>
                      <option value="ate">食べた</option>
                      <option value="not_ate">食べていない</option>
                      <option value="never_eat">いつも食べない</option>
                      <option value="no_appetite">欲しくない</option>
                      <option value="no_time">時間がない</option>
                      <option value="other">その他</option>
                    </select>
                  </div>

                  <!-- Bowel Movement -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      便通
                    </label>
                    <select
                      v-model="visit.bowel_movement"
                      class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">選択してください</option>
                      <option value="this_morning">今朝した</option>
                      <option value="normal">普通便</option>
                      <option value="diarrhea">下痢便</option>
                      <option value="not_this_morning">今朝はしなかった</option>
                      <option value="never_morning">朝はいつもしない</option>
                      <option value="no_time">時間がなかった</option>
                      <option value="constipated">便秘ぎみ</option>
                      <option value="no_urge">便意がなかった</option>
                      <option value="other">その他</option>
                    </select>
                  </div>
                </div>

                <!-- Treatment for Internal -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    処置
                  </label>
                  <select
                    v-model="visit.treatment"
                    class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">選択してください</option>
                    <option value="observe_classroom">教室で観察</option>
                    <option value="rest_infirmary">保健室で休養</option>
                    <option value="health_consultation">健康相談</option>
                    <option value="infirmary_attendance">保健室登校</option>
                    <option value="infirmary_exam">保健室試験</option>
                    <option value="safe_place">居場所</option>
                    <option value="rest_early_leave">休養早退</option>
                    <option value="early_leave">早退</option>
                    <option value="hospital_visit">病院受診</option>
                    <option value="separate_exam">別室受験</option>
                    <option value="separate_move">別室移動</option>
                    <option value="other">その他</option>
                  </select>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  備考・原因・その他
                </label>
                <textarea
                  v-model="visit.treatment_notes"
                  rows="2"
                  class="block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="体温37.2度、冷却、休養後下校"
                ></textarea>
              </div>
            </div>
          </div>

          <div class="mt-4 flex justify-end space-x-3">
            <BaseButton
              variant="secondary"
              @click="cancel"
            >
              キャンセル
            </BaseButton>
            <BaseButton
              variant="primary"
              @click="saveNursingVisits"
              :disabled="savingNursing"
            >
              {{ savingNursing ? '保存中...' : '保存' }}
            </BaseButton>
          </div>
        </BaseCard>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClassStore } from '@/stores/class.js';
import { useStudentStore } from '@/stores/student.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton
} from '@/components/index.js';

// Icons
const UserGroupIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
  `
};

export default {
  name: 'AttendanceRegistrationCreate',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    UserGroupIcon
  },
  
  setup() {
    const router = useRouter();
    const classStore = useClassStore();
    const studentStore = useStudentStore();
    const notificationStore = useNotificationStore();
    
    // Tab State - Initialize based on route meta
    const recordType = router.currentRoute.value.meta.recordType || 'attendance';
    const activeTab = ref(recordType);
    
    // Attendance State
    const saving = ref(false);
    const today = new Date();
    const dateComponents = ref({
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: today.getDate()
    });
    
    const form = ref({
      date: new Date().toISOString().split('T')[0],
      class_id: ''
    });
    
    const students = ref([]);
    const attendanceData = ref({});
    const searchQuery = ref('');
    
    // Nursing Visit State
    const savingNursing = ref(false);
    const nursingDateComponents = ref({
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: today.getDate()
    });
    
    const nursingForm = ref({
      date: new Date().toISOString().split('T')[0],
      class_id: ''
    });
    
    const nursingVisits = ref([]);
    const nursingStudents = ref([]);
    const nursingSearchQuery = ref('');
    
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
      if (recordType === 'nursing') {
        return 'クラス単位で保健室来室記録を入力します';
      }
      return 'クラス単位で出席状況を入力します';
    });
    
    const filteredStudents = computed(() => {
      if (!searchQuery.value) return students.value;
      
      const query = searchQuery.value.toLowerCase().trim();
      return students.value.filter(student => {
        const name = (student.name || '').toLowerCase();
        const nameKana = (student.name_kana || '').toLowerCase();
        const studentNumber = String(student.student_number || '');
        const studentId = String(student.student_id || '');
        
        return name.includes(query) ||
               nameKana.includes(query) ||
               studentNumber.includes(query) ||
               studentId.includes(query);
      });
    });
    
    const nursingDaysInMonth = computed(() => {
      const year = nursingDateComponents.value.year;
      const month = nursingDateComponents.value.month;
      return new Date(year, month, 0).getDate();
    });
    
    const filteredNursingStudents = computed(() => {
      if (!nursingSearchQuery.value) return nursingStudents.value;
      
      const query = nursingSearchQuery.value.toLowerCase().trim();
      return nursingStudents.value.filter(student => {
        const name = (student.name || '').toLowerCase();
        const nameKana = (student.name_kana || '').toLowerCase();
        const studentNumber = String(student.student_number || '');
        
        return name.includes(query) ||
               nameKana.includes(query) ||
               studentNumber.includes(query);
      });
    });
    
    // Methods
    const updateDate = () => {
      const year = dateComponents.value.year;
      const month = String(dateComponents.value.month).padStart(2, '0');
      const day = String(dateComponents.value.day).padStart(2, '0');
      form.value.date = `${year}-${month}-${day}`;
    };
    
    const loadStudents = async () => {
      try {
        const params = {};
        if (form.value.class_id) params.class_id = form.value.class_id;
        
        // If no filters are set, don't load anything
        if (!params.class_id && !searchQuery.value) {
          students.value = [];
          attendanceData.value = {};
          return;
        }
        
        await studentStore.fetchStudents(params);
        students.value = studentStore.students;
        
        // Initialize attendance data for each student
        attendanceData.value = {};
        students.value.forEach(student => {
          attendanceData.value[student.id] = {
            status: 'present',
            arrival_time: '',
            departure_time: '',
            notes: ''
          };
        });
      } catch (error) {
        console.error('Failed to load students:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '学生情報の取得に失敗しました'
        });
      }
    };
    
    const onStatusChange = (studentId) => {
      // Reset times when status changes
      if (!['late'].includes(attendanceData.value[studentId].status)) {
        attendanceData.value[studentId].arrival_time = '';
      }
      if (!['early_leave'].includes(attendanceData.value[studentId].status)) {
        attendanceData.value[studentId].departure_time = '';
      }
    };
    
    const setAllStatus = (status) => {
      Object.keys(attendanceData.value).forEach(studentId => {
        attendanceData.value[studentId].status = status;
        attendanceData.value[studentId].arrival_time = '';
        attendanceData.value[studentId].departure_time = '';
      });
    };
    
    const loadStudentsBySearch = async () => {
      if (!searchQuery.value || searchQuery.value.trim().length < 2) {
        // If search is cleared or too short, reload based on filters
        if (!form.value.class_id) {
          students.value = [];
          attendanceData.value = {};
        }
        return;
      }
      
      // Search across all students
      try {
        await studentStore.fetchStudents({
          search: searchQuery.value,
          class_id: form.value.class_id || undefined
        });
        students.value = studentStore.students;
        
        // Initialize attendance data for new students
        students.value.forEach(student => {
          if (!attendanceData.value[student.id]) {
            attendanceData.value[student.id] = {
              status: 'present',
              arrival_time: '',
              departure_time: '',
              notes: ''
            };
          }
        });
      } catch (error) {
        console.error('Failed to search students:', error);
      }
    };
    
    const saveAttendance = async () => {
      if (!form.value.date || !form.value.class_id) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '日付とクラスを選択してください'
        });
        return;
      }
      
      saving.value = true;
      try {
        // Prepare records for API
        const records = Object.entries(attendanceData.value).map(([studentId, data]) => {
          const student = filteredStudents.value.find(s => s.id === parseInt(studentId));
          return {
            student_id: student ? student.student_id : studentId,
            status: data.status,
            arrival_time: data.arrival_time || null,
            departure_time: data.departure_time || null,
            notes: data.notes || null
          };
        });
        
        // Call API to save attendance records
        const response = await fetch('/api/v1/attendance-records/bulk', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            date: form.value.date,
            records: records
          })
        });
        
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || '保存に失敗しました');
        }
        
        const result = await response.json();
        console.log('Attendance records saved:', result);
        
        notificationStore.addNotification({
          type: 'success',
          title: '保存完了',
          message: `${result.count}件の出席記録を保存しました`
        });
        
        router.push({ name: 'attendance-registration.attendance.index' });
      } catch (error) {
        console.error('Failed to save attendance:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: error.message || '保存に失敗しました'
        });
      } finally {
        saving.value = false;
      }
    };
    
    const cancel = () => {
      if (recordType === 'nursing') {
        router.push({ name: 'attendance-registration.nursing.index' });
      } else {
        router.push({ name: 'attendance-registration.attendance.index' });
      }
    };
    
    // Tab switching
    const switchToNursingTab = async () => {
      activeTab.value = 'nursing';
      // 保健室タブに切り替えた時に全学生を再読み込み
      try {
        console.log('Switching to nursing tab, reloading all students...');
        await studentStore.fetchStudents({ per_page: 1000 }); // 全学生を取得
        nursingStudents.value = studentStore.students;
        console.log('Nursing students reloaded:', nursingStudents.value.length);
      } catch (error) {
        console.error('Failed to reload students:', error);
      }
    };
    
    // Nursing Visit Methods
    const updateNursingDate = () => {
      const year = nursingDateComponents.value.year;
      const month = String(nursingDateComponents.value.month).padStart(2, '0');
      const day = String(nursingDateComponents.value.day).padStart(2, '0');
      nursingForm.value.date = `${year}-${month}-${day}`;
    };
    
    const loadStudentsForNursing = async () => {
      try {
        const params = { per_page: 1000 }; // 全学生を取得
        if (nursingForm.value.class_id) params.class_id = nursingForm.value.class_id;
        if (nursingSearchQuery.value && nursingSearchQuery.value.length >= 2) {
          params.search = nursingSearchQuery.value;
        }
        
        await studentStore.fetchStudents(params);
        nursingStudents.value = studentStore.students;
      } catch (error) {
        console.error('Failed to load students for nursing:', error);
      }
    };
    
    const addNursingVisit = () => {
      const now = new Date();
      nursingVisits.value.push({
        time: now.toTimeString().slice(0, 5), // HH:MM format
        student_id: '',
        category: 'internal',
        type: 'illness',
        type_detail: '',
        occurrence_time: '',
        subject: '', // 教科
        club: '', // 部活
        event: '', // 行事
        treatment_notes: '',
        breakfast: '', // 朝食
        bowel_movement: '', // 便通
        treatment: '', // 処置（内科）
        absence_reason: '', // 原因・理由（欠席・遅刻他）
        injury_location: '', // 怪我の部位（外科）
        injury_place: '', // 発生場所（外科）
        surgical_treatment: '', // 処置（外科）
        selectedClass: '',
        selected_student_number: ''
      });
    };

    const onCategoryChange = (index) => {
      // 分類が変更されたらすべての関連フィールドをリセット
      nursingVisits.value[index].type_detail = '';
      nursingVisits.value[index].absence_reason = '';
      nursingVisits.value[index].breakfast = '';
      nursingVisits.value[index].bowel_movement = '';
      nursingVisits.value[index].treatment = '';
      nursingVisits.value[index].injury_location = '';
      nursingVisits.value[index].injury_place = '';
      nursingVisits.value[index].surgical_treatment = '';
    };
    
    const removeNursingVisit = (index) => {
      nursingVisits.value.splice(index, 1);
    };
    
    const onStudentSelect = (index) => {
      // Student info is automatically updated via computed properties
    };
    
    const onStudentNumberSelect = (index) => {
      const visit = nursingVisits.value[index];
      if (!visit.selectedClass || !visit.selected_student_number) {
        visit.student_id = '';
        return;
      }
      
      // 数値型に変換（v-model.numberを使用しているが念のため）
      const studentNumber = parseInt(visit.selected_student_number);
      if (isNaN(studentNumber)) {
        visit.student_id = '';
        return;
      }
      
      // クラスと出席番号から学生を検索
      const student = nursingStudents.value.find(s => 
        String(s.class_id) === String(visit.selectedClass) && 
        s.student_number === studentNumber
      );
      
      if (student) {
        visit.student_id = student.student_id;
      } else {
        visit.student_id = '';
      }
    };
    
    const getStudentById = (studentId) => {
      return nursingStudents.value.find(s => s.student_id === studentId);
    };
    
    const getGenderLabel = (gender) => {
      console.log('Gender value:', gender, 'Type:', typeof gender);
      if (gender === 'male' || gender === 'M' || gender === 'm' || gender === '男') {
        return '男';
      } else if (gender === 'female' || gender === 'F' || gender === 'f' || gender === '女') {
        return '女';
      }
      return gender || '不明';
    };
    
    const getFilteredStudents = (index) => {
      const visit = nursingVisits.value[index];
      let filtered = nursingStudents.value;
      
      console.log('=== getFilteredStudents DEBUG ===');
      console.log('Total students:', filtered.length);
      console.log('Selected class:', visit.selectedClass, 'Type:', typeof visit.selectedClass);
      
      if (filtered.length > 0) {
        console.log('First student sample:', {
          name: filtered[0].name,
          class_id: filtered[0].class_id,
          class_id_type: typeof filtered[0].class_id,
          student_number: filtered[0].student_number
        });
      }
      
      // クラスでフィルター（型を文字列に統一して比較）
      if (visit.selectedClass) {
        console.log('Filtering by class...');
        const selectedClass = String(visit.selectedClass);
        filtered = filtered.filter(s => {
          const studentClass = String(s.class_id || '');
          const matches = studentClass === selectedClass;
          if (!matches && filtered.length < 10) {
            console.log(`No match: student.class_id="${studentClass}" != selected="${selectedClass}"`);
          }
          return matches;
        });
        console.log('After filter:', filtered.length, 'students');
      }
      
      // 出席番号順にソート
      filtered.sort((a, b) => {
        const numA = parseInt(a.student_number) || 0;
        const numB = parseInt(b.student_number) || 0;
        return numA - numB;
      });
      
      console.log('=================================');
      return filtered;
    };
    
    const getAvailableStudentNumbers = (index) => {
      const visit = nursingVisits.value[index];
      let filtered = nursingStudents.value;
      
      // クラスでフィルター（型を文字列に統一して比較）
      if (visit.selectedClass) {
        const selectedClass = String(visit.selectedClass);
        filtered = filtered.filter(s => String(s.class_id || '') === selectedClass);
      }
      
      // 出席番号を抽出してソート
      const numbers = [...new Set(filtered.map(s => s.student_number).filter(n => n))];
      return numbers.sort((a, b) => a - b);
    };
    
    const onClassChange = async (index) => {
      const visit = nursingVisits.value[index];
      // クラスが変更されたら学生の選択をリセット
      visit.student_id = '';
      visit.selected_student_number = '';
      
      // 選択されたクラスの学生をAPIから読み込む
      if (visit.selectedClass) {
        try {
          await studentStore.fetchStudents({ 
            class_id: visit.selectedClass,
            per_page: 1000 // 全学生を取得
          });
          nursingStudents.value = studentStore.students;
          console.log(`Loaded ${nursingStudents.value.length} students for class ${visit.selectedClass}`);
        } catch (error) {
          console.error('Failed to load students for class:', error);
        }
      }
    };
    
    const clearStudent = (index) => {
      const visit = nursingVisits.value[index];
      visit.student_id = '';
      visit.selectedClass = '';
    };
    
    const saveNursingVisits = async () => {
      if (!nursingForm.value.date) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '日付を選択してください'
        });
        return;
      }
      
      if (nursingVisits.value.length === 0) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '来室記録を追加してください'
        });
        return;
      }
      
      // Validate all visits have required fields
      for (let i = 0; i < nursingVisits.value.length; i++) {
        const visit = nursingVisits.value[i];
        if (!visit.student_id || !visit.time || !visit.category) {
          notificationStore.addNotification({
            type: 'danger',
            title: 'エラー',
            message: `${i + 1}行目：学生、時間、分類は必須です`
          });
          return;
        }
      }
      
      savingNursing.value = true;
      try {
        // Prepare visits for API
        const visits = nursingVisits.value.map(visit => ({
          date: nursingForm.value.date,
          time: visit.time,
          student_id: visit.student_id,
          category: visit.category,
          type: visit.type,
          type_detail: visit.type_detail || null,
          occurrence_time: visit.occurrence_time || null,
          treatment_notes: visit.treatment_notes || null
        }));
        
        console.log('=== Saving Nursing Visits ===');
        console.log('Visits data:', JSON.stringify(visits, null, 2));
        
        // Call API to save nursing visits
        const response = await fetch('/api/v1/nursing-visits/bulk', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            visits: visits
          })
        });
        
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || '保存に失敗しました');
        }
        
        const result = await response.json();
        console.log('Nursing visits saved:', result);
        
        notificationStore.addNotification({
          type: 'success',
          title: '保存完了',
          message: result.message || '来室記録を保存しました'
        });
        
        // Redirect to nursing list page
        router.push({ name: 'attendance-registration.nursing.index' });
      } catch (error) {
        console.error('Failed to save nursing visits:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: error.message || '保存に失敗しました'
        });
      } finally {
        savingNursing.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      try {
        await classStore.fetchClasses();
        // 保健室来室記録用に全学生を読み込む
        await studentStore.fetchStudents({ per_page: 1000 });
        nursingStudents.value = studentStore.students;
      } catch (error) {
        console.error('Failed to load data:', error);
      }
    });
    
    return {
      activeTab,
      saving,
      form,
      dateComponents,
      years,
      daysInMonth,
      students,
      attendanceData,
      searchQuery,
      classes,
      pageTitle,
      pageDescription,
      filteredStudents,
      updateDate,
      loadStudents,
      loadStudentsBySearch,
      onStatusChange,
      setAllStatus,
      saveAttendance,
      cancel,
      switchToNursingTab,
      // Nursing
      savingNursing,
      nursingForm,
      nursingDateComponents,
      nursingDaysInMonth,
      nursingVisits,
      nursingStudents,
      nursingSearchQuery,
      filteredNursingStudents,
      updateNursingDate,
      loadStudentsForNursing,
      addNursingVisit,
      removeNursingVisit,
      onCategoryChange,
      onStudentSelect,
      onStudentNumberSelect,
      getStudentById,
      saveNursingVisits,
      getFilteredStudents,
      getGenderLabel,
      onClassChange,
      clearStudent
    };
  }
};
</script>
