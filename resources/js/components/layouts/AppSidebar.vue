<template>
  <!-- Mobile sidebar overlay -->
  <Transition
    enter-active-class="transition-opacity ease-linear duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity ease-linear duration-300"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open && isMobile"
      class="fixed inset-0 z-45 lg:hidden"
      @click="$emit('update:open', false)"
    >
      <div class="fixed inset-0 bg-gray-900/80"></div>
    </div>
  </Transition>
  
  <!-- Sidebar -->
  <Transition
    enter-active-class="transition ease-in-out duration-300 transform"
    enter-from-class="-translate-x-full"
    enter-to-class="translate-x-0"
    leave-active-class="transition ease-in-out duration-300 transform"
    leave-from-class="translate-x-0"
    leave-to-class="-translate-x-full"
  >
    <div
      v-if="open || !isMobile"
      class="fixed-sidebar"
      :class="{ 'lg:block': !isMobile }"
    >
      <!-- Close button for mobile -->
      <div v-if="isMobile" class="flex justify-end p-4">
        <button
          @click="$emit('update:open', false)"
          class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
          aria-label="サイドバーを閉じる"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>
      
      <!-- Navigation -->
      <nav class="flex flex-col flex-1 px-4 py-4 space-y-1 sidebar-scroll">
        <template v-for="item in navigation" :key="item.name">
          <!-- Navigation item with children -->
          <div v-if="item.children" class="space-y-1">
            <button
              @click="toggleGroup(item.name)"
              :class="[
                item.current
                  ? 'bg-blue-50 border-blue-500 text-blue-700'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                'group flex w-full items-center justify-between rounded-md border-l-4 py-2 pl-3 pr-2 text-sm font-medium'
              ]"
            >
              <div class="flex items-center">
                <component
                  :is="getIcon(item.icon)"
                  :class="[
                    item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                    'mr-3 h-5 w-5 flex-shrink-0'
                  ]"
                />
                {{ item.name }}
              </div>
              <ChevronRightIcon
                :class="[
                  expandedGroups[item.name] ? 'rotate-90' : '',
                  'ml-3 h-4 w-4 transform transition-transform'
                ]"
              />
            </button>
            
            <!-- Sub-navigation -->
            <Transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="transform scale-95 opacity-0"
              enter-to-class="transform scale-100 opacity-100"
              leave-active-class="transition duration-75 ease-in"
              leave-from-class="transform scale-100 opacity-100"
              leave-to-class="transform scale-95 opacity-0"
            >
              <div
                v-if="expandedGroups[item.name]"
                class="pl-11 space-y-1"
              >
                <router-link
                  v-for="child in item.children"
                  :key="child.name"
                  :to="child.href"
                  :class="[
                    currentRoute === child.routeName
                      ? 'bg-blue-50 text-blue-700 border-blue-500'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                    'group flex items-center rounded-md border-l-4 py-2 pl-3 pr-2 text-sm font-medium'
                  ]"
                  @click="closeMobileSidebar"
                >
                  {{ child.name }}
                </router-link>
              </div>
            </Transition>
          </div>
          
          <!-- Simple navigation item -->
          <router-link
            v-else
            :to="item.href"
            :class="[
              item.current
                ? 'bg-blue-50 border-blue-500 text-blue-700'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent',
              'group flex items-center rounded-md border-l-4 py-2 pl-3 pr-2 text-sm font-medium'
            ]"
            @click="closeMobileSidebar"
          >
            <component
              :is="getIcon(item.icon)"
              :class="[
                item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                'mr-3 h-5 w-5 flex-shrink-0'
              ]"
            />
            {{ item.name }}
          </router-link>
        </template>
      </nav>
      
      <!-- Sidebar footer -->
      <div class="flex-shrink-0 p-4 border-t border-gray-200">
        <div class="text-xs text-gray-500 text-center">
          <p>保健室管理システム</p>
          <p class="mt-1">ver 1.0.0</p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Icons
const HeartIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
    </svg>
  `
};

const XMarkIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  `
};

const ChevronRightIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
  `
};

const DashboardIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
    </svg>
  `
};

const StudentIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
    </svg>
  `
};

const ClassIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
  `
};

const HealthIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
    </svg>
  `
};

const ClipboardCheckIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>
  `
};

const ClipboardDocumentCheckIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/>
    </svg>
  `
};

const ReportIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
  `
};

const SettingsIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
  `
};

export default {
  name: 'AppSidebar',
  components: {
    HeartIcon,
    XMarkIcon,
    ChevronRightIcon,
    DashboardIcon,
    StudentIcon,
    ClassIcon,
    HealthIcon,
    ClipboardCheckIcon,
    ClipboardDocumentCheckIcon,
    ReportIcon,
    SettingsIcon
  },
  props: {
    open: {
      type: Boolean,
      default: false
    },
    
    navigation: {
      type: Array,
      required: true
    },
    
    currentRoute: {
      type: String,
      default: ''
    }
  },
  
  emits: ['update:open'],
  
  setup(props, { emit }) {
    // State
    const expandedGroups = ref({});
    const isMobile = ref(false);
    
    // Methods
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth < 1024;
    };
    
    const toggleGroup = (groupName) => {
      expandedGroups.value[groupName] = !expandedGroups.value[groupName];
    };
    
    const closeMobileSidebar = () => {
      if (isMobile.value) {
        emit('update:open', false);
      }
    };
    
    const getIcon = (iconName) => {
      const iconMap = {
        DashboardIcon: 'DashboardIcon',
        StudentIcon: 'StudentIcon',
        ClassIcon: 'ClassIcon',
        HealthIcon: 'HealthIcon',
        ClipboardCheckIcon: 'ClipboardCheckIcon',
        ClipboardDocumentCheckIcon: 'ClipboardDocumentCheckIcon',
        ReportIcon: 'ReportIcon',
        SettingsIcon: 'SettingsIcon'
      };
      return iconMap[iconName] || 'DashboardIcon';
    };
    
    const initializeExpandedGroups = () => {
      // Auto-expand groups based on current route
      props.navigation.forEach(item => {
        if (item.children && item.current) {
          expandedGroups.value[item.name] = true;
        }
      });
    };
    
    // Lifecycle
    onMounted(() => {
      checkIsMobile();
      window.addEventListener('resize', checkIsMobile);
      initializeExpandedGroups();
    });
    
    onUnmounted(() => {
      window.removeEventListener('resize', checkIsMobile);
    });
    
    return {
      expandedGroups,
      isMobile,
      toggleGroup,
      closeMobileSidebar,
      getIcon
    };
  }
};
</script>

<style scoped>
.sidebar-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.sidebar-scroll::-webkit-scrollbar {
  width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.7);
}
</style>