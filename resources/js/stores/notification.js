import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
  const notification = ref({
    show: false,
    type: 'info', // success, error, info, warning
    message: '',
    timeout: null
  });

  const showNotification = (type, message, duration = 5000) => {
    // 既存の通知がある場合はクリア
    if (notification.value.timeout) {
      clearTimeout(notification.value.timeout);
    }

    notification.value = {
      show: true,
      type,
      message,
      timeout: setTimeout(() => {
        hideNotification();
      }, duration)
    };
  };

  const hideNotification = () => {
    notification.value.show = false;
    if (notification.value.timeout) {
      clearTimeout(notification.value.timeout);
      notification.value.timeout = null;
    }
  };

  // 便利なメソッド
  const showSuccess = (message, duration = 5000) => {
    showNotification('success', message, duration);
  };

  const showError = (message, duration = 7000) => {
    showNotification('error', message, duration);
  };

  const showInfo = (message, duration = 5000) => {
    showNotification('info', message, duration);
  };

  const showWarning = (message, duration = 6000) => {
    showNotification('warning', message, duration);
  };

  return {
    notification,
    showNotification,
    hideNotification,
    showSuccess,
    showError,
    showInfo,
    showWarning
  };
});