<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            出席入力
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            クラス単位で出席状況を一括入力します
          </p>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-6">
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Date Selection (Scroll Type) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              日付 <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
              <select
                v-model="dateComponents.year"
                @change="updateDate"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
              </select>
              <select
                v-model="dateComponents.month"
                @change="updateDate"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
              </select>
              <select
                v-model="dateComponents.day"
                @change="updateDate"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
                placeholder="名前、番号..."
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
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    v-if="['early_leave'].includes(attendanceData[student.id].status)"
                    type="time"
                    v-model="attendanceData[student.id].departure_time"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4">
                  <input
                    type="text"
                    v-model="attendanceData[student.id].notes"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
            <!-- Date Selection (Scroll Type) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                日付 <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-3 gap-2 max-w-md">
                <select
                  v-model="nursingDateComponents.year"
                  @change="updateNursingDate"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
                </select>
                <select
                  v-model="nursingDateComponents.month"
                  @change="updateNursingDate"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
                </select>
                <select
                  v-model="nursingDateComponents.day"
                  @change="updateNursingDate"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                    時間
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    学生情報（年・組・番・氏名・性）
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                    種別
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                    発生時
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    処置・原因・備考
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                    操作
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="nursingVisits.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                    来室記録がありません。「来室記録追加」ボタンから記録を追加してください。
                  </td>
                </tr>
                <tr v-for="(visit, index) in nursingVisits" :key="index">
                  <td class="px-4 py-4 whitespace-nowrap">
                    <input
                      type="time"
                      v-model="visit.time"
                      class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </td>
                  <td class="px-4 py-4">
                    <div class="space-y-2">
                      <!-- クラス選択 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">クラス</label>
                        <select
                          v-model="visit.selectedClass"
                          @change="onClassChange(index)"
                          class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                          <option value="">すべてのクラス</option>
                          <option
                            v-for="cls in classes"
                            :key="cls.id"
                            :value="cls.class_id"
                          >
                            {{ cls.name }}
                          </option>
                        </select>
                      </div>
                      
                      <!-- 出席番号選択 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">出席番号</label>
                        <input
                          type="number"
                          v-model.number="visit.selectedStudentNumber"
                          @input="onStudentNumberChange(index)"
                          min="1"
                          max="99"
                          class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          placeholder="番号を入力"
                        />
                      </div>
                      
                      <!-- 人名選択 -->
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                          人名
                          <span v-if="visit.selectedClass && !visit.selectedStudentNumber" class="text-gray-500 font-normal ml-1">
                            (出席番号を入力してください)
                          </span>
                        </label>
                        <select
                          v-model="visit.student_id"
                          @change="onStudentSelect(index)"
                          class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          :disabled="!visit.selectedClass"
                        >
                          <option value="">人名を選択</option>
                          <option
                            v-for="student in getFilteredStudents(index)"
                            :key="student.id"
                            :value="student.id"
                          >
                            {{ student.student_number }}番 {{ student.name }} ({{ student.gender === 'male' ? '男' : '女' }})
                          </option>
                        </select>
                        <p v-if="visit.selectedClass && visit.selectedStudentNumber && getFilteredStudents(index).length === 0" class="mt-1 text-xs text-red-600">
                          該当する学生が見つかりません
                        </p>
                        <p v-else-if="visit.selectedClass && !visit.selectedStudentNumber && getFilteredStudents(index).length > 0" class="mt-1 text-xs text-blue-600">
                          {{ getFilteredStudents(index).length }}人の学生がいます
                        </p>
                      </div>
                      
                      <!-- 選択された学生の表示 -->
                      <div v-if="visit.student_id" class="p-2 bg-blue-50 rounded-md">
                        <div class="flex items-center justify-between">
                          <div class="text-xs">
                            <span class="font-medium text-gray-900">{{ getStudentById(visit.student_id)?.name }}</span>
                            <div class="text-gray-600 mt-1">
                              {{ getStudentById(visit.student_id)?.schoolClass?.grade || '-' }}年 
                              {{ getStudentById(visit.student_id)?.schoolClass?.name?.replace(/[0-9]年/, '') || '-' }}組 
                              {{ getStudentById(visit.student_id)?.student_number || '-' }}番 
                              ({{ getStudentById(visit.student_id)?.gender === 'male' ? '男' : '女' }})
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
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <select
                      v-model="visit.type"
                      class="block w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="injury">けが</option>
                      <option value="illness">病気</option>
                      <option value="other">その他</option>
                    </select>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <input
                      type="text"
                      v-model="visit.occurrence_time"
                      class="block w-32 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="休み時間"
                    />
                  </td>
                  <td class="px-4 py-4">
                    <input
                      type="text"
                      v-model="visit.treatment_notes"
                      class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="体温37.2度、冷却、休養後下校"
                    />
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <button
                      @click="removeNursingVisit(index)"
                      class="text-red-600 hover:text-red-900"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
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
    
    // Tab State
    const activeTab = ref('attendance');
    
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
        const records = Object.entries(attendanceData.value).map(([studentId, data]) => ({
          student_id: parseInt(studentId),
          status: data.status,
          arrival_time: data.arrival_time || null,
          departure_time: data.departure_time || null,
          notes: data.notes || null
        }));
        
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
        
        router.push({ name: 'attendance-registration.index' });
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
      router.push({ name: 'attendance-registration.index' });
    };
    
    // Tab switching
    const switchToNursingTab = async () => {
      activeTab.value = 'nursing';
      // 保健室タブに切り替えた時に全学生を再読み込み
      try {
        console.log('Switching to nursing tab, reloading all students...');
        await studentStore.fetchStudents({});
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
        const params = {};
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
        type: 'illness',
        occurrence_time: '',
        treatment_notes: '',
        selectedClass: '',
        selectedStudentNumber: ''
      });
    };
    
    const removeNursingVisit = (index) => {
      nursingVisits.value.splice(index, 1);
    };
    
    const onStudentSelect = (index) => {
      // Student info is automatically updated via computed properties
    };
    
    const getStudentById = (studentId) => {
      return nursingStudents.value.find(s => s.id === studentId);
    };
    
    const getFilteredStudents = (index) => {
      const visit = nursingVisits.value[index];
      let filtered = nursingStudents.value;
      
      console.log('=== Filtering Students ===');
      console.log('Total nursing students:', filtered.length);
      console.log('Selected class:', visit.selectedClass);
      console.log('Selected student number:', visit.selectedStudentNumber);
      
      // サンプル学生データを出力
      if (filtered.length > 0) {
        console.log('Sample student data:', {
          id: filtered[0].id,
          name: filtered[0].name,
          student_number: filtered[0].student_number,
          class_id: filtered[0].class_id,
          class_id_type: typeof filtered[0].class_id,
          schoolClass: filtered[0].schoolClass
        });
      }
      
      // 全学生のclass_idをリスト表示
      console.log('All student class_ids:', filtered.map(s => ({ name: s.name, class_id: s.class_id, type: typeof s.class_id })));
      
      // クラスでフィルター
      if (visit.selectedClass) {
        console.log('Filtering by class...');
        const beforeCount = filtered.length;
        filtered = filtered.filter(s => {
          const match = s.class_id == visit.selectedClass;
          if (match) {
            console.log('Match found:', s.name, 'class_id:', s.class_id);
          }
          return match;
        });
        console.log('After class filter:', filtered.length, '(was', beforeCount, ')');
      }
      
      // 出席番号でフィルター
      if (visit.selectedStudentNumber) {
        console.log('Filtering by student number...');
        const beforeCount = filtered.length;
        filtered = filtered.filter(s => {
          const match = s.student_number == visit.selectedStudentNumber;
          if (match) {
            console.log('Match found:', s.name, 'number:', s.student_number);
          }
          return match;
        });
        console.log('After number filter:', filtered.length, '(was', beforeCount, ')');
      }
      
      console.log('Final result:', filtered.length, 'students');
      console.log('======================');
      
      return filtered;
    };
    
    const getAvailableStudentNumbers = (index) => {
      const visit = nursingVisits.value[index];
      let filtered = nursingStudents.value;
      
      // クラスでフィルター
      if (visit.selectedClass) {
        filtered = filtered.filter(s => s.class_id === visit.selectedClass);
      }
      
      // 出席番号を抽出してソート
      const numbers = [...new Set(filtered.map(s => s.student_number).filter(n => n))];
      return numbers.sort((a, b) => a - b);
    };
    
    const onClassChange = (index) => {
      const visit = nursingVisits.value[index];
      // クラスが変更されたら出席番号と学生の選択をリセット
      visit.selectedStudentNumber = '';
      visit.student_id = '';
    };
    
    const onStudentNumberChange = (index) => {
      const visit = nursingVisits.value[index];
      // 出席番号が変更されたら学生の選択をリセット
      visit.student_id = '';
    };
    
    const clearStudent = (index) => {
      const visit = nursingVisits.value[index];
      visit.student_id = '';
      visit.selectedClass = '';
      visit.selectedStudentNumber = '';
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
        if (!visit.student_id || !visit.time || !visit.type) {
          notificationStore.addNotification({
            type: 'danger',
            title: 'エラー',
            message: `${i + 1}行目：学生、時間、種別は必須です`
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
          type: visit.type,
          occurrence_time: visit.occurrence_time || null,
          treatment_notes: visit.treatment_notes || null
        }));
        
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
        
        // Clear form
        nursingVisits.value = [];
        nursingForm.value.class_id = '';
        nursingSearchQuery.value = '';
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
        await studentStore.fetchStudents({});
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
      onStudentSelect,
      getStudentById,
      saveNursingVisits,
      getFilteredStudents,
      getAvailableStudentNumbers,
      onClassChange,
      onStudentNumberChange,
      clearStudent
    };
  }
};
</script>
