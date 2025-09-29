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
                  <span class="ml-4 text-sm font-medium text-gray-500">
                    {{ schoolClass?.name }}
                  </span>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            {{ schoolClass?.name }}
          </h1>
          <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <AcademicCapIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ schoolClass?.academic_year }}年度
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <UsersIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ schoolClass?.students_count || 0 }}名の学生
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <BaseBadge :variant="schoolClass?.is_active ? 'success' : 'secondary'">
                {{ schoolClass?.is_active ? 'アクティブ' : '非アクティブ' }}
              </BaseBadge>
            </div>
          </div>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="$router.push(`/classes/${schoolClass?.id}/edit`)"
          >
            <PencilIcon class="h-4 w-4 mr-2" />
            編集
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="$router.push(`/students/create?class_id=${schoolClass?.id}`)"
          >
            <UserPlusIcon class="h-4 w-4 mr-2" />
            学生追加
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <BaseSpinner size="lg" text="読み込み中..." />
    </div>

    <!-- Content -->
    <div v-else-if="schoolClass" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Class Overview -->
        <BaseCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">クラス概要</h2>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Basic Information -->
            <div class="space-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">学年・組</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">
                  {{ schoolClass.grade }}年{{ schoolClass.class_number }}組
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">年度</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ schoolClass.academic_year }}年度</dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">定員</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ schoolClass.capacity ? `${schoolClass.capacity}名` : '未設定' }}
                </dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">現在の学生数</dt>
                <dd class="mt-1 flex items-center">
                  <span class="text-2xl font-bold text-blue-600">
                    {{ schoolClass.students_count || 0 }}
                  </span>
                  <span class="ml-1 text-sm text-gray-500">名</span>
                  <div v-if="schoolClass.capacity" class="ml-3">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full"
                        :style="{ width: `${Math.min(100, (schoolClass.students_count / schoolClass.capacity) * 100)}%` }"
                      ></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                      {{ Math.round((schoolClass.students_count / schoolClass.capacity) * 100) }}% 充足率
                    </p>
                  </div>
                </dd>
              </div>
            </div>

            <!-- Teacher Information -->
            <div class="space-y-4">
              <div v-if="schoolClass.homeroom_teacher">
                <dt class="text-sm font-medium text-gray-500">担任教師</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                  {{ schoolClass.homeroom_teacher }}
                </dd>
              </div>

              <div v-if="schoolClass.assistant_teacher">
                <dt class="text-sm font-medium text-gray-500">副担任教師</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                  {{ schoolClass.assistant_teacher }}
                </dd>
              </div>

              <div v-if="schoolClass.room_number || schoolClass.building">
                <dt class="text-sm font-medium text-gray-500">教室</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ schoolClass.building ? `${schoolClass.building} ` : '' }}{{ schoolClass.room_number }}
                  {{ schoolClass.floor ? ` (${schoolClass.floor}階)` : '' }}
                </dd>
              </div>
            </div>
          </div>

          <div v-if="schoolClass.description" class="mt-6 pt-6 border-t border-gray-200">
            <dt class="text-sm font-medium text-gray-500">説明・備考</dt>
            <dd class="mt-2 text-sm text-gray-900 whitespace-pre-wrap">{{ schoolClass.description }}</dd>
          </div>
        </BaseCard>

        <!-- Students List -->
        <BaseCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">
                学生一覧 ({{ students.length }}名)
              </h2>
              <div class="flex space-x-2">
                <BaseButton
                  size="sm"
                  variant="secondary"
                  @click="exportStudentList"
                >
                  <DocumentArrowDownIcon class="h-4 w-4 mr-1" />
                  エクスポート
                </BaseButton>
                <BaseButton
                  size="sm"
                  variant="primary"
                  @click="$router.push(`/students/create?class_id=${schoolClass.id}`)"
                >
                  <UserPlusIcon class="h-4 w-4 mr-1" />
                  学生追加
                </BaseButton>
              </div>
            </div>
          </template>

          <!-- Search and Filter -->
          <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex space-x-4">
              <div class="flex-1">
                <BaseInput
                  v-model="searchQuery"
                  placeholder="学生名で検索..."
                  size="sm"
                >
                  <template #prepend>
                    <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                  </template>
                </BaseInput>
              </div>
              <div>
                <BaseInput
                  type="select"
                  v-model="statusFilter"
                  size="sm"
                  class="w-32"
                >
                  <option value="">全ての状態</option>
                  <option value="active">在学中</option>
                  <option value="inactive">休学中</option>
                  <option value="graduated">卒業</option>
                </BaseInput>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredStudents.length === 0" class="text-center py-12">
            <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              {{ searchQuery || statusFilter ? '学生が見つかりません' : '学生がいません' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ searchQuery || statusFilter ? '検索条件を変更してください' : 'このクラスに最初の学生を追加しましょう' }}
            </p>
            <div class="mt-6" v-if="!searchQuery && !statusFilter">
              <BaseButton
                variant="primary"
                @click="$router.push(`/students/create?class_id=${schoolClass.id}`)"
              >
                <UserPlusIcon class="h-4 w-4 mr-2" />
                学生追加
              </BaseButton>
            </div>
          </div>

          <!-- Students Table -->
          <div v-else class="overflow-hidden">
            <BaseTable
              :columns="studentsTableColumns"
              :data="filteredStudents"
              @row-click="(row) => $router.push(`/students/${row.id}`)"
            >
              <template #student_number="{ row }">
                <span class="font-mono text-sm">{{ row.student_number }}</span>
              </template>

              <template #name="{ row }">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-700">
                        {{ row.name.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <div class="font-medium text-gray-900">{{ row.name }}</div>
                    <div v-if="row.name_kana" class="text-sm text-gray-500">{{ row.name_kana }}</div>
                  </div>
                </div>
              </template>

              <template #gender="{ row }">
                <BaseBadge :variant="row.gender === 'male' ? 'blue' : 'pink'">
                  {{ row.gender === 'male' ? '男' : '女' }}
                </BaseBadge>
              </template>

              <template #age="{ row }">
                <span class="text-sm text-gray-900">{{ calculateAge(row.birth_date) }}歳</span>
              </template>

              <template #status="{ row }">
                <BaseBadge
                  :variant="
                    row.status === 'active' ? 'success' :
                    row.status === 'inactive' ? 'warning' : 'secondary'
                  "
                >
                  {{ getStatusLabel(row.status) }}
                </BaseBadge>
              </template>

              <template #latest_health_record="{ row }">
                <div v-if="row.latest_health_record" class="text-sm">
                  <div class="text-gray-900">
                    {{ formatDate(row.latest_health_record.measured_date) }}
                  </div>
                  <div class="text-gray-500">
                    身長 {{ row.latest_health_record.height }}cm / 
                    体重 {{ row.latest_health_record.weight }}kg
                  </div>
                </div>
                <div v-else class="text-sm text-gray-400">
                  記録なし
                </div>
              </template>

              <template #actions="{ row }">
                <div class="flex space-x-2">
                  <BaseButton
                    size="sm"
                    variant="secondary"
                    @click.stop="$router.push(`/students/${row.id}/edit`)"
                  >
                    編集
                  </BaseButton>
                  <BaseButton
                    size="sm"
                    variant="primary"
                    @click.stop="$router.push(`/students/${row.id}`)"
                  >
                    詳細
                  </BaseButton>
                </div>
              </template>
            </BaseTable>
          </div>
        </BaseCard>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Class Statistics -->
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">クラス統計</h3>
          </template>

          <div class="space-y-4">
            <!-- Gender Distribution -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-2">性別構成</h4>
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">男子</span>
                  <div class="flex items-center">
                    <div class="w-16 h-2 bg-gray-200 rounded-full mr-2">
                      <div
                        class="h-2 bg-blue-500 rounded-full"
                        :style="{ width: `${classStats.malePercentage}%` }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium">{{ classStats.maleCount }}名</span>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">女子</span>
                  <div class="flex items-center">
                    <div class="w-16 h-2 bg-gray-200 rounded-full mr-2">
                      <div
                        class="h-2 bg-pink-500 rounded-full"
                        :style="{ width: `${classStats.femalePercentage}%` }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium">{{ classStats.femaleCount }}名</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Average Age -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-2">平均年齢</h4>
              <div class="text-2xl font-bold text-gray-900">
                {{ classStats.averageAge }}歳
              </div>
            </div>

            <!-- Health Records -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-2">健康記録</h4>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">記録数</span>
                <span class="text-sm font-medium">{{ classStats.healthRecordsCount }}件</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">最新測定</span>
                <span class="text-sm font-medium">
                  {{ classStats.latestMeasurement ? formatDate(classStats.latestMeasurement) : '記録なし' }}
                </span>
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
              variant="primary"
              size="sm"
              class="w-full"
              @click="$router.push(`/students/create?class_id=${schoolClass.id}`)"
            >
              <UserPlusIcon class="h-4 w-4 mr-2" />
              学生追加
            </BaseButton>

            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/health-records/create?class_id=${schoolClass.id}`)"
            >
              <HeartIcon class="h-4 w-4 mr-2" />
              健康測定実施
            </BaseButton>

            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="exportStudentList"
            >
              <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
              学生リストエクスポート
            </BaseButton>

            <BaseButton
              variant="secondary"
              size="sm"
              class="w-full"
              @click="$router.push(`/classes/${schoolClass.id}/edit`)"
            >
              <PencilIcon class="h-4 w-4 mr-2" />
              クラス情報編集
            </BaseButton>
          </div>
        </BaseCard>

        <!-- Recent Activity -->
        <BaseCard v-if="recentActivities.length > 0">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">最近の活動</h3>
          </template>

          <div class="space-y-3">
            <div
              v-for="activity in recentActivities.slice(0, 5)"
              :key="activity.id"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">{{ activity.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClassStore } from '@/stores/class.js';
import { useStudentStore } from '@/stores/student.js';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton,
  BaseBadge,
  BaseSpinner,
  BaseTable
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

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

const PencilIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
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

const DocumentArrowDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
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

const HeartIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
  `
};

export default {
  name: 'ClassShow',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    BaseBadge,
    BaseSpinner,
    BaseTable,
    ChevronRightIcon,
    AcademicCapIcon,
    UsersIcon,
    PencilIcon,
    UserPlusIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    HeartIcon
  },
  
  setup() {
    const route = useRoute();
    const router = useRouter();
    const classStore = useClassStore();
    const studentStore = useStudentStore();
    const notificationStore = useNotificationStore();
    
    // State
    const isLoading = ref(true);
    const searchQuery = ref('');
    const statusFilter = ref('');
    
    // Computed
    const schoolClass = computed(() => classStore.currentClass);
    const students = computed(() => studentStore.students);
    const recentActivities = computed(() => [
      // Placeholder for recent activities - would come from API
    ]);
    
    const filteredStudents = computed(() => {
      let result = [...students.value];
      
      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(student =>
          student.name.toLowerCase().includes(query) ||
          student.name_kana?.toLowerCase().includes(query) ||
          student.student_number.toLowerCase().includes(query)
        );
      }
      
      // Status filter
      if (statusFilter.value) {
        result = result.filter(student => student.status === statusFilter.value);
      }
      
      return result;
    });
    
    const classStats = computed(() => {
      const students = filteredStudents.value;
      const totalStudents = students.length;
      
      if (totalStudents === 0) {
        return {
          maleCount: 0,
          femaleCount: 0,
          malePercentage: 0,
          femalePercentage: 0,
          averageAge: 0,
          healthRecordsCount: 0,
          latestMeasurement: null
        };
      }
      
      const maleCount = students.filter(s => s.gender === 'male').length;
      const femaleCount = students.filter(s => s.gender === 'female').length;
      const malePercentage = Math.round((maleCount / totalStudents) * 100);
      const femalePercentage = Math.round((femaleCount / totalStudents) * 100);
      
      const ages = students.map(s => calculateAge(s.birth_date)).filter(age => age > 0);
      const averageAge = ages.length > 0 ? Math.round(ages.reduce((sum, age) => sum + age, 0) / ages.length) : 0;
      
      const healthRecordsCount = students.reduce((sum, s) => sum + (s.health_records_count || 0), 0);
      
      const latestMeasurements = students
        .map(s => s.latest_health_record?.measured_date)
        .filter(date => date)
        .sort((a, b) => new Date(b) - new Date(a));
      
      const latestMeasurement = latestMeasurements.length > 0 ? latestMeasurements[0] : null;
      
      return {
        maleCount,
        femaleCount,
        malePercentage,
        femalePercentage,
        averageAge,
        healthRecordsCount,
        latestMeasurement
      };
    });
    
    const studentsTableColumns = [
      { key: 'student_number', label: '学生番号', sortable: true, width: '120px' },
      { key: 'name', label: '氏名', sortable: true },
      { key: 'gender', label: '性別', sortable: true, width: '80px' },
      { key: 'age', label: '年齢', sortable: true, width: '80px' },
      { key: 'status', label: '状態', sortable: true, width: '100px' },
      { key: 'latest_health_record', label: '最新測定', width: '160px' },
      { key: 'actions', label: '', width: '120px' }
    ];
    
    // Methods
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
    
    const getStatusLabel = (status) => {
      const statusLabels = {
        active: '在学中',
        inactive: '休学中',
        graduated: '卒業'
      };
      return statusLabels[status] || status;
    };
    
    const exportStudentList = () => {
      // Implement CSV export functionality
      const csvContent = [
        ['学生番号', '氏名', 'ふりがな', '性別', '生年月日', '年齢', '状態'],
        ...filteredStudents.value.map(student => [
          student.student_number,
          student.name,
          student.name_kana || '',
          student.gender === 'male' ? '男' : '女',
          student.birth_date,
          calculateAge(student.birth_date),
          getStatusLabel(student.status)
        ])
      ].map(row => row.join(',')).join('\n');
      
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `${schoolClass.value.name}_学生一覧.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      notificationStore.addNotification({
        type: 'success',
        title: 'エクスポート完了',
        message: '学生一覧をCSVファイルでダウンロードしました'
      });
    };
    
    // Lifecycle
    onMounted(async () => {
      const classId = route.params.id;
      
      try {
        await Promise.all([
          classStore.fetchClass(classId),
          studentStore.fetchStudents({ class_id: classId })
        ]);
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
      searchQuery,
      statusFilter,
      schoolClass,
      students,
      filteredStudents,
      classStats,
      recentActivities,
      studentsTableColumns,
      calculateAge,
      formatDate,
      getStatusLabel,
      exportStudentList
    };
  }
};
</script>