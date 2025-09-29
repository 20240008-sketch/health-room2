<template>
  <Teleport to="body">
    <!-- Notification container -->
    <div
      class="fixed top-0 right-0 z-50 p-4 space-y-4 w-full max-w-sm pointer-events-none"
      style="top: 1rem;"
    >
      <TransitionGroup
        name="notification"
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-for="notification in visibleNotifications"
          :key="notification.id"
          class="pointer-events-auto"
        >
          <BaseAlert
            :variant="notification.type"
            :title="notification.title"
            :message="notification.message"
            :closable="true"
            :auto-close="notification.autoClose || 5000"
            @close="removeNotification(notification.id)"
          >
            <!-- Custom actions if provided -->
            <template v-if="notification.actions" #actions>
              <BaseButton
                v-for="action in notification.actions"
                :key="action.id"
                :variant="action.variant || 'secondary'"
                size="sm"
                @click="handleAction(action, notification)"
              >
                {{ action.label }}
              </BaseButton>
            </template>
          </BaseAlert>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script>
import { computed, onMounted, onUnmounted } from 'vue';
import { useNotificationStore } from '@/stores/notification.js';
import { BaseAlert, BaseButton } from '../index.js';

export default {
  name: 'NotificationContainer',
  components: {
    BaseAlert,
    BaseButton
  },
  
  setup() {
    const notificationStore = useNotificationStore();
    
    // Computed
    const visibleNotifications = computed(() => notificationStore.notifications);
    
    // Methods
    const removeNotification = (id) => {
      notificationStore.removeNotification(id);
    };
    
    const handleAction = (action, notification) => {
      if (action.handler) {
        action.handler(notification);
      }
      
      // Auto-close notification after action
      if (action.closeAfterAction !== false) {
        removeNotification(notification.id);
      }
    };
    
    // Keyboard shortcuts
    const handleKeydown = (event) => {
      // Press Escape to clear all notifications
      if (event.key === 'Escape') {
        notificationStore.clearAll();
      }
    };
    
    // Lifecycle
    onMounted(() => {
      document.addEventListener('keydown', handleKeydown);
    });
    
    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeydown);
    });
    
    return {
      visibleNotifications,
      removeNotification,
      handleAction
    };
  }
};
</script>

<style scoped>
.notification-enter-active {
  transition: all 0.3s ease-out;
}

.notification-leave-active {
  transition: all 0.2s ease-in;
}

.notification-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.notification-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>