<template>
  <header class="fixed-header flex items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
    <!-- Logo and title -->
    <div class="flex items-center">
      <router-link to="/dashboard" class="flex items-center">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <HeartIcon class="w-5 h-5 text-white" />
        </div>
        <span class="ml-3 text-lg font-semibold text-gray-900">保健室システム</span>
      </router-link>
    </div>
    
    <!-- Separator -->
    <div class="h-6 w-px bg-gray-200 lg:block hidden" aria-hidden="true" />
    
    <!-- Breadcrumb -->
    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
      <nav class="flex flex-1 items-center" aria-label="パンくずリスト">
        <ol role="list" class="flex items-center space-x-4">
          <li>
            <div class="flex">
              <router-link 
                to="/dashboard" 
                class="text-sm font-medium text-gray-500 hover:text-gray-700"
              >
                ホーム
              </router-link>
            </div>
          </li>
          <li v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.name">
            <div class="flex items-center">
              <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />
              <router-link
                v-if="index < breadcrumbs.length - 1"
                :to="breadcrumb.href"
                class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
              >
                {{ breadcrumb.name }}
              </router-link>
              <span
                v-else
                class="ml-4 text-sm font-medium text-gray-900"
                aria-current="page"
              >
                {{ breadcrumb.name }}
              </span>
            </div>
          </li>
        </ol>
      </nav>
    </div>
    
    <!-- Right side -->
    <div class="flex items-center gap-x-4 lg:gap-x-6">
      <!-- Search -->
      <div class="hidden lg:block lg:flex-1 lg:max-w-md">
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <SearchIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
          </div>
          <input
            v-model="searchQuery"
            @keydown.enter="handleSearch"
            type="search"
            placeholder="学生名、クラス名で検索..."
            class="block w-full rounded-md border-0 bg-white py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
          />
        </div>
      </div>
      
      <!-- Notifications -->
      <div class="relative">
        <button
          type="button"
          @click="toggleNotifications"
          class="notification-button relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          aria-label="通知を表示"
        >
          <span class="sr-only">通知を表示</span>
          <BellIcon class="h-6 w-6" />
          <!-- Notification badge -->
          <span
            v-if="notificationCount > 0"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
          >
            {{ notificationCount }}
          </span>
        </button>
        
        <!-- Notifications dropdown menu -->
        <Transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="notificationsOpen"
            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            role="menu"
            aria-orientation="vertical"
            tabindex="-1"
          >
            <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
              通知
            </div>
            <div class="max-h-96 overflow-y-auto">
              <div class="p-4 text-center text-sm text-gray-500">
                新しい通知はありません
              </div>
            </div>
          </div>
        </Transition>
      </div>
      
      <!-- Profile dropdown -->
      <div class="relative">
        <button
          type="button"
          @click="toggleProfileMenu"
          class="flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-gray-900 hover:bg-gray-50"
          aria-expanded="false"
          aria-haspopup="true"
        >
          <!-- Avatar -->
          <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
            <UserIcon class="h-5 w-5 text-gray-500" />
          </div>
          
          <!-- User info -->
          <div class="hidden lg:flex lg:flex-col lg:items-start lg:leading-6">
            <span class="text-sm font-semibold text-gray-900">
              {{ currentUser?.name || 'ゲスト' }}
            </span>
            <span class="text-xs text-gray-600">
              {{ currentUser?.role || '教職員' }}
            </span>
          </div>
          
          <!-- Dropdown arrow -->
          <ChevronDownIcon class="h-5 w-5 text-gray-400 lg:block hidden" />
        </button>
        
        <!-- Profile dropdown menu -->
        <Transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="profileMenuOpen"
            class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            role="menu"
            aria-orientation="vertical"
            tabindex="-1"
          >
            <router-link
              to="/profile"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              role="menuitem"
              @click="profileMenuOpen = false"
            >
              プロフィール
            </router-link>
            <router-link
              to="/settings"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              role="menuitem"
              @click="profileMenuOpen = false"
            >
              設定
            </router-link>
            <hr class="my-1">
            <button
              @click="handleLogout"
              class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
              role="menuitem"
            >
              ログアウト
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification.js';

