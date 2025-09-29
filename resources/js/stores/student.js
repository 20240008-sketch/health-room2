import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotificationStore } from './notification';

export const useStudentStore = defineStore('student', () => {
  const notificationStore = useNotificationStore();
  
  // State
  const students = ref([]);
  const currentStudent = ref(null);
  const loading = ref(false);
  const searchQuery = ref('');
  const filters = ref({
    grade: '',
    class_id: '',
    gender: ''
  });
  const pagination = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1
  });

  // Computed
  const filteredStudents = computed(() => {
    if (!searchQuery.value && !Object.values(filters.value).some(Boolean)) {
      return students.value;
    }
    
    return students.value.filter(student => {
      const matchesSearch = !searchQuery.value || 
        student.name.includes(searchQuery.value) ||
        student.name_kana.includes(searchQuery.value) ||
        student.student_number.includes(searchQuery.value);
      
      const matchesGrade = !filters.value.grade || student.grade == filters.value.grade;
      const matchesClass = !filters.value.class_id || student.school_class_id == filters.value.class_id;
      const matchesGender = !filters.value.gender || student.gender === filters.value.gender;
      
      return matchesSearch && matchesGrade && matchesClass && matchesGender;
    });
  });

  // Actions
  const fetchStudents = async (params = {}) => {
    loading.value = true;
    try {
      // If params is a number (old API), convert to object
      if (typeof params === 'number') {
        params = { page: params };
      }
      
      const requestParams = {
        page: params.page || 1,
        per_page: pagination.value.per_page,
        ...params
      };

      console.log('Fetching students with params:', requestParams); // Debug log
      const response = await axios.get('/v1/students', { params: requestParams });
      
      console.log('API Response:', response.data); // Debug log
      
      if (response.data.success) {
        // レスポンスデータの構造に応じて適切に処理
        if (response.data.data.data) {
          // ページネーション付きレスポンス
          students.value = response.data.data.data;
          pagination.value = {
            current_page: response.data.data.current_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
            last_page: response.data.data.last_page
          };
        } else {
          // 単純な配列レスポンス
          students.value = response.data.data;
        }
        console.log('Students loaded:', students.value.length); // Debug log
        console.log('First student:', students.value[0]); // Debug log
      }
    } catch (error) {
      console.error('生徒一覧取得エラー:', error);
      console.error('Error response:', error.response?.data);
      console.error('Error status:', error.response?.status);
      notificationStore.showError('生徒一覧の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchStudent = async (id) => {
    loading.value = true;
    try {
      const response = await axios.get(`/v1/students/${id}`);
      
      if (response.data.success) {
        currentStudent.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('生徒詳細取得エラー:', error);
      notificationStore.showError('生徒詳細の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const createStudent = async (studentData) => {
    loading.value = true;
    try {
      const response = await axios.post('/v1/students', studentData);
      
      if (response.data.success) {
        notificationStore.showSuccess('生徒を登録しました');
        await fetchStudents(); // リストを更新
        return response.data.data;
      }
    } catch (error) {
      console.error('生徒登録エラー:', error);
      
      if (error.response && error.response.status === 422) {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat();
        notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
      } else {
        notificationStore.showError('生徒の登録に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const updateStudent = async (id, studentData) => {
    loading.value = true;
    try {
      const response = await axios.put(`/v1/students/${id}`, studentData);
      
      if (response.data.success) {
        notificationStore.showSuccess('生徒情報を更新しました');
        await fetchStudents(); // リストを更新
        currentStudent.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('生徒更新エラー:', error);
      
      if (error.response && error.response.status === 422) {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat();
        notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
      } else {
        notificationStore.showError('生徒情報の更新に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const deleteStudent = async (id) => {
    loading.value = true;
    try {
      const response = await axios.delete(`/v1/students/${id}`);
      
      if (response.data.success) {
        notificationStore.showSuccess('生徒を削除しました');
        await fetchStudents(); // リストを更新
        return true;
      }
    } catch (error) {
      console.error('生徒削除エラー:', error);
      notificationStore.showError('生徒の削除に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const searchSuggestions = async (query, limit = 10) => {
    try {
      const response = await axios.get('/v1/students/search/suggestions', {
        params: { query, limit }
      });
      
      if (response.data.success) {
        return response.data.data;
      }
      return [];
    } catch (error) {
      console.error('検索候補取得エラー:', error);
      return [];
    }
  };

  const exportData = async (searchParams = {}) => {
    try {
      const response = await axios.get('/v1/students/export/data', {
        params: { ...filters.value, search: searchQuery.value, ...searchParams }
      });
      
      if (response.data.success) {
        return response.data.data;
      }
      return [];
    } catch (error) {
      console.error('エクスポートデータ取得エラー:', error);
      notificationStore.showError('エクスポートデータの取得に失敗しました');
      return [];
    }
  };

  // Utility functions
  const clearFilters = () => {
    searchQuery.value = '';
    filters.value = {
      grade: '',
      class_id: '',
      gender: ''
    };
  };

  const setCurrentStudent = (student) => {
    currentStudent.value = student;
  };

  return {
    // State
    students,
    currentStudent,
    loading,
    searchQuery,
    filters,
    pagination,
    
    // Computed
    filteredStudents,
    
    // Actions
    fetchStudents,
    fetchStudent,
    createStudent,
    updateStudent,
    deleteStudent,
    searchSuggestions,
    exportData,
    clearFilters,
    setCurrentStudent
  };
});