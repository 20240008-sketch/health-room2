import { createRouter, createWebHistory } from 'vue-router';
import { useMainStore } from '@/stores/main.js';
import AppLayout from '@/components/layouts/AppLayout.vue';

// Import view components (will be created later)
const Dashboard = () => import('@/views/Dashboard.vue');
const StudentIndex = () => import('@/views/students/StudentIndex.vue');
const StudentCreate = () => import('@/views/students/StudentCreate.vue');
const StudentShow = () => import('@/views/students/StudentShow.vue');
const StudentEdit = () => import('@/views/students/StudentEdit.vue');
const ClassIndex = () => import('@/views/classes/ClassIndex.vue');
const ClassCreate = () => import('@/views/classes/ClassCreate.vue');
const ClassShow = () => import('@/views/classes/ClassShow.vue');
const ClassEdit = () => import('@/views/classes/ClassEdit.vue');
const HealthRecordIndex = () => import('@/views/health-records/HealthRecordIndex.vue');
const HealthRecordCreate = () => import('@/views/health-records/HealthRecordCreate.vue');
const HealthRecordShow = () => import('@/views/health-records/HealthRecordShow.vue');
const HealthRecordEdit = () => import('@/views/health-records/HealthRecordEdit.vue');
const ReportsHealthCheckup = () => import('@/views/Dashboard.vue'); // 一時的
const ReportsBmiAnalysis = () => import('@/views/Dashboard.vue'); // 一時的
const ReportsGrowth = () => import('@/views/Dashboard.vue'); // 一時的
const SettingsAccount = () => import('@/views/Dashboard.vue'); // 一時的
const SettingsSystem = () => import('@/views/Dashboard.vue'); // 一時的

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'ダッシュボード',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' }
      ]
    }
  },
  
  // Students routes
  {
    path: '/students',
    name: 'students.index',
    component: StudentIndex,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '学生一覧',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '学生管理', href: '/students' }
      ]
    }
  },
  {
    path: '/students/create',
    name: 'students.create',
    component: StudentCreate,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '学生登録',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '学生管理', href: '/students' },
        { name: '学生登録', href: '/students/create' }
      ]
    }
  },
  {
    path: '/students/:id',
    name: 'students.show',
    component: StudentShow,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '学生詳細',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '学生管理', href: '/students' },
        { name: '学生詳細', href: '#' }
      ]
    }
  },
  {
    path: '/students/:id/edit',
    name: 'students.edit',
    component: StudentEdit,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '学生編集',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '学生管理', href: '/students' },
        { name: '学生編集', href: '#' }
      ]
    }
  },
  
  // Classes routes
  {
    path: '/classes',
    name: 'classes.index',
    component: ClassIndex,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'クラス一覧',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'クラス管理', href: '/classes' }
      ]
    }
  },
  {
    path: '/classes/create',
    name: 'classes.create',
    component: ClassCreate,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'クラス作成',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'クラス管理', href: '/classes' },
        { name: 'クラス作成', href: '/classes/create' }
      ]
    }
  },
  {
    path: '/classes/:id',
    name: 'classes.show',
    component: ClassShow,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'クラス詳細',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'クラス管理', href: '/classes' },
        { name: 'クラス詳細', href: '#' }
      ]
    }
  },
  {
    path: '/classes/:id/edit',
    name: 'classes.edit',
    component: ClassEdit,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'クラス編集',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'クラス管理', href: '/classes' },
        { name: 'クラス編集', href: '#' }
      ]
    }
  },
  
  // Health Records routes
  {
    path: '/health-records',
    name: 'health-records.index',
    component: HealthRecordIndex,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '健康記録一覧',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '健康記録', href: '/health-records' }
      ]
    }
  },
  {
    path: '/health-records/create',
    name: 'health-records.create',
    component: HealthRecordCreate,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '健康記録追加',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '健康記録', href: '/health-records' },
        { name: '記録追加', href: '/health-records/create' }
      ]
    }
  },
  {
    path: '/health-records/:id',
    name: 'health-records.show',
    component: HealthRecordShow,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '健康記録詳細',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '健康記録', href: '/health-records' },
        { name: '記録詳細', href: '#' }
      ]
    }
  },
  {
    path: '/health-records/:id/edit',
    name: 'health-records.edit',
    component: HealthRecordEdit,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '健康記録編集',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '健康記録', href: '/health-records' },
        { name: '記録編集', href: '#' }
      ]
    }
  },
  {
    path: '/health-records/statistics',
    name: 'health-records.statistics',
    component: HealthRecordIndex, // 統計コンポーネント実装まで一時的にIndexを使用
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '統計・分析',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '健康記録', href: '/health-records' },
        { name: '統計・分析', href: '/health-records/statistics' }
      ]
    }
  },
  
  // Reports routes
  {
    path: '/reports/health-checkup',
    name: 'reports.health-checkup',
    component: ReportsHealthCheckup,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '健康診断レポート',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'レポート', href: '#' },
        { name: '健康診断レポート', href: '/reports/health-checkup' }
      ]
    }
  },
  {
    path: '/reports/bmi-analysis',
    name: 'reports.bmi-analysis',
    component: ReportsBmiAnalysis,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'BMI分析',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'レポート', href: '#' },
        { name: 'BMI分析', href: '/reports/bmi-analysis' }
      ]
    }
  },
  {
    path: '/reports/growth',
    name: 'reports.growth',
    component: ReportsGrowth,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: '成長記録',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: 'レポート', href: '#' },
        { name: '成長記録', href: '/reports/growth' }
      ]
    }
  },
  
  // Settings routes
  {
    path: '/settings/account',
    name: 'settings.account',
    component: SettingsAccount,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'アカウント設定',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '設定', href: '/settings' },
        { name: 'アカウント設定', href: '/settings/account' }
      ]
    }
  },
  {
    path: '/settings/system',
    name: 'settings.system',
    component: SettingsSystem,
    meta: {
      requiresAuth: true,
      layout: AppLayout,
      title: 'システム設定',
      breadcrumb: [
        { name: 'ホーム', href: '/dashboard' },
        { name: '設定', href: '/settings' },
        { name: 'システム設定', href: '/settings/system' }
      ]
    }
  },
  
  // Catch all 404
  {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/Dashboard.vue'),
      meta: {
        title: 'ページが見つかりません'
      }
    }
];

const router = createRouter({
  history: createWebHistory('/'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const mainStore = useMainStore();
  
  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Check if user is authenticated
    if (!mainStore.currentUser) {
      try {
        await mainStore.fetchCurrentUser();
      } catch (error) {
        // Redirect to login or show error
        console.error('Authentication check failed:', error);
        // For now, continue without authentication
        // In production, redirect to login page
      }
    }
  }
  
  // Set page title
  if (to.meta.title) {
    document.title = `${to.meta.title} | 保健室システム`;
  } else {
    document.title = '保健室システム';
  }
  
  next();
});

export default router;