// Icons
const MenuIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  `
};

const ChevronRightIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
  `
};

const ChevronDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  `
};

const SearchIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
  `
};

const BellIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>
  `
};

const UserIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
  `
};

const HeartIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
    </svg>
  `
};

export default {
  name: 'AppHeader',
  components: {
    MenuIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    SearchIcon,
    BellIcon,
    UserIcon,
    HeartIcon
  },
  props: {
    sidebarOpen: {
      type: Boolean,
      default: false
    },
    
    currentUser: {
      type: Object,
      default: null
    }
  },
  
  emits: ['update:sidebarOpen', 'logout'],
  
  setup(props, { emit }) {
    const route = useRoute();
    const router = useRouter();
    const notificationStore = useNotificationStore();
    
    // State
    const searchQuery = ref('');
    const profileMenuOpen = ref(false);
    const notificationsOpen = ref(false);
    
    // Computed
    const notificationCount = computed(() => notificationStore.unreadCount);
    
    const breadcrumbs = computed(() => {
      const crumbs = [];
      const pathSegments = route.path.split('/').filter(segment => segment);
      
      // Route name based breadcrumbs
      const routeMap = {
        'dashboard': { name: 'ダッシュボード', href: '/dashboard' },
        'students': { name: '学生管理', href: '/students' },
        'students.index': { name: '学生一覧', href: '/students' },
        'students.create': { name: '学生登録', href: '/students/create' },
        'students.show': { name: '学生詳細', href: '#' },
        'students.edit': { name: '学生編集', href: '#' },
        'classes': { name: 'クラス管理', href: '/classes' },
        'classes.index': { name: 'クラス一覧', href: '/classes' },
        'classes.create': { name: 'クラス作成', href: '/classes/create' },
        'health-records': { name: '健康記録', href: '/health-records' },
        'health-records.index': { name: '記録一覧', href: '/health-records' },
        'health-records.create': { name: '記録追加', href: '/health-records/create' },
        'health-records.statistics': { name: '統計・分析', href: '/health-records/statistics' },
        'reports': { name: 'レポート', href: '/reports' },
        'settings': { name: '設定', href: '/settings' }
      };
      
      if (route.name && routeMap[route.name]) {
        // Build breadcrumb hierarchy
        const parts = route.name.split('.');
        let currentPath = '';
        
        parts.forEach((part, index) => {
          if (index === 0) {
            currentPath = part;
          } else {
            currentPath += '.' + part;
          }
          
          if (routeMap[currentPath]) {
            crumbs.push(routeMap[currentPath]);
          }
        });
      }
      
      return crumbs;
    });
    
    // Methods
    const handleSearch = () => {
      if (searchQuery.value.trim()) {
        router.push({ 
          name: 'students.index', 
          query: { search: searchQuery.value.trim() }
        });
        searchQuery.value = '';
      }
    };
    
    const toggleProfileMenu = () => {
      profileMenuOpen.value = !profileMenuOpen.value;
      if (notificationsOpen.value) {
        notificationsOpen.value = false;
      }
    };
    
    const toggleNotifications = () => {
      notificationsOpen.value = !notificationsOpen.value;
      if (profileMenuOpen.value) {
        profileMenuOpen.value = false;
      }
    };
    
    const handleLogout = () => {
      profileMenuOpen.value = false;
      emit('logout');
    };
    
    const handleClickOutside = (event) => {
      const target = event.target;
      
      // Close profile menu if clicked outside
      if (profileMenuOpen.value && !target.closest('[aria-haspopup="true"]')) {
        profileMenuOpen.value = false;
      }
      
      // Close notifications if clicked outside
      if (notificationsOpen.value && !target.closest('.notification-button')) {
        notificationsOpen.value = false;
      }
    };
    
    // Lifecycle
    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
    });
    
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });
    
    return {
      searchQuery,
      profileMenuOpen,
      notificationsOpen,
      notificationCount,
      breadcrumbs,
      handleSearch,
      toggleProfileMenu,
      toggleNotifications,
      handleLogout
    };
  }
};
</script>