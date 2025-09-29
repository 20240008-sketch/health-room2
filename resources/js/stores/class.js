import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotificationStore } from './notification';

export const useClassStore = defineStore('class', () => {
  const notificationStore = useNotificationStore();
  
  // State
  const classes = ref([]);
  const currentClass = ref(null);
  const loading = ref(false);
  const filters = ref({
    grade: '',
    teacher_name: ''
  });

  // Computed
  const classesByGrade = computed(() => {
    const grouped = {};
    classes.value.forEach(cls => {
      if (!grouped[cls.grade]) {
        grouped[cls.grade] = [];
      }
      grouped[cls.grade].push(cls);
    });
    
    // 学年順にソート
    Object.keys(grouped).forEach(grade => {
      grouped[grade].sort((a, b) => a.name.localeCompare(b.name, 'ja'));
    });
    
    return grouped;
  });

  const filteredClasses = computed(() => {
    if (!filters.value.grade && !filters.value.teacher_name) {
      return classes.value;
    }
    
    return classes.value.filter(cls => {
      const matchesGrade = !filters.value.grade || cls.grade == filters.value.grade;
      const matchesTeacher = !filters.value.teacher_name || 
        cls.teacher_name.includes(filters.value.teacher_name);
      
      return matchesGrade && matchesTeacher;
    });
  });

  const gradeOptions = computed(() => {
    const grades = [...new Set(classes.value.map(cls => cls.grade))];
    return grades.sort((a, b) => a - b);
  });

  // Actions
  const fetchClasses = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/v1/classes');
      
      if (response.data.success) {
        classes.value = response.data.data;
      }
    } catch (error) {
      console.error('クラス一覧取得エラー:', error);
      notificationStore.showError('クラス一覧の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchClass = async (id) => {
    loading.value = true;
    try {
      const response = await axios.get(`/v1/classes/${id}`);
      
      if (response.data.success) {
        currentClass.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('クラス詳細取得エラー:', error);
      notificationStore.showError('クラス詳細の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const createClass = async (classData) => {
    loading.value = true;
    try {
      const response = await axios.post('/v1/classes', classData);
      
      if (response.data.success) {
        notificationStore.showSuccess('クラスを作成しました');
        await fetchClasses(); // リストを更新
        return response.data.data;
      }
    } catch (error) {
      console.error('クラス作成エラー:', error);
      
      if (error.response && error.response.status === 422) {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat();
        notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
      } else {
        notificationStore.showError('クラスの作成に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const updateClass = async (id, classData) => {
    loading.value = true;
    try {
      const response = await axios.put(`/v1/classes/${id}`, classData);
      
      if (response.data.success) {
        notificationStore.showSuccess('クラス情報を更新しました');
        await fetchClasses(); // リストを更新
        currentClass.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('クラス更新エラー:', error);
      
      if (error.response && error.response.status === 422) {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat();
        notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
      } else {
        notificationStore.showError('クラス情報の更新に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const deleteClass = async (id) => {
    loading.value = true;
    try {
      const response = await axios.delete(`/v1/classes/${id}`);
      
      if (response.data.success) {
        notificationStore.showSuccess('クラスを削除しました');
        await fetchClasses(); // リストを更新
        return true;
      }
    } catch (error) {
      console.error('クラス削除エラー:', error);
      
      if (error.response && error.response.status === 409) {
        notificationStore.showError('このクラスには生徒が在籍しているため削除できません');
      } else {
        notificationStore.showError('クラスの削除に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  // クラスに所属する生徒一覧を取得
  const fetchClassStudents = async (classId) => {
    try {
      const response = await axios.get(`/v1/students`, {
        params: { class_id: classId }
      });
      
      if (response.data.success) {
        return response.data.data;
      }
      return [];
    } catch (error) {
      console.error('クラス生徒一覧取得エラー:', error);
      notificationStore.showError('クラス生徒一覧の取得に失敗しました');
      return [];
    }
  };

  // 学年別のクラス選択肢を取得
  const getClassOptionsByGrade = (grade) => {
    return classes.value
      .filter(cls => cls.grade == grade)
      .map(cls => ({
        value: cls.id,
        label: cls.name,
        teacher: cls.teacher_name
      }))
      .sort((a, b) => a.label.localeCompare(b.label, 'ja'));
  };

  // Utility functions
  const clearFilters = () => {
    filters.value = {
      grade: '',
      teacher_name: ''
    };
  };

  const setCurrentClass = (cls) => {
    currentClass.value = cls;
  };

  // 初期化時にクラス一覧を取得
  const initialize = async () => {
    if (classes.value.length === 0) {
      await fetchClasses();
    }
  };

  return {
    // State
    classes,
    currentClass,
    loading,
    filters,
    
    // Computed
    classesByGrade,
    filteredClasses,
    gradeOptions,
    
    // Actions
    fetchClasses,
    fetchClass,
    createClass,
    updateClass,
    deleteClass,
    fetchClassStudents,
    getClassOptionsByGrade,
    clearFilters,
    setCurrentClass,
    initialize
  };
});