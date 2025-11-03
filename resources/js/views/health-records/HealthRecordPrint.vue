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
          <!-- クラス選択 -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <BaseInput
              v-model="selectedClassId"
              type="select"
              label="クラス選択"
              @change="onClassChange"
            >
              <option value="">すべてのクラス</option>
              <option
                v-for="schoolClass in classList"
                :key="schoolClass.class_id"
                :value="schoolClass.class_id"
              >
                {{ schoolClass.name }}
              </option>
            </BaseInput>

            <BaseInput
              v-model="selectedStudentNumber"
              type="select"
              label="出席番号"
              :disabled="!selectedClassId"
              @change="onStudentNumberChange"
            >
              <option value="">選択してください</option>
              <option
                v-for="student in classStudents"
                :key="student.id"
                :value="student.student_number"
              >
                {{ student.student_number }}番 - {{ student.name }}
              </option>
            </BaseInput>
          </div>

          <!-- 名前検索 -->
          <BaseInput
            v-model="studentSearch"
            label="または学生名・学生番号で検索"
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
          <div class="flex justify-end mb-4">
            <BaseButton
              variant="primary"
              @click="downloadPDF"
            >
              <DocumentArrowDownIcon class="h-5 w-5 mr-2" />
              PDF保存
            </BaseButton>
          </div>

          <!-- Print Preview -->
          <BaseCard>
            <div id="printArea">
              <component
                ref="printFormComponent"
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
import { useClassStore } from '@/stores/class';
import OtolaryngologyPrintForm from './print-forms/OtolaryngologyPrintForm.vue';
import VisionTestPrintForm from '@/components/print-forms/VisionTestPrintForm.vue';

// Icons
const ChevronRightIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>'
};

const MagnifyingGlassIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>'
};

