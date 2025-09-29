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
                  学生管理
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <router-link 
                    :to="`/students/${student?.id}`" 
                    class="ml-4 text-gray-400 hover:text-gray-500"
                  >
                    {{ student?.name }}
                  </router-link>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">編集</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            学生情報の編集
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ student?.name }}さんの情報を編集します
          </p>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <BaseSpinner size="lg" text="読み込み中..." />
    </div>

    <!-- Content -->
    <div v-else-if="student" class="max-w-3xl">
      <form @submit.prevent="handleSubmit">
        <div class="space-y-8">
          <!-- Basic Information -->
          <BaseCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                <BaseButton
                  type="button"
                  variant="danger"
                  size="sm"
                  @click="confirmDelete"
                  v-if="canDelete"
                >
                  <TrashIcon class="h-4 w-4 mr-2" />
                  削除
                </BaseButton>
              </div>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Student Number -->
                <div>
                  <BaseInput
                    v-model="form.student_number"
                    label="学生番号"
                    required
                    :error="errors.student_number"
                  />
                </div>

                <!-- Name -->
                <div>
                  <BaseInput
                    v-model="form.name"
                    label="氏名"
                    required
                    :error="errors.name"
                  />
                </div>

                <!-- Name Kana -->
                <div>
                  <BaseInput
                    v-model="form.name_kana"
                    label="氏名（ふりがな）"
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

                <!-- Status -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="form.status"
                    label="状態"
                    required
                    :error="errors.status"
                  >
                    <option value="active">在学中</option>
                    <option value="inactive">休学中</option>
                    <option value="graduated">卒業</option>
                  </BaseInput>
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
          <div class="flex justify-between pt-6 border-t border-gray-200">
            <div>
              <BaseButton
                type="button"
                variant="secondary"
                @click="resetForm"
                :disabled="isSubmitting"
              >
                リセット
              </BaseButton>
            </div>
            <div class="flex space-x-3">
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
                変更を保存
              </BaseButton>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="学生の削除"
      variant="danger"
    >
      <div class="space-y-4">
        <div class="flex items-center">
          <ExclamationTriangleIcon class="h-6 w-6 text-red-600 mr-3" />
          <div>
            <p class="text-sm text-gray-900">
              <strong>{{ student?.name }}</strong>さんを削除しようとしています。
            </p>
            <p class="text-sm text-gray-500 mt-1">
              この操作は元に戻すことができません。関連する健康記録もすべて削除されます。
            </p>
          </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="text-sm text-red-800">
            <p class="font-medium">削除される内容：</p>
            <ul class="mt-2 list-disc list-inside space-y-1">
              <li>学生の基本情報</li>
              <li>健康記録（{{ student?.health_records_count || 0 }}件）</li>
              <li>その他関連データ</li>
            </ul>
          </div>
        </div>

        <div>
          <BaseInput
            v-model="deleteConfirmText"
            label="確認のため「削除」と入力してください"
            placeholder="削除"
            :error="deleteConfirmError"
          />
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="showDeleteModal = false"
            :disabled="isDeleting"
          >
            キャンセル
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="handleDelete"
            :loading="isDeleting"
            :disabled="deleteConfirmText !== '削除'"
          >
            削除実行
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStudentStore } from '@/stores/student.js';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseSelect,
  BaseButton,
  BaseSpinner,
  BaseModal
} from '@/components/index.js';

// Icons
const ChevronRightIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
  `
};

const TrashIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

export default {
  name: 'StudentEdit',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseSelect,
    BaseButton,
    BaseSpinner,
    BaseModal,
    ChevronRightIcon,
    TrashIcon,
    ExclamationTriangleIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const studentStore = useStudentStore();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const isSubmitting = ref(false);
    const isDeleting = ref(false);
    const showDeleteModal = ref(false);
    const deleteConfirmText = ref('');
    const deleteConfirmError = ref('');
    const originalForm = ref({});
    
    // Form data
    const form = reactive({
      student_number: '',
      name: '',
      name_kana: '',
      gender: '',
      birth_date: '',
      class_id: '',
      grade_id: '',
      status: 'active',
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
    const student = computed(() => studentStore.currentStudent);
    const classes = computed(() => classStore.classes);
    const canDelete = computed(() => {
      // Only allow deletion if student has no health records or is not active
      return student.value && (
        !student.value.health_records_count || 
        student.value.status !== 'active'
      );
    });
    
    // Methods
    const loadFormData = () => {
      if (!student.value) return;
      
      Object.keys(form).forEach(key => {
        form[key] = student.value[key] || '';
      });
      
      // Convert class_id to string for select
      if (form.class_id) {
        form.class_id = form.class_id.toString();
      }
      
      // Store original form data for reset
      originalForm.value = { ...form };
    };
    
    const resetForm = () => {
      Object.keys(form).forEach(key => {
        form[key] = originalForm.value[key] || '';
      });
      errors.value = {};
    };
    
    const validateForm = () => {
      errors.value = {};
      
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
      
      if (!form.birth_date) {
        errors.value.birth_date = '生年月日は必須です';
      }
      
      if (!form.status) {
        errors.value.status = '状態を選択してください';
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
        const studentData = { ...form };
        
        // Convert empty strings to null for optional fields
        Object.keys(studentData).forEach(key => {
          if (studentData[key] === '') {
            studentData[key] = null;
          }
        });
        
        // Convert class_id to integer
        if (studentData.class_id) {
          studentData.class_id = parseInt(studentData.class_id);
        }
        
        await studentStore.updateStudent(student.value.id, studentData);
        
        notificationStore.addNotification({
          type: 'success',
          title: '更新完了',
          message: `${form.name}さんの情報を更新しました`
        });
        
        // Update original form data
        originalForm.value = { ...form };
        
        // Redirect to student detail page
        router.push(`/students/${student.value.id}`);
      } catch (error) {
        // Handle validation errors from server
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '更新エラー',
          message: error.response?.data?.message || '学生情報の更新に失敗しました'
        });
      } finally {
        isSubmitting.value = false;
      }
    };
    
    const confirmDelete = () => {
      showDeleteModal.value = true;
      deleteConfirmText.value = '';
      deleteConfirmError.value = '';
    };
    
    const handleDelete = async () => {
      if (deleteConfirmText.value !== '削除') {
        deleteConfirmError.value = '「削除」と正確に入力してください';
        return;
      }
      
      isDeleting.value = true;
      
      try {
        await studentStore.deleteStudent(student.value.id);
        
        notificationStore.addNotification({
          type: 'success',
          title: '削除完了',
          message: `${student.value.name}さんを削除しました`
        });
        
        router.push('/students');
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: '削除エラー',
          message: '学生の削除に失敗しました'
        });
      } finally {
        isDeleting.value = false;
        showDeleteModal.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      const studentId = route.params.id;
      
      try {
        await Promise.all([
          studentStore.fetchStudent(studentId),
          classStore.fetchClasses()
        ]);
        
        loadFormData();
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: '学生情報の取得に失敗しました'
        });
        router.push('/students');
      } finally {
        isLoading.value = false;
      }
    });
    
    return {
      isLoading,
      isSubmitting,
      isDeleting,
      showDeleteModal,
      deleteConfirmText,
      deleteConfirmError,
      form,
      errors,
      student,
      classes,
      canDelete,
      resetForm,
      handleSubmit,
      confirmDelete,
      handleDelete
    };
  }
};
</script>