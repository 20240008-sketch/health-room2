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
                  <span class="ml-4 text-sm font-medium text-gray-500">結果印刷</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            検診結果印刷
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            生徒の検診結果を印刷します
          </p>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-7xl">
      <!-- Student Search Section -->
      <BaseCard v-if="!selectedStudent" class="mb-6">
        <template #header>
          <h2 class="text-lg font-medium text-gray-900">生徒検索</h2>
        </template>

        <div class="space-y-4">
          <BaseInput
            v-model="studentSearch"
            label="学生名または学生番号で検索"
            placeholder="検索..."
            @input="searchStudents"
          >
            <template #prepend>
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </template>
          </BaseInput>

          <div v-if="searchResults.length > 0" class="max-h-60 overflow-y-auto border border-gray-300 rounded-md">
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
                    学生番号: {{ student.student_id }} | 
                    {{ getClassGradeName(student) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="studentSearch && searchResults.length === 0" class="text-center py-8 text-gray-500">
            該当する学生が見つかりません
          </div>
        </div>
      </BaseCard>

      <!-- Print Selection Section -->
      <div v-if="selectedStudent">
        <BaseCard class="mb-6">
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">選択中の生徒</h2>
              <BaseButton
                size="sm"
                variant="secondary"
                @click="clearSelection"
              >
                別の生徒を選択
              </BaseButton>
            </div>
          </template>

          <div class="bg-blue-50 p-4 rounded-md">
            <div class="text-lg font-medium text-gray-900">{{ selectedStudent.name }}</div>
            <div class="text-sm text-gray-600 mt-1">
              学生番号: {{ selectedStudent.student_id }} | {{ getClassGradeName(selectedStudent) }}
            </div>
          </div>
        </BaseCard>

        <BaseCard class="mb-6">
          <template #header>
            <h2 class="text-lg font-medium text-gray-900">検診項目の選択</h2>
          </template>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <label
              v-for="exam in examTypes"
              :key="exam.value"
              class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
              :class="selectedExam === exam.value ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
            >
              <input
                type="radio"
                name="exam_type"
                :value="exam.value"
                v-model="selectedExam"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-3 text-sm font-medium text-gray-900">{{ exam.label }}</span>
            </label>
          </div>
        </BaseCard>

        <!-- 検診結果フォーム表示 -->
        <div v-if="selectedExam && selectedStudent" class="mb-6">
          <div class="flex justify-end space-x-3 mb-4">
            <BaseButton
              variant="success"
              @click="printResults"
            >
              <PrinterIcon class="h-5 w-5 mr-2" />
              印刷
            </BaseButton>
          </div>

          <!-- Print Preview -->
          <BaseCard>
            <div id="printArea">
              <component
                :is="currentPrintComponent"
                :student="selectedStudent"
                :health-record="latestHealthRecord"
              />
            </div>
          </BaseCard>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/components/layouts/AppLayout.vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseInput from '@/components/base/BaseInput.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import { useHealthRecordStore } from '@/stores/healthRecord';
import { useNotificationStore } from '@/stores/notification';
import OtolaryngologyPrintForm from './print-forms/OtolaryngologyPrintForm.vue';
import VisionTestPrintForm from '@/components/print-forms/VisionTestPrintForm.vue';

// Icons
const ChevronRightIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>'
};

const MagnifyingGlassIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>'
};

const PrinterIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>'
};

