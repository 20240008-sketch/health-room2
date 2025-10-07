import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const isAuthenticated = ref(false);
  const isLoading = ref(false);

  function fetchCurrentUser() {
    return new Promise((resolve) => {
      try {
        isLoading.value = true;
        user.value = {
          id: 1,
          name: '管理者',
          email: 'admin@example.com'
        };
        isAuthenticated.value = true;
        resolve(user.value);
      } catch (error) {
        console.error('Failed to fetch user:', error);
        user.value = null;
        isAuthenticated.value = false;
        resolve(null);
      } finally {
        isLoading.value = false;
      }
    });
  }

  async function login(credentials) {
    try {
      isLoading.value = true;
      await fetchCurrentUser();
      return user.value;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  function logout() {
    user.value = null;
    isAuthenticated.value = false;
  }

  async function initialize() {
    try {
      await fetchCurrentUser();
    } catch (error) {
      console.error('Auth initialization failed:', error);
      user.value = null;
      isAuthenticated.value = false;
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    fetchCurrentUser,
    login,
    logout,
    initialize
  };
});