const DocumentArrowDownIcon = {
  template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>'
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
    DocumentArrowDownIcon,
    OtolaryngologyPrintForm,
    VisionTestPrintForm
  },
  setup() {
    const router = useRouter();
    const healthRecordStore = useHealthRecordStore();
    const notificationStore = useNotificationStore();
    const classStore = useClassStore();

    const studentSearch = ref('');
    const searchResults = ref([]);
    const selectedStudent = ref(null);
    const selectedExam = ref('');
    const studentHealthRecords = ref([]);
    const latestHealthRecord = ref(null);
    const printFormComponent = ref(null);
    const selectedClassId = ref('');
    const selectedStudentNumber = ref('');
    const classStudents = ref([]);

    const classList = computed(() => classStore.classes || []);

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

    const onClassChange = async () => {
      selectedStudentNumber.value = '';
      classStudents.value = [];
      
      if (!selectedClassId.value) {
        return;
      }

      try {
        const response = await fetch(`/api/v1/students?class_id=${selectedClassId.value}&per_page=100`);
        const data = await response.json();
        
        if (data.success && data.data) {
          if (data.data.data) {
            classStudents.value = data.data.data.sort((a, b) => a.student_number - b.student_number);
          } else if (Array.isArray(data.data)) {
            classStudents.value = data.data.sort((a, b) => a.student_number - b.student_number);
          }
        }
      } catch (error) {
        console.error('Error fetching class students:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: 'クラスの学生情報の取得に失敗しました'
        });
      }
    };

    const onStudentNumberChange = () => {
      if (!selectedStudentNumber.value) {
        return;
      }

      const student = classStudents.value.find(s => s.student_number === parseInt(selectedStudentNumber.value));
      if (student) {
        selectStudent(student);
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
          console.log('Student health records length:', studentHealthRecords.value.length);
          console.log('Student health records array:', JSON.stringify(studentHealthRecords.value, null, 2));
          
          // 最新の健康記録を設定（年度が最も新しいもの）
          if (studentHealthRecords.value.length > 0) {
            const sorted = [...studentHealthRecords.value].sort((a, b) => b.year - a.year);
            latestHealthRecord.value = sorted[0];
            console.log('Latest health record set to:', latestHealthRecord.value);
          } else {
            latestHealthRecord.value = null;
            console.log('No health records, latestHealthRecord set to null');
          }
          
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
      latestHealthRecord.value = null;
      searchResults.value = [];
      studentSearch.value = '';
      selectedClassId.value = '';
      selectedStudentNumber.value = '';
      classStudents.value = [];
    };

    const getClassGradeName = (student) => {
      if (student.school_class) {
        return `${student.school_class.name} (${student.school_class.grade_id}年)`;
      }
      return '未設定';
    };

    const downloadPDF = async () => {
      if (!selectedStudent.value) {
        notificationStore.addNotification({
          type: 'warning',
          title: '生徒未選択',
          message: '生徒を選択してください'
        });
        return;
      }

      try {
        notificationStore.addNotification({
          type: 'info',
          title: 'PDF生成中',
          message: 'PDFを生成しています...'
        });

        // 印刷フォームコンポーネントからformDataを取得
        let formData = {};
        
        if (printFormComponent.value && typeof printFormComponent.value.getFormData === 'function') {
          formData = printFormComponent.value.getFormData();
          console.log('Form data from component:', formData);
        } else {
          console.warn('printFormComponent.getFormData not available');
        }

        // APIエンドポイントにPOSTリクエスト
        const apiUrl = '/api/v1/health-records/generate-pdf';
        const payload = {
          exam_type: selectedExam.value,
          student: {
            id: selectedStudent.value.id,
            name: selectedStudent.value.name,
            student_id: selectedStudent.value.student_id,
            student_number: selectedStudent.value.student_number,
            school_class: selectedStudent.value.school_class || {}
          },
          health_record: latestHealthRecord.value || {},
          form_data: formData
        };

        console.log('Requesting PDF from server:', apiUrl, payload);

        const response = await axios.post(apiUrl, payload, {
          responseType: 'blob',
          headers: {
            'Accept': 'application/pdf',
            'Content-Type': 'application/json'
          }
        });

        // レスポンスがPDFかどうかを確認
        const blob = response.data;
        if (blob.type !== 'application/pdf') {
          // エラーレスポンスの場合
          const text = await blob.text();
          const errorData = JSON.parse(text);
          throw new Error(errorData.message || 'PDF生成に失敗しました');
        }

        // ファイル名を生成
        const examTypeName = examTypes.find(e => e.value === selectedExam.value)?.label || '検診';
        const fileName = `${selectedStudent.value.name}_${examTypeName}_${new Date().toISOString().split('T')[0]}.pdf`;

        // Blobからダウンロード用URLを作成
        const blobUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();

        // クリーンアップ
        setTimeout(() => {
          window.URL.revokeObjectURL(blobUrl);
          document.body.removeChild(a);
        }, 100);

        console.log('PDF downloaded successfully:', fileName);

        notificationStore.addNotification({
          type: 'success',
          title: 'PDF保存完了',
          message: 'PDFファイルをダウンロードしました'
        });
      } catch (error) {
        console.error('PDF generation error:', error);
        console.error('Error details:', error.response?.data);
        
        let errorMessage = 'PDFの生成に失敗しました';
        if (error.response?.data) {
          try {
            const text = await error.response.data.text();
            const errorData = JSON.parse(text);
            errorMessage = errorData.message || errorMessage;
          } catch (e) {
            // JSONパースエラーは無視
          }
        } else if (error.message) {
          errorMessage = error.message;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: 'PDF生成エラー',
          message: errorMessage
        });
      }
    };

    onMounted(() => {
      // クラスリストをロード
      console.log('HealthRecordPrint mounted');
      classStore.fetchClasses();
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
      printFormComponent,
      classList,
      selectedClassId,
      selectedStudentNumber,
      classStudents,
      searchStudents,
      selectStudent,
      clearSelection,
      getClassGradeName,
      onClassChange,
      onStudentNumberChange,
      downloadPDF
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
