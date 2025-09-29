<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <nav class="flex" aria-label="パンくず">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <router-link to="/classes" class="text-gray-400 hover:text-gray-500">
                  クラス管理
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">新規作成</span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            新しいクラス
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            新しいクラスの情報を入力してください
          </p>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="max-w-3xl">
      <form @submit.prevent="handleSubmit">
        <div class="space-y-8">
          <!-- Basic Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Class Name -->
                <div class="sm:col-span-2">
                  <BaseInput
                    v-model="form.name"
                    label="クラス名"
                    required
                    placeholder="例: 3年A組"
                    :error="errors.name"
                    help-text="学年とクラスが分かりやすい名前を入力してください"
                  />
                </div>

                <!-- Grade -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="form.grade"
                    label="学年"
                    required
                    :error="errors.grade"
                    @change="updateClassNumber"
                  >
                    <option value="">選択してください</option>
                    <option value="1">1年</option>
                    <option value="2">2年</option>
                    <option value="3">3年</option>
                  </BaseInput>
                </div>

                <!-- Class Number -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="form.class_number"
                    label="組"
                    required
                    :error="errors.class_number"
                    :disabled="!form.grade"
                    @change="updateClassName"
                  >
                    <option value="">選択してください</option>
                    <option
                      v-for="number in availableClassNumbers"
                      :key="number.value"
                      :value="number.value"
                      :disabled="number.disabled"
                    >
                      {{ number.label }}
                    </option>
                  </BaseInput>
                  <p v-if="form.grade && !form.class_number" class="mt-1 text-sm text-gray-500">
                    利用可能な組番号から選択してください
                  </p>
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

                <!-- Capacity -->
                <div>
                  <BaseInput
                    type="number"
                    v-model="form.capacity"
                    label="定員"
                    placeholder="30"
                    min="1"
                    max="50"
                    :error="errors.capacity"
                    help-text="クラスの最大収容人数を設定してください"
                  />
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Teacher Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">担任教師情報</h2>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Homeroom Teacher -->
                <div class="sm:col-span-2">
                  <BaseInput
                    v-model="form.homeroom_teacher"
                    label="担任教師"
                    placeholder="例: 田中 太郎"
                    :error="errors.homeroom_teacher"
                  />
                </div>

                <!-- Assistant Teacher -->
                <div class="sm:col-span-2">
                  <BaseInput
                    v-model="form.assistant_teacher"
                    label="副担任教師"
                    placeholder="例: 佐藤 花子"
                    :error="errors.assistant_teacher"
                  />
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Classroom Information -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">教室情報</h2>
            </template>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Room Number -->
                <div>
                  <BaseInput
                    v-model="form.room_number"
                    label="教室番号"
                    placeholder="例: 301"
                    :error="errors.room_number"
                  />
                </div>

                <!-- Building -->
                <div>
                  <BaseInput
                    v-model="form.building"
                    label="校舎名"
                    placeholder="例: 本館"
                    :error="errors.building"
                  />
                </div>

                <!-- Floor -->
                <div>
                  <BaseInput
                    type="select"
                    v-model="form.floor"
                    label="階数"
                    :error="errors.floor"
                  >
                    <option value="">選択してください</option>
                    <option value="1">1階</option>
                    <option value="2">2階</option>
                    <option value="3">3階</option>
                    <option value="4">4階</option>
                    <option value="5">5階</option>
                  </BaseInput>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Additional Notes -->
          <BaseCard>
            <template #header>
              <h2 class="text-lg font-medium text-gray-900">その他</h2>
            </template>

            <div class="space-y-6">
              <!-- Description -->
              <div>
                <BaseInput
                  type="textarea"
                  v-model="form.description"
                  label="説明・備考"
                  placeholder="クラスの特色や特記事項があれば記入してください"
                  rows="4"
                  :error="errors.description"
                />
              </div>

              <!-- Active Status -->
              <div class="flex items-center">
                <input
                  id="is_active"
                  name="is_active"
                  type="checkbox"
                  v-model="form.is_active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  アクティブなクラスとして設定
                </label>
              </div>
              <p class="text-sm text-gray-500">
                アクティブなクラスは学生登録時に選択肢として表示されます
              </p>
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
              クラスを作成
            </BaseButton>
          </div>
        </div>
      </form>
    </div>

    <!-- Preview Card -->
    <template #rightSidebar>
      <div class="space-y-6">
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">プレビュー</h3>
          </template>
          
          <div class="space-y-4">
            <div v-if="form.name" class="text-center">
              <AcademicCapIcon class="h-12 w-12 text-blue-600 mx-auto" />
              <h4 class="mt-2 text-xl font-bold text-gray-900">
                {{ form.name }}
              </h4>
              <p v-if="form.academic_year" class="text-sm text-gray-500">
                {{ form.academic_year }}年度
              </p>
            </div>

            <div v-else class="text-center py-8">
              <AcademicCapIcon class="h-12 w-12 text-gray-400 mx-auto" />
              <p class="mt-2 text-sm text-gray-500">
                クラス名を入力すると<br>プレビューが表示されます
              </p>
            </div>

            <div v-if="form.name" class="border-t pt-4 space-y-3">
              <div v-if="form.homeroom_teacher" class="flex justify-between">
                <span class="text-sm text-gray-500">担任教師</span>
                <span class="text-sm font-medium text-gray-900">{{ form.homeroom_teacher }}</span>
              </div>

              <div v-if="form.capacity" class="flex justify-between">
                <span class="text-sm text-gray-500">定員</span>
                <span class="text-sm font-medium text-gray-900">{{ form.capacity }}名</span>
              </div>

              <div v-if="form.room_number" class="flex justify-between">
                <span class="text-sm text-gray-500">教室</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ form.building ? `${form.building} ` : '' }}{{ form.room_number }}
                  {{ form.floor ? ` (${form.floor}階)` : '' }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-gray-500">状態</span>
                <BaseBadge :variant="form.is_active ? 'success' : 'secondary'">
                  {{ form.is_active ? 'アクティブ' : '非アクティブ' }}
                </BaseBadge>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Tips -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">
              <LightBulbIcon class="h-5 w-5 inline mr-2 text-yellow-500" />
              ヒント
            </h3>
          </template>
          
          <div class="space-y-3 text-sm text-gray-600">
            <div class="flex items-start">
              <CheckIcon class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
              <span>クラス名は学年と組を含めると分かりやすくなります</span>
            </div>
            <div class="flex items-start">
              <CheckIcon class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
              <span>定員は一般的に30名程度が適切です</span>
            </div>
            <div class="flex items-start">
              <CheckIcon class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
              <span>担任教師は後から変更することも可能です</span>
            </div>
            <div class="flex items-start">
              <CheckIcon class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
              <span>アクティブなクラスのみ新規学生登録時に表示されます</span>
            </div>
          </div>
        </BaseCard>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
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

const AcademicCapIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 14 6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
    </svg>
  `
};

const LightBulbIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
    </svg>
  `
};

const CheckIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
  `
};

export default {
  name: 'ClassCreate',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseBadge,
    ChevronRightIcon,
    AcademicCapIcon,
    LightBulbIcon,
    CheckIcon
  },
  
  setup() {
    const router = useRouter();
    const classStore = useClassStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isSubmitting = ref(false);
    
    // Form data
    const form = reactive({
      name: '',
      grade: '',
      class_number: '',
      academic_year: '',
      capacity: 30,
      homeroom_teacher: '',
      assistant_teacher: '',
      room_number: '',
      building: '',
      floor: '',
      description: '',
      is_active: true
    });
    
    // Errors
    const errors = ref({});
    
    // Computed
    const currentYear = new Date().getFullYear();
    const academicYearOptions = computed(() => {
      return [currentYear - 1, currentYear, currentYear + 1];
    });
    
    const existingClasses = computed(() => classStore.classes);
    
    const availableClassNumbers = computed(() => {
      if (!form.grade || !form.academic_year) return [];
      
      const maxClasses = 6; // Maximum classes per grade
      const existingNumbers = existingClasses.value
        .filter(c => 
          c.grade.toString() === form.grade && 
          c.academic_year.toString() === form.academic_year
        )
        .map(c => c.class_number);
      
      const classLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
      
      return Array.from({ length: maxClasses }, (_, i) => ({
        value: i + 1,
        label: `${i + 1}組 (${classLabels[i]})`,
        disabled: existingNumbers.includes(i + 1)
      }));
    });
    
    // Methods
    const updateClassNumber = () => {
      form.class_number = '';
      updateClassName();
    };
    
    const updateClassName = () => {
      if (form.grade && form.class_number) {
        const classLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
        const classLabel = classLabels[form.class_number - 1] || form.class_number;
        form.name = `${form.grade}年${classLabel}組`;
      }
    };
    
    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'capacity') {
          form[key] = 30;
        } else if (key === 'is_active') {
          form[key] = true;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };
    
    const validateForm = () => {
      errors.value = {};
      
      // Required fields validation
      if (!form.name) {
        errors.value.name = 'クラス名は必須です';
      }
      
      if (!form.grade) {
        errors.value.grade = '学年を選択してください';
      }
      
      if (!form.class_number) {
        errors.value.class_number = '組を選択してください';
      }
      
      if (!form.academic_year) {
        errors.value.academic_year = '年度を選択してください';
      }
      
      // Check for duplicate class
      if (form.grade && form.class_number && form.academic_year) {
        const isDuplicate = existingClasses.value.some(c =>
          c.grade.toString() === form.grade &&
          c.class_number.toString() === form.class_number &&
          c.academic_year.toString() === form.academic_year
        );
        
        if (isDuplicate) {
          errors.value.class_number = 'このクラスは既に存在します';
        }
      }
      
      // Capacity validation
      if (form.capacity && (form.capacity < 1 || form.capacity > 50)) {
        errors.value.capacity = '定員は1名以上50名以下で設定してください';
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
        const classData = { ...form };
        
        // Convert string values to appropriate types
        classData.grade = parseInt(classData.grade);
        classData.class_number = parseInt(classData.class_number);
        classData.academic_year = parseInt(classData.academic_year);
        classData.capacity = parseInt(classData.capacity) || null;
        classData.floor = classData.floor ? parseInt(classData.floor) : null;
        
        // Convert empty strings to null for optional fields
        Object.keys(classData).forEach(key => {
          if (classData[key] === '') {
            classData[key] = null;
          }
        });
        
        const newClass = await classStore.createClass(classData);
        
        notificationStore.addNotification({
          type: 'success',
          title: 'クラス作成完了',
          message: `${form.name}を作成しました`
        });
        
        // Redirect to class detail page
        router.push(`/classes/${newClass.id}`);
      } catch (error) {
        // Handle validation errors from server
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '作成エラー',
          message: error.response?.data?.message || 'クラスの作成に失敗しました'
        });
      } finally {
        isSubmitting.value = false;
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      // Set default academic year to current year
      form.academic_year = currentYear.toString();
      
      // Fetch existing classes to check for duplicates
      try {
        await classStore.fetchClasses();
      } catch (error) {
        // Non-critical error, continue with form
      }
    });
    
    return {
      isSubmitting,
      form,
      errors,
      academicYearOptions,
      availableClassNumbers,
      updateClassNumber,
      updateClassName,
      resetForm,
      handleSubmit
    };
  }
};
</script>