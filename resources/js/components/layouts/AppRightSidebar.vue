<template>
  <aside class="fixed inset-y-0 right-0 hidden w-96 overflow-y-auto border-l border-gray-200 bg-white px-4 py-6 sm:px-6 lg:px-8 xl:block">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">詳細情報</h3>
        <button
          @click="$emit('close')"
          class="rounded-md text-gray-400 hover:text-gray-500"
          aria-label="右サイドバーを閉じる"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>
      
      <!-- Content -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div v-if="showQuickActions">
          <h4 class="text-sm font-medium text-gray-900 mb-3">クイックアクション</h4>
          <div class="space-y-2">
            <BaseButton
              v-for="action in quickActions"
              :key="action.id"
              :variant="action.variant || 'secondary'"
              size="sm"
              class="w-full justify-start"
              @click="action.handler"
            >
              <component :is="getIcon(action.icon)" class="h-4 w-4 mr-2" />
              {{ action.label }}
            </BaseButton>
          </div>
        </div>
        
        <!-- Recent Activities -->
        <div v-if="showRecentActivities">
          <h4 class="text-sm font-medium text-gray-900 mb-3">最近の活動</h4>
          <div class="space-y-3">
            <div
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center',
                  activity.type === 'create' ? 'bg-green-100 text-green-600' : 
                  activity.type === 'update' ? 'bg-blue-100 text-blue-600' : 
                  activity.type === 'delete' ? 'bg-red-100 text-red-600' : 
                  'bg-gray-100 text-gray-600'
                ]">
                  <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">{{ activity.description }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(activity.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Statistics -->
        <div v-if="showStatistics">
          <h4 class="text-sm font-medium text-gray-900 mb-3">統計情報</h4>
          <div class="space-y-4">
            <div
              v-for="stat in statistics"
              :key="stat.key"
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ stat.label }}</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                </div>
                <div :class="[
                  'w-12 h-12 rounded-lg flex items-center justify-center',
                  stat.color || 'bg-blue-100 text-blue-600'
                ]">
                  <component :is="getIcon(stat.icon)" class="h-6 w-6" />
                </div>
              </div>
              <div v-if="stat.description" class="mt-2">
                <p class="text-xs text-gray-600">{{ stat.description }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Help & Tips -->
        <div v-if="showHelp">
          <h4 class="text-sm font-medium text-gray-900 mb-3">ヘルプ・ヒント</h4>
          <div class="space-y-3">
            <div
              v-for="tip in helpTips"
              :key="tip.id"
              class="bg-blue-50 border border-blue-200 rounded-lg p-3"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <LightBulbIcon class="h-5 w-5 text-blue-600" />
                </div>
                <div class="ml-3">
                  <h5 class="text-sm font-medium text-blue-900">{{ tip.title }}</h5>
                  <p class="text-sm text-blue-700 mt-1">{{ tip.content }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Slot for custom content -->
        <div v-if="$slots.default">
          <slot></slot>
        </div>
      </div>
    </div>
  </aside>
</template>

<script>
import { computed } from 'vue';
import { BaseButton } from '../index.js';

// Icons
const XMarkIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  `
};

const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
  `
};

const PencilIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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

const EyeIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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

const ChartBarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
  `
};

const UsersIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
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
  name: 'AppRightSidebar',
  components: {
    BaseButton,
    XMarkIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
    LightBulbIcon,
    ChartBarIcon,
    UsersIcon,
    HeartIcon
  },
  props: {
    // Content sections visibility
    showQuickActions: {
      type: Boolean,
      default: true
    },
    
    showRecentActivities: {
      type: Boolean,
      default: true
    },
    
    showStatistics: {
      type: Boolean,
      default: true
    },
    
    showHelp: {
      type: Boolean,
      default: true
    },
    
    // Content data
    quickActions: {
      type: Array,
      default: () => [
        {
          id: 'add-student',
          label: '学生を追加',
          icon: 'PlusIcon',
          variant: 'primary',
          handler: () => console.log('Add student')
        },
        {
          id: 'add-health-record',
          label: '健康記録を追加',
          icon: 'HeartIcon',
          variant: 'success',
          handler: () => console.log('Add health record')
        }
      ]
    },
    
    recentActivities: {
      type: Array,
      default: () => [
        {
          id: 1,
          type: 'create',
          description: '山田太郎の健康記録を追加しました',
          created_at: '2025-09-18T10:30:00Z'
        },
        {
          id: 2,
          type: 'update',
          description: '1年A組の情報を更新しました',
          created_at: '2025-09-18T09:15:00Z'
        }
      ]
    },
    
    statistics: {
      type: Array,
      default: () => [
        {
          key: 'total-students',
          label: '総学生数',
          value: '302',
          icon: 'UsersIcon',
          color: 'bg-blue-100 text-blue-600',
          description: '全学年の学生数'
        },
        {
          key: 'health-records-today',
          label: '今日の記録数',
          value: '12',
          icon: 'HeartIcon',
          color: 'bg-green-100 text-green-600',
          description: '本日追加された健康記録'
        }
      ]
    },
    
    helpTips: {
      type: Array,
      default: () => [
        {
          id: 1,
          title: '検索のコツ',
          content: '学生番号、名前、クラス名での検索が可能です。'
        },
        {
          id: 2,
          title: 'データ取込み',
          content: 'CSVファイルから一括でデータを取込むことができます。'
        }
      ]
    }
  },
  
  emits: ['close'],
  
  setup() {
    // Methods
    const getIcon = (iconName) => {
      const iconMap = {
        'PlusIcon': 'PlusIcon',
        'PencilIcon': 'PencilIcon',
        'TrashIcon': 'TrashIcon',
        'EyeIcon': 'EyeIcon',
        'ChartBarIcon': 'ChartBarIcon',
        'UsersIcon': 'UsersIcon',
        'HeartIcon': 'HeartIcon'
      };
      return iconMap[iconName] || 'EyeIcon';
    };
    
    const getActivityIcon = (activityType) => {
      const iconMap = {
        'create': 'PlusIcon',
        'update': 'PencilIcon',
        'delete': 'TrashIcon',
        'view': 'EyeIcon'
      };
      return iconMap[activityType] || 'EyeIcon';
    };
    
    const formatDateTime = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diff = now - date;
      
      // Less than 1 hour
      if (diff < 60 * 60 * 1000) {
        const minutes = Math.floor(diff / (60 * 1000));
        return `${minutes}分前`;
      }
      
      // Less than 24 hours
      if (diff < 24 * 60 * 60 * 1000) {
        const hours = Math.floor(diff / (60 * 60 * 1000));
        return `${hours}時間前`;
      }
      
      // More than 24 hours
      return date.toLocaleDateString('ja-JP', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };
    
    return {
      getIcon,
      getActivityIcon,
      formatDateTime
    };
  }
};
</script>