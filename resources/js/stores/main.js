import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useStudentStore } from './student';
import { useClassStore } from './class';
import { useHealthRecordStore } from './healthRecord';
import { useStatisticsStore } from './statistics';
import { useNotificationStore } from './notification';

export const useMainStore = defineStore('main', () => {
  // 他のストアへの参照
  const studentStore = useStudentStore();
  const classStore = useClassStore();
  const healthRecordStore = useHealthRecordStore();
  const statisticsStore = useStatisticsStore();
  const notificationStore = useNotificationStore();

  // 全体的なアプリ状態
  const appLoading = ref(false);
  const initialized = ref(false);
  const currentView = ref('dashboard');
  const sidebarCollapsed = ref(false);

  // アプリケーション設定
  const appConfig = ref({
    schoolName: '健康管理システム',
    academicYear: new Date().getFullYear(),
    itemsPerPage: 20,
    dateFormat: 'YYYY-MM-DD',
    theme: 'light'
  });

  // Computed
  const isLoading = computed(() => {
    return appLoading.value || 
           studentStore.loading || 
           classStore.loading || 
           healthRecordStore.loading || 
           statisticsStore.loading;
  });

  const hasData = computed(() => {
    return studentStore.students.length > 0 || 
           classStore.classes.length > 0 || 
           healthRecordStore.healthRecords.length > 0;
  });

  const dashboardSummary = computed(() => {
    return {
      students: {
        total: studentStore.students.length,
        by_grade: studentStore.students.reduce((acc, student) => {
          acc[student.grade] = (acc[student.grade] || 0) + 1;
          return acc;
        }, {})
      },
      classes: {
        total: classStore.classes.length,
        by_grade: classStore.gradeOptions
      },
      health_records: {
        total: healthRecordStore.healthRecords.length,
        recent: healthRecordStore.healthRecords.filter(record => {
          const thirtyDaysAgo = new Date();
          thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
          return new Date(record.record_date) >= thirtyDaysAgo;
        }).length
      }
    };
  });

  // Actions
  const initializeApp = async () => {
    if (initialized.value) return;
    
    appLoading.value = true;
    try {
      // 並行してデータを取得
      await Promise.allSettled([
        classStore.fetchClasses(),
        studentStore.fetchStudents(),
        healthRecordStore.fetchHealthRecords(),
        statisticsStore.fetchDashboardStats()
      ]);

      initialized.value = true;
      notificationStore.showSuccess('アプリケーションが正常に初期化されました');
    } catch (error) {
      console.error('アプリケーション初期化エラー:', error);
      notificationStore.showError('アプリケーションの初期化に失敗しました');
    } finally {
      appLoading.value = false;
    }
  };

  const refreshAllData = async () => {
    appLoading.value = true;
    try {
      await Promise.all([
        studentStore.fetchStudents(),
        classStore.fetchClasses(),
        healthRecordStore.fetchHealthRecords(),
        statisticsStore.fetchAllStats()
      ]);
      
      notificationStore.showSuccess('データを更新しました');
    } catch (error) {
      console.error('データ更新エラー:', error);
      notificationStore.showError('データの更新に失敗しました');
    } finally {
      appLoading.value = false;
    }
  };

  // 学年情報を取得（1〜6年生）
  const getGradeOptions = () => {
    return [
      { value: 1, label: '1年生' },
      { value: 2, label: '2年生' },
      { value: 3, label: '3年生' },
      { value: 4, label: '4年生' },
      { value: 5, label: '5年生' },
      { value: 6, label: '6年生' }
    ];
  };

  // 性別選択肢
  const getGenderOptions = () => {
    return [
      { value: '男', label: '男' },
      { value: '女', label: '女' }
    ];
  };

  // 日付フォーマット関数
  const formatDate = (date, format = null) => {
    if (!date) return '';
    
    const d = new Date(date);
    const fmt = format || appConfig.value.dateFormat;
    
    switch (fmt) {
      case 'YYYY-MM-DD':
        return d.toISOString().split('T')[0];
      case 'YYYY/MM/DD':
        return `${d.getFullYear()}/${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}`;
      case 'MM/DD':
        return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}`;
      case 'Japanese':
        return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
      default:
        return d.toLocaleDateString('ja-JP');
    }
  };

  // 学年から年齢を計算（概算）
  const calculateAge = (grade, birthDate = null) => {
    if (birthDate) {
      const today = new Date();
      const birth = new Date(birthDate);
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      return age;
    }
    
    // 学年から概算年齢を計算（4月入学想定）
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    const baseAge = 6 + grade - 1; // 1年生は6-7歳
    
    // 4月以降は学年通りの年齢、3月以前は-1歳
    return currentMonth >= 4 ? baseAge : baseAge - 1;
  };

  // 検索とフィルタリングの共通関数
  const globalSearch = async (query, filters = {}) => {
    const results = {
      students: [],
      classes: [],
      healthRecords: []
    };

    try {
      // 学生検索
      if (query) {
        const suggestions = await studentStore.searchSuggestions(query);
        results.students = suggestions;
      }

      // フィルター適用
      if (filters.grade) {
        studentStore.filters.grade = filters.grade;
        classStore.filters.grade = filters.grade;
        healthRecordStore.filters.grade = filters.grade;
      }

      return results;
    } catch (error) {
      console.error('グローバル検索エラー:', error);
      notificationStore.showError('検索に失敗しました');
      return results;
    }
  };

  // ビューナビゲーション
  const navigateToView = (view) => {
    currentView.value = view;
  };

  // サイドバー制御
  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  };

  // 設定更新
  const updateConfig = (newConfig) => {
    appConfig.value = { ...appConfig.value, ...newConfig };
    
    // ローカルストレージに保存
    try {
      localStorage.setItem('app-config', JSON.stringify(appConfig.value));
    } catch (error) {
      console.warn('設定の保存に失敗しました:', error);
    }
  };

  // 設定読み込み
  const loadConfig = () => {
    try {
      const savedConfig = localStorage.getItem('app-config');
      if (savedConfig) {
        appConfig.value = { ...appConfig.value, ...JSON.parse(savedConfig) };
      }
    } catch (error) {
      console.warn('設定の読み込みに失敗しました:', error);
    }
  };

  // ログアウト（将来の認証機能用）
  const logout = () => {
    // 全ストアをクリア
    studentStore.$reset();
    classStore.$reset();
    healthRecordStore.$reset();
    statisticsStore.clearCache();
    
    initialized.value = false;
    notificationStore.showInfo('ログアウトしました');
  };

  return {
    // State
    appLoading,
    initialized,
    currentView,
    sidebarCollapsed,
    appConfig,
    
    // Computed
    isLoading,
    hasData,
    dashboardSummary,
    
    // Actions
    initializeApp,
    refreshAllData,
    getGradeOptions,
    getGenderOptions,
    formatDate,
    calculateAge,
    globalSearch,
    navigateToView,
    toggleSidebar,
    updateConfig,
    loadConfig,
    logout
  };
});