export default {
  name: 'HealthRecordPrint',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    ChevronRightIcon,
    MagnifyingGlassIcon,
    PrinterIcon,
    OtolaryngologyPrintForm,
    VisionTestPrintForm
  },
  setup() {
    const router = useRouter();
    const healthRecordStore = useHealthRecordStore();
    const notificationStore = useNotificationStore();

    const studentSearch = ref('');
    const searchResults = ref([]);
    const selectedStudent = ref(null);
    const selectedExam = ref('');
    const studentHealthRecords = ref([]);

    const examTypes = [
      { value: 'vision_test', label: '視力検査' },
      { value: 'ophthalmology', label: '眼科検診' },
      { value: 'otolaryngology', label: '耳鼻科検診' },
      { value: 'internal_medicine', label: '内科検診' },
      { value: 'hearing_test', label: '聴力検査' },
      { value: 'tuberculosis_test', label: '結核検査' },
      { value: 'urine_test', label: '尿検査' },
      { value: 'ecg', label: '心電図' }
    ];

    // 最新の健康記録を取得（年度が最も新しいもの）
    const latestHealthRecord = computed(() => {
      if (!studentHealthRecords.value.length) return null;
      // 年度でソートして最新のものを返す
      const sorted = [...studentHealthRecords.value].sort((a, b) => b.year - a.year);
      return sorted[0];
    });

    const currentPrintComponent = computed(() => {
      switch (selectedExam.value) {
        case 'vision_test':
          return VisionTestPrintForm;
        case 'otolaryngology':
          return OtolaryngologyPrintForm;
        default:
          return null;
      }
    });

    const searchStudents = async () => {
      if (!studentSearch.value || studentSearch.value.length < 1) {
        searchResults.value = [];
        return;
      }

      try {
        const response = await fetch(`/api/v1/students?search=${encodeURIComponent(studentSearch.value)}&per_page=20`);
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success && data.data) {
          // ページネーションレスポンスの場合
          if (data.data.data) {
            searchResults.value = data.data.data;
          } else if (Array.isArray(data.data)) {
            searchResults.value = data.data;
          } else {
            searchResults.value = [];
          }
          console.log('Search results:', searchResults.value);
        } else {
          searchResults.value = [];
        }
      } catch (error) {
        console.error('Student search error:', error);
        searchResults.value = [];
        notificationStore.addNotification({
          type: 'danger',
          title: '検索エラー',
          message: '学生の検索に失敗しました'
        });
      }
    };

    const selectStudent = async (student) => {
      selectedStudent.value = student;
      searchResults.value = [];
      studentSearch.value = '';

      // Fetch student's health records
      try {
        const response = await fetch(`/api/v1/health-records/print-results/${student.student_id}`);
        const data = await response.json();
        
        console.log('Health records API response:', data);
        
        if (data.success) {
          studentHealthRecords.value = data.data.health_records;
          
          console.log('Student health records:', studentHealthRecords.value);
          console.log('Available years:', availableYears.value);
          
          if (studentHealthRecords.value.length === 0) {
            notificationStore.addNotification({
              type: 'warning',
              title: '健康記録なし',
              message: 'この生徒の健康記録が見つかりません'
            });
          }
        }
      } catch (error) {
        console.error('Error fetching health records:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '健康記録の取得に失敗しました'
        });
      }
    };

    const clearSelection = () => {
      selectedStudent.value = null;
      selectedExam.value = '';
      studentHealthRecords.value = [];
      searchResults.value = [];
      studentSearch.value = '';
    };

    const getClassGradeName = (student) => {
      if (student.school_class) {
        return `${student.school_class.name} (${student.school_class.grade_id}年)`;
      }
      return '未設定';
    };

    const printResults = () => {
      if (!latestHealthRecord.value) {
        notificationStore.addNotification({
          type: 'warning',
          title: 'データなし',
          message: 'この生徒の健康記録が見つかりません'
        });
        return;
      }

      // 印刷ダイアログを開く
      window.print();
    };

    onMounted(() => {
      // 初期化処理（必要に応じて）
      console.log('HealthRecordPrint mounted');
    });

    watch(selectedExam, (newExam) => {
      console.log('Selected exam changed:', newExam);
      console.log('Latest health record:', latestHealthRecord.value);
      console.log('Current print component:', currentPrintComponent.value);
    });

    return {
      studentSearch,
      searchResults,
      selectedStudent,
      selectedExam,
      examTypes,
      latestHealthRecord,
      currentPrintComponent,
      searchStudents,
      selectStudent,
      clearSelection,
      getClassGradeName,
      printResults
    };
  }
};
</script>

<style scoped>
/* 画面表示用 */
#printArea {
  background: white;
  padding: 20px;
}

/* 印刷用スタイル */
@media print {
  /* ページ以外の要素を非表示 */
  body * {
    visibility: hidden;
  }
  
  /* 印刷エリアとその子要素のみ表示 */
  #printArea,
  #printArea * {
    visibility: visible;
  }
  
  /* 印刷エリアを画面全体に配置 */
  #printArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 0;
  }
}
</style>
