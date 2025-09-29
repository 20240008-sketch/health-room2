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
                  <router-link 
                    :to="`/classes/${schoolClass?.id}`" 
                    class="ml-4 text-gray-400 hover:text-gray-500"
                  >
                    {{ schoolClass?.name }}
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
            クラス情報の編集
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            {{ schoolClass?.name }}の情報を編集します
          </p>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <BaseSpinner size="lg" text="読み込み中..." />
    </div>

    <!-- Content -->
    <div v-else-if="schoolClass" class="max-w-3xl">
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
                <!-- Class Name -->
                <div class="sm:col-span-2">
                  <BaseInput
                    v-model="form.name"
                    label="クラス名"
                    required
                    placeholder="例: 3年A組"
                    :error="errors.name"
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
                  />
                </div>

                <!-- Status -->
                <div class="sm:col-span-2">
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
                  <p class="mt-1 text-sm text-gray-500">
                    アクティブなクラスは学生登録時に選択肢として表示されます
                  </p>
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
      title="クラスの削除"
      variant="danger"
    >
      <div class="space-y-4">
        <div class="flex items-center">
          <ExclamationTriangleIcon class="h-6 w-6 text-red-600 mr-3" />
          <div>
            <p class="text-sm text-gray-900">
              <strong>{{ schoolClass?.name }}</strong>を削除しようとしています。
            </p>
            <p class="text-sm text-gray-500 mt-1">
              この操作は元に戻すことができません。
            </p>
          </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="text-sm text-red-800">
            <p class="font-medium">削除される内容：</p>
            <ul class="mt-2 list-disc list-inside space-y-1">
              <li>クラス基本情報</li>
              <li>所属学生との関連（{{ schoolClass?.students_count || 0 }}名）</li>
              <li>その他関連データ</li>
            </ul>
          </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex items-start">
            <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 mr-2 mt-0.5" />
            <div class="text-sm text-yellow-800">
              <p class="font-medium">注意：</p>
              <p>学生は削除されませんが、クラス情報が空になります。事前に学生を他のクラスに移動することをお勧めします。</p>
            </div>
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

    <!-- Right Sidebar -->
    <template #rightSidebar>
      <div class="space-y-6" v-if="schoolClass">
        <!-- Current Status -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">現在の状況</h3>
          </template>
          
          <div class="space-y-4">
            <div class="text-center">
              <AcademicCapIcon class="h-12 w-12 text-blue-600 mx-auto" />
              <h4 class="mt-2 text-xl font-bold text-gray-900">
                {{ schoolClass.name }}
              </h4>
              <p class="text-sm text-gray-500">
                {{ schoolClass.academic_year }}年度
              </p>
            </div>

            <div class="border-t pt-4 space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">学生数</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ schoolClass.students_count || 0 }}名
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-gray-500">定員</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ schoolClass.capacity ? `${schoolClass.capacity}名` : '未設定' }}
                </span>
              </div>

              <div v-if="schoolClass.capacity" class="space-y-1">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">充足率</span>
                  <span class="font-medium text-gray-900">
                    {{ Math.round((schoolClass.students_count / schoolClass.capacity) * 100) }}%
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full"
                    :style="{ width: `${Math.min(100, (schoolClass.students_count / schoolClass.capacity) * 100)}%` }"
                  ></div>
                </div>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-gray-500">状態</span>
                <BaseBadge :variant="schoolClass.is_active ? 'success' : 'secondary'">
                  {{ schoolClass.is_active ? 'アクティブ' : '非アクティブ' }}
                </BaseBadge>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Form Preview -->
        <BaseCard v-if="hasFormChanges">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">変更プレビュー</h3>
          </template>
          
          <div class="space-y-4">
            <div class="text-center">
              <AcademicCapIcon class="h-12 w-12 text-green-600 mx-auto" />
              <h4 class="mt-2 text-xl font-bold text-gray-900">
                {{ form.name || 'クラス名未入力' }}
              </h4>
              <p v-if="form.academic_year" class="text-sm text-gray-500">
                {{ form.academic_year }}年度
              </p>
            </div>

            <div class="border-t pt-4 space-y-3">
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

        <!-- Quick Actions -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">クイックアクション</h3>
          </template>

          <div class="space-y-3">
            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/classes/${schoolClass.id}`)"
            >
              <EyeIcon class="h-4 w-4 mr-2" />
              詳細表示
            </BaseButton>

            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/students?class_id=${schoolClass.id}`)"
            >
              <UsersIcon class="h-4 w-4 mr-2" />
              学生一覧
            </BaseButton>

            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/students/create?class_id=${schoolClass.id}`)"
            >
              <UserPlusIcon class="h-4 w-4 mr-2" />
              学生追加
            </BaseButton>
          </div>
        </BaseCard>
      </div>
    </template>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClassStore } from '@/stores/class.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseBadge,
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

const AcademicCapIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 14 6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
    </svg>
  `
};

const EyeIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
  `
};

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const UserPlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
    </svg>
  `
};

export default {
  name: 'ClassEdit',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseBadge,
    BaseSpinner,
    BaseModal,
    ChevronRightIcon,
    TrashIcon,
    ExclamationTriangleIcon,
    AcademicCapIcon,
    EyeIcon,
    UsersIcon,
    UserPlusIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
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
      name: '',
      grade: '',
      class_number: '',
      academic_year: '',
      capacity: '',
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
    const schoolClass = computed(() => classStore.currentClass);
    const allClasses = computed(() => classStore.classes);
    const currentYear = new Date().getFullYear();
    const academicYearOptions = computed(() => {
      return [currentYear - 1, currentYear, currentYear + 1];
    });
    
    const availableClassNumbers = computed(() => {
      if (!form.grade || !form.academic_year) return [];
      
      const maxClasses = 6; // Maximum classes per grade
      const existingNumbers = allClasses.value
        .filter(c => 
          c.id !== schoolClass.value?.id && // Exclude current class
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
    
    const canDelete = computed(() => {
      // Allow deletion if class has no students or is not active
      return schoolClass.value && (
        !schoolClass.value.students_count || 
        !schoolClass.value.is_active
      );
    });
    
    const hasFormChanges = computed(() => {
      return Object.keys(form).some(key => {
        const originalValue = originalForm.value[key];
        const currentValue = form[key];
        return originalValue !== currentValue;
      });
    });
    
    // Methods
    const loadFormData = () => {
      if (!schoolClass.value) return;
      
      Object.keys(form).forEach(key => {
        let value = schoolClass.value[key];
        
        // Convert numeric values to strings for form inputs
        if (typeof value === 'number') {
          value = value.toString();
        }
        
        form[key] = value || '';
      });
      
      // Store original form data for comparison
      originalForm.value = { ...form };
    };
    
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
        form[key] = originalForm.value[key] || '';
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
      
      // Check for duplicate class (excluding current class)
      if (form.grade && form.class_number && form.academic_year) {
        const isDuplicate = allClasses.value.some(c =>
          c.id !== schoolClass.value?.id &&
          c.grade.toString() === form.grade &&
          c.class_number.toString() === form.class_number &&
          c.academic_year.toString() === form.academic_year
        );
        
        if (isDuplicate) {
          errors.value.class_number = 'このクラスは既に存在します';
        }
      }
      
      // Capacity validation
      if (form.capacity && (parseInt(form.capacity) < 1 || parseInt(form.capacity) > 50)) {
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
        classData.capacity = classData.capacity ? parseInt(classData.capacity) : null;
        classData.floor = classData.floor ? parseInt(classData.floor) : null;
        
        // Convert empty strings to null for optional fields
        Object.keys(classData).forEach(key => {
          if (classData[key] === '') {
            classData[key] = null;
          }
        });
        
        await classStore.updateClass(schoolClass.value.id, classData);
        
        notificationStore.addNotification({
          type: 'success',
          title: '更新完了',
          message: `${form.name}の情報を更新しました`
        });
        
        // Update original form data
        originalForm.value = { ...form };
        
        // Redirect to class detail page
        router.push(`/classes/${schoolClass.value.id}`);
      } catch (error) {
        // Handle validation errors from server
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: '更新エラー',
          message: error.response?.data?.message || 'クラス情報の更新に失敗しました'
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
        await classStore.deleteClass(schoolClass.value.id);
        
        notificationStore.addNotification({
          type: 'success',
          title: '削除完了',
          message: `${schoolClass.value.name}を削除しました`
        });
        
        router.push('/classes');
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: '削除エラー',
          message: 'クラスの削除に失敗しました'
        });
      } finally {
        isDeleting.value = false;
        showDeleteModal.value = false;
      }
    };
    
    // Watchers
    watch(() => form.grade, updateClassNumber);
    watch(() => form.class_number, updateClassName);
    
    // Lifecycle
    onMounted(async () => {
      const classId = route.params.id;
      
      try {
        await Promise.all([
          classStore.fetchClass(classId),
          classStore.fetchClasses()
        ]);
        
        loadFormData();
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'データ取得エラー',
          message: 'クラス情報の取得に失敗しました'
        });
        router.push('/classes');
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
      schoolClass,
      academicYearOptions,
      availableClassNumbers,
      canDelete,
      hasFormChanges,
      updateClassNumber,
      updateClassName,
      resetForm,
      handleSubmit,
      confirmDelete,
      handleDelete
    };
  }
};
</script>