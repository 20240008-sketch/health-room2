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
      <!-- Date and Class Selection -->
      <BaseCard>
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
        </template>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <BaseInput
            type="date"
            v-model="form.date"
            label="日付"
            required
          />
          
          <BaseInput
            type="select"
            v-model="form.grade"
            label="学年"
            required
            @change="onGradeChange"
          >
            <option value="">学年を選択</option>
            <option value="1">1年生</option>
            <option value="2">2年生</option>
            <option value="3">3年生</option>
            <option value="4">4年生</option>
            <option value="5">5年生</option>
            <option value="6">6年生</option>
          </BaseInput>
          
          <BaseInput
            type="select"
            v-model="form.class_id"
            label="クラス"
            required
            @change="loadStudents"
          >
            <option value="">クラスを選択</option>
            <option
              v-for="cls in filteredClasses"
              :key="cls.id"
              :value="cls.class_id"
            >
              {{ cls.name }}
            </option>
          </BaseInput>
        </div>
      </BaseCard>

      <!-- Student Attendance Input -->
      <BaseCard v-if="students.length > 0">
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">出席状況入力</h2>
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
              <tr v-for="student in students" :key="student.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ student.student_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ student.last_name }} {{ student.first_name }}
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
    
    // State
    const saving = ref(false);
    const form = ref({
      date: new Date().toISOString().split('T')[0],
      grade: '',
      class_id: ''
    });
    
    const students = ref([]);
    const attendanceData = ref({});
    
    // Computed
    const classes = computed(() => classStore.classes);
    
    const filteredClasses = computed(() => {
      if (!form.value.grade) return classes.value;
      return classes.value.filter(c => c.grade.toString() === form.value.grade);
    });
    
    // Methods
    const onGradeChange = () => {
      form.value.class_id = '';
      students.value = [];
      attendanceData.value = {};
    };
    
    const loadStudents = async () => {
      if (!form.value.class_id) {
        students.value = [];
        attendanceData.value = {};
        return;
      }
      
      try {
        await studentStore.fetchStudents({ class_id: form.value.class_id });
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
    
    // Lifecycle
    onMounted(async () => {
      try {
        await classStore.fetchClasses();
      } catch (error) {
        console.error('Failed to load classes:', error);
      }
    });
    
    return {
      saving,
      form,
      students,
      attendanceData,
      classes,
      filteredClasses,
      onGradeChange,
      loadStudents,
      onStatusChange,
      setAllStatus,
      saveAttendance,
      cancel
    };
  }
};
</script>
