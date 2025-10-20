<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Fixed Global Menu Container -->
    <div class="global-menu-container">
      <!-- Header -->
      <AppHeader 
        v-model:sidebarOpen="sidebarOpen"
        :currentUser="currentUser"
        @logout="handleLogout"
      />
      
      <!-- Sidebar -->
      <AppSidebar 
        :open="true"
        :navigation="navigation"
        :currentRoute="currentRoute"
      />
    </div>
    
    <!-- Main content area -->
    <div class="content-with-fixed-header lg:pl-64">
      <!-- Main content -->
      <div class="px-4 py-8 sm:px-6 lg:px-8">
        <!-- Page header -->
        <div v-if="$slots.header" class="mb-8">
          <slot name="header"></slot>
        </div>
        
        <!-- Page content -->
        <main>
          <slot></slot>
        </main>
      </div>
      
      <!-- Right sidebar -->
      <AppRightSidebar v-if="showRightSidebar">
        <slot name="rightSidebar"></slot>
      </AppRightSidebar>
    </div>
    
    <!-- Notifications -->
    <NotificationContainer />
    
    <!-- Loading overlay -->
    <BaseSpinner 
      v-if="isLoading" 
      overlay 
      size="lg" 
      text="読み込み中..." 
      color="blue"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';
import { useNotificationStore } from '@/stores/notification.js';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';
import AppRightSidebar from './AppRightSidebar.vue';
import NotificationContainer from '../notifications/NotificationContainer.vue';
import { BaseSpinner } from '../index.js';

export default {
  name: 'AppLayout',
  components: {
    AppHeader,
    AppSidebar,
    AppRightSidebar,
    NotificationContainer,
    BaseSpinner
  },
  props: {
    // Right sidebar visibility
    showRightSidebar: {
      type: Boolean,
      default: false
    },
    
    // Custom navigation items
    customNavigation: {
      type: Array,
      default: null
    }
  },
  
  setup(props) {
    const route = useRoute();
    const authStore = useAuthStore();
    const notificationStore = useNotificationStore();
    
    // State
    const sidebarOpen = ref(false);
    
    // Computed properties
    const currentUser = computed(() => authStore.user);
    const isLoading = computed(() => authStore.isLoading);
    const currentRoute = computed(() => route.name);
    
    // Navigation items
    const navigation = computed(() => {
      if (props.customNavigation) {
        return props.customNavigation;
      }
      
      return [
        {
          name: 'ダッシュボード',
          href: '/dashboard',
          routeName: 'dashboard',
          icon: 'DashboardIcon',
          current: route.name === 'dashboard'
        },
        {
          name: '学生管理',
          href: '/students',
          routeName: 'students',
          icon: 'StudentIcon',
          current: route.name?.startsWith('students'),
          children: [
            {
              name: '学生一覧',
              href: '/students',
              routeName: 'students.index'
            },
            {
              name: '学生登録',
              href: '/students/create',
              routeName: 'students.create'
            }
          ]
        },
        {
          name: 'クラス管理',
          href: '/classes',
          routeName: 'classes',
          icon: 'ClassIcon',
          current: route.name?.startsWith('classes'),
          children: [
            {
              name: 'クラス一覧',
              href: '/classes',
              routeName: 'classes.index'
            },
            {
              name: 'クラス作成',
              href: '/classes/create',
              routeName: 'classes.create'
            }
          ]
        },
        {
          name: '健康記録',
          href: '/health-records',
          routeName: 'health-records',
          icon: 'HealthIcon',
          current: route.name?.startsWith('health-records'),
          children: [
            {
              name: '記録一覧',
              href: '/health-records',
              routeName: 'health-records.index'
            },
            {
              name: '記録追加',
              href: '/health-records/create',
              routeName: 'health-records.create'
            },
            {
              name: '統計・分析',
              href: '/health-records/statistics',
              routeName: 'health-records.statistics'
            }
          ]
        },
        {
          name: '出席・保健室記録',
          href: '/attendance-registration/attendance',
          routeName: 'attendance-registration',
          icon: 'ClipboardDocumentCheckIcon',
          current: route.name?.startsWith('attendance-registration'),
          children: [
            {
              name: '出席記録一覧',
              href: '/attendance-registration/attendance',
              routeName: 'attendance-registration.attendance.index'
            },
            {
              name: '保健室記録一覧',
              href: '/attendance-registration/nursing',
              routeName: 'attendance-registration.nursing.index'
            },
            {
              name: '出席記録入力',
              href: '/attendance-registration/attendance/create',
              routeName: 'attendance-registration.attendance.create'
            },
            {
              name: '保健室記録入力',
              href: '/attendance-registration/nursing/create',
              routeName: 'attendance-registration.nursing.create'
            }
          ]
        },
        {
          name: '出席確認',
          href: '/attendance',
          routeName: 'attendance.index',
          icon: 'ClipboardCheckIcon',
          current: route.name?.startsWith('attendance')
        },
        {
          name: 'レポート',
          href: '/reports',
          routeName: 'reports',
          icon: 'ReportIcon',
          current: route.name?.startsWith('reports'),
          children: [
            {
              name: '健康診断レポート',
              href: '/reports/health-checkup',
              routeName: 'reports.health-checkup'
            },
            {
              name: 'BMI分析',
              href: '/reports/bmi-analysis',
              routeName: 'reports.bmi-analysis'
            },
            {
              name: '成長記録',
              href: '/reports/growth',
              routeName: 'reports.growth'
            }
          ]
        },
        {
          name: '設定',
          href: '/settings',
          routeName: 'settings',
          icon: 'SettingsIcon',
          current: route.name?.startsWith('settings'),
          children: [
            {
              name: 'アカウント設定',
              href: '/settings/account',
              routeName: 'settings.account'
            },
            {
              name: 'システム設定',
              href: '/settings/system',
              routeName: 'settings.system'
            }
          ]
        }
      ];
    });
    
    // Methods
    const handleLogout = async () => {
      try {
        await authStore.logout();
        notificationStore.showSuccess('ログアウトしました');
      } catch (error) {
        notificationStore.showError('ログアウトに失敗しました');
      }
    };
    
    // Lifecycle
    onMounted(async () => {
      // Initialize app data
      if (!currentUser.value) {
        try {
          await authStore.fetchCurrentUser();
        } catch (error) {
          console.error('Failed to fetch current user:', error);
        }
      }
    });
    
    return {
      sidebarOpen,
      currentUser,
      isLoading,
      currentRoute,
      navigation,
      handleLogout
    };
  }
};
</script>

<style scoped>
/* Custom scrollbar for sidebar */
:deep(.sidebar-scroll) {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

:deep(.sidebar-scroll::-webkit-scrollbar) {
  width: 6px;
}

:deep(.sidebar-scroll::-webkit-scrollbar-track) {
  background: transparent;
}

:deep(.sidebar-scroll::-webkit-scrollbar-thumb) {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

:deep(.sidebar-scroll::-webkit-scrollbar-thumb:hover) {
  background-color: rgba(156, 163, 175, 0.7);
}
</style>