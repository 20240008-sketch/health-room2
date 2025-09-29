<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <router-link to="/students" class="text-gray-400 hover:text-gray-500">
                  <span class="sr-only">学生管理</span>
                  学生管理
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">学生登録</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            学生登録
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            新しい学生の基本情報を登録します
          </p>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-3xl">
      <form @submit.prevent="handleSubmit" novalidate>
        <div class="space-y-8">
          <!-- Basic Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Student Number -->
                <div>
                  <BaseInput
                    v-model="form.student_number"
                    label="学生番号"
                    placeholder="例: 2025001"
                    required
                    :error="errors.student_number"
                  />
                </div>

                <!-- Name -->
                <div>
                  <BaseInput
                    v-model="form.name"
                    label="氏名"
                    placeholder="例: 山田太郎"
                    required
                    :error="errors.name"
                  />
                </div>

                <!-- Name Kana -->
                <div>
                  <BaseInput
                    v-model="form.name_kana"
                    label="氏名（ふりがな）"
                    placeholder="例: やまだたろう"
                    :error="errors.name_kana"
                  />
                </div>

                <!-- Gender -->
                <div>
                  <BaseSelect
                    v-model="form.gender"
                    label="性別"
                    required
                    :error="errors.gender"
                  >
                    <option value="">選択してください</option>
                    <option value="male">男</option>
                    <option value="female">女</option>
                  </BaseSelect>
                </div>

                <!-- Birth Date -->
                <div>
                  <BaseInput
                    type="date"
                    v-model="form.birth_date"
                    label="生年月日"
                    required
                    :error="errors.birth_date"
                  />
                </div>

                <!-- Class -->
                <div>
                  <BaseSelect
                    v-model="form.class_id"
                    label="所属クラス"
                    required
                    :error="errors.class_id"
                  >
                    <option value="">選択してください</option>
                    <option value="特進">特進</option>
                    <option value="進学">進学</option>
                    <option value="調理">調理</option>
                    <option value="福祉">福祉</option>
                    <option value="情会">情会</option>
                    <option value="総合１">総合１</option>
                    <option value="総合２">総合２</option>
                    <option value="総合３">総合３</option>
                  </BaseSelect>
                </div>

                <!-- Grade -->
                <div>
                  <BaseSelect
                    v-model="form.grade_id"
                    label="学年"
                    required
                    :error="errors.grade_id"
                  >
                    <option value="">選択してください</option>
                    <option value="1">1年生</option>
                    <option value="2">2年生</option>
                    <option value="3">3年生</option>
                  </BaseSelect>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Contact Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">連絡先情報</h2>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Phone -->
                <div>
                  <BaseInput
                    v-model="form.phone"
                    label="電話番号"
                    placeholder="例: 090-1234-5678"
                    :error="errors.phone"
                  />
                </div>

                <!-- Emergency Contact -->
                <div>
                  <BaseInput
                    v-model="form.emergency_contact"
                    label="緊急連絡先"
                    placeholder="例: 090-9876-5432"
                    :error="errors.emergency_contact"
                  />
                </div>
              </div>

              <!-- Address -->
              <div>
                <BaseInput
                  v-model="form.address"
                  label="住所"
                  placeholder="例: 東京都渋谷区..."
                  :error="errors.address"
                />
              </div>
            </div>
          </BaseCard>

          <!-- Medical Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">医療情報</h2>
            </template>

            <div class="space-y-6">
              <!-- Allergies -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.allergies"
                  label="アレルギー情報"
                  placeholder="既知のアレルギーがある場合は記入してください"
                  rows="3"
                  :error="errors.allergies"
                />
              </div>

              <!-- Medical Conditions -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.medical_conditions"
                  label="既往症・持病"
                  placeholder="既往症や現在の医療状況がある場合は記入してください"
                  rows="3"
                  :error="errors.medical_conditions"
                />
              </div>

              <!-- Medications -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.medications"
                  label="服用中の薬"
                  placeholder="常用薬がある場合は記入してください"
                  rows="3"
                  :error="errors.medications"
                />
              </div>
            </div>
          </BaseCard>

          <!-- Additional Notes -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">備考</h2>
            </template>

            <div>
              <BaseInput
                type="textarea"
                v-model="form.notes"
                label="備考"
                placeholder="その他の特記事項があれば記入してください"
                rows="4"
                :error="errors.notes"
              />
            </div>
          </BaseCard>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <BaseButton
              type="button"
              variant="secondary"
              @click="$router.back()"
            >
              キャンセル
            </BaseButton>
            <BaseButton
              type="submit"
              variant="primary"
              :loading="isSubmitting"
            >
              学生を登録
            </BaseButton>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useStudentStore } from '@/stores/student.js';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseSelect,
  BaseButton
} from '@/components/index.js';

// Icons
const ChevronRightIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
  `
};

export default {
  name: 'StudentCreate',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseSelect,
    BaseButton,
    ChevronRightIcon
  },
  
  setup() {
    const router = useRouter();
    const studentStore = useStudentStore();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isSubmitting = ref(false);
    
    // Form data
    const form = reactive({
      student_number: '',
      name: '',
      name_kana: '',
      gender: '',
      birth_date: '',
      class_id: '',
      grade_id: '',
      phone: '',
      emergency_contact: '',
      address: '',
      allergies: '',
      medical_conditions: '',
      medications: '',
      notes: ''
    });
    
    // Errors
    const errors = ref({});
    
    // Computed
    const classes = computed(() => classStore.classes);
    
    // Methods
    const validateForm = () => {
      errors.value = {};
      
      console.log('Validating form.class_id:', form.class_id, 'Type:', typeof form.class_id); // Debug log
      
      // Required fields validation
      if (!form.student_number) {
        errors.value.student_number = '学生番号は必須です';
      }
      
      if (!form.name) {
        errors.value.name = '氏名は必須です';
      }
      
      if (!form.gender) {
        errors.value.gender = '性別を選択してください';
      }
      
      if (!form.class_id || form.class_id === '') {
        errors.value.class_id = 'クラスを選択してください';
      }
      
      if (!form.grade_id || form.grade_id === '') {
        errors.value.grade_id = '学年を選択してください';      
      }
      
      if (!form.birth_date) {
        errors.value.birth_date = '生年月日は必須です';
      }
      
      // Validate birth date is not in future
      if (form.birth_date && new Date(form.birth_date) > new Date()) {
        errors.value.birth_date = '生年月日は今日以前の日付を入力してください';
      }
      
      // Phone number format validation
      if (form.phone && !/^[\d-]+$/.test(form.phone)) {
        errors.value.phone = '電話番号の形式が正しくありません';
      }
      
      if (form.emergency_contact && !/^[\d-]+$/.test(form.emergency_contact)) {
        errors.value.emergency_contact = '緊急連絡先の形式が正しくありません';
      }
      
      return Object.keys(errors.value).length === 0;
    };
    
    const handleSubmit = async () => {
      console.log('Form data before validation:', form); // Debug log
      
      if (!validateForm()) {
        console.log('Validation errors:', errors.value); // Debug log
        notificationStore.addNotification({
          type: 'warning',
          title: '入力エラー',
          message: '入力内容を確認してください'
        });
        return;
      }
      
      isSubmitting.value = true;
      
      try {
        const studentData = { ...form };
        
        // Convert empty strings to null for optional fields  
        Object.keys(studentData).forEach(key => {
          if (studentData[key] === '' && key !== 'class_id' && key !== 'grade_id') {
            studentData[key] = null;
          }
        });
        
        const student = await studentStore.createStudent(studentData);
        
        notificationStore.addNotification({
          type: 'success',
          title: '登録完了',
          message: `${form.name}さんを登録しました`
        });
        
        // Redirect to student detail page
        router.push(`/students/${student.id}`);
      } catch (error) {
        // Handle validation errors from server
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '登録エラー',
          message: error.response?.data?.message || '学生の登録に失敗しました'
        });
      } finally {
        isSubmitting.value = false;
      }
    };
    
    // Auto-generate student number
    const generateStudentNumber = () => {
      const currentYear = new Date().getFullYear();
      const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
      form.student_number = `${currentYear}${randomNum}`;
    };
    
    // Lifecycle
    onMounted(async () => {
      await classStore.fetchClasses();
      
      // Auto-generate student number if empty
      if (!form.student_number) {
        generateStudentNumber();
      }
    });

    // Watchers
    watch(() => form.class_id, (newValue) => {
      console.log('StudentCreate class_id changed:', newValue);
    });

    watch(() => form.grade_id, (newValue) => {
      console.log('StudentCreate grade_id changed:', newValue);
    });
    
    return {
      form,
      errors,
      classes,
      isSubmitting,
      handleSubmit
    };
  }
};
</script>