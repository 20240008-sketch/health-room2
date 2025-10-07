import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotificationStore } from './notification';

export const useHealthRecordStore = defineStore('healthRecord', () => {
  const notificationStore = useNotificationStore();
  
  // State
  const healthRecords = ref([]);
  const recentRecords = ref([]);
  const currentRecord = ref(null);
  const loading = ref(false);
  const filters = ref({
    student_id: '',
    start_date: '',
    end_date: '',
    grade: ''
  });
  const pagination = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1
  });

  // 共通エラーハンドリング
  const handleApiError = (error, defaultMessage) => {
    console.error('API Error:', error);
    
    if (error.response) {
      const status = error.response.status;
      const data = error.response.data;
      
      switch (status) {
        case 422:
          // バリデーションエラー
          const errors = data.errors || {};
          const errorMessages = Object.values(errors).flat();
          notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
          break;
        case 404:
          notificationStore.showError('データが見つかりません');
          break;
        case 500:
          notificationStore.showError('サーバーエラーが発生しました');
          break;
        default:
          notificationStore.showError(data.message || defaultMessage);
      }
    } else if (error.request) {
      // ネットワークエラー
      notificationStore.showError('ネットワークエラーが発生しました');
    } else {
      // その他のエラー
      notificationStore.showError(defaultMessage);
    }
  };

  // Computed
  const filteredRecords = computed(() => {
    if (!Object.values(filters.value).some(Boolean)) {
      return healthRecords.value;
    }
    
    return healthRecords.value.filter(record => {
      const matchesStudent = !filters.value.student_id || record.student_id == filters.value.student_id;
      const matchesStartDate = !filters.value.start_date || record.record_date >= filters.value.start_date;
      const matchesEndDate = !filters.value.end_date || record.record_date <= filters.value.end_date;
      const matchesGrade = !filters.value.grade || (record.student && record.student.grade == filters.value.grade);
      
      return matchesStudent && matchesStartDate && matchesEndDate && matchesGrade;
    });
  });

  const recordsByMonth = computed(() => {
    const grouped = {};
    healthRecords.value.forEach(record => {
      const month = record.record_date.substring(0, 7); // YYYY-MM
      if (!grouped[month]) {
        grouped[month] = [];
      }
      grouped[month].push(record);
    });
    
    // 月順でソート
    Object.keys(grouped).forEach(month => {
      grouped[month].sort((a, b) => new Date(b.record_date) - new Date(a.record_date));
    });
    
    return grouped;
  });

  const latestRecordsByStudent = computed(() => {
    const latest = {};
    healthRecords.value.forEach(record => {
      if (!latest[record.student_id] || 
          new Date(record.record_date) > new Date(latest[record.student_id].record_date)) {
        latest[record.student_id] = record;
      }
    });
    return Object.values(latest);
  });

  // Actions
  const fetchHealthRecords = async (page = 1) => {
    loading.value = true;
    try {
      const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters.value
      };

      const response = await axios.get('/v1/health-records', { params });
      
      if (response.data.success) {
        // 新しいシンプルなレスポンス形式に対応
        healthRecords.value = response.data.data || [];
        
        // ページネーション情報を更新
        if (response.data.pagination) {
          pagination.value = {
            current_page: response.data.pagination.current_page,
            per_page: response.data.pagination.per_page,
            total: response.data.pagination.total,
            last_page: response.data.pagination.last_page
          };
        }
      }
    } catch (error) {
      console.error('健康記録一覧取得エラー:', error);
      notificationStore.showError('健康記録一覧の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchHealthRecord = async (id) => {
    loading.value = true;
    try {
      const response = await axios.get(`/v1/health-records/${id}`);
      
      if (response.data.success) {
        currentRecord.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      handleApiError(error, '健康記録詳細の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const fetchRecentRecords = async (limit = 10) => {
    loading.value = true;
    try {
      const response = await axios.get('/v1/health-records', {
        params: {
          per_page: limit,
          sort: 'created_at',
          order: 'desc'
        }
      });
      
      if (response.data.success) {
        recentRecords.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      handleApiError(error, '最近の健康記録の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const createHealthRecord = async (recordData) => {
    loading.value = true;
    try {
      const response = await axios.post('/v1/health-records', recordData);
      
      if (response.data.success) {
        notificationStore.showSuccess('健康記録を登録しました');
        await fetchHealthRecords(); // リストを更新
        return response.data.data;
      }
    } catch (error) {
      handleApiError(error, '健康記録の登録に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const updateHealthRecord = async (id, recordData) => {
    loading.value = true;
    try {
      const response = await axios.put(`/v1/health-records/${id}`, recordData);
      
      if (response.data.success) {
        notificationStore.showSuccess('健康記録を更新しました');
        await fetchHealthRecords(); // リストを更新
        currentRecord.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('健康記録更新エラー:', error);
      
      if (error.response && error.response.status === 422) {
        const errors = error.response.data.errors || {};
        const errorMessages = Object.values(errors).flat();
        notificationStore.showError(`入力エラー: ${errorMessages.join(', ')}`);
      } else {
        notificationStore.showError('健康記録の更新に失敗しました');
      }
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const deleteHealthRecord = async (id) => {
    loading.value = true;
    try {
      const response = await axios.delete(`/v1/health-records/${id}`);
      
      if (response.data.success) {
        notificationStore.showSuccess('健康記録を削除しました');
        await fetchHealthRecords(); // リストを更新
        return true;
      }
    } catch (error) {
      console.error('健康記録削除エラー:', error);
      notificationStore.showError('健康記録の削除に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  // 特定の生徒の健康記録履歴を取得
  const fetchStudentHealthHistory = async (studentId) => {
    try {
      const response = await axios.get('/v1/health-records', {
        params: { student_id: studentId }
      });
      
      if (response.data.success) {
        const records = response.data.data.data || response.data.data;
        return records.sort((a, b) => new Date(b.record_date) - new Date(a.record_date));
      }
      return [];
    } catch (error) {
      console.error('生徒健康記録履歴取得エラー:', error);
      notificationStore.showError('生徒の健康記録履歴の取得に失敗しました');
      return [];
    }
  };

  // 新機能: 特定学生の健康記録を全て取得
  const getHealthRecordsByStudent = async (studentId) => {
    try {
      const response = await axios.get(`/v1/students/${studentId}/health-records`);
      
      if (response.data.success) {
        const records = response.data.data;
        return records.map(enrichRecordWithBMI).sort((a, b) => new Date(b.measured_date) - new Date(a.measured_date));
      }
      return [];
    } catch (error) {
      handleApiError(error, '学生の健康記録の取得に失敗しました');
      return [];
    }
  };

  // 新機能: 一括健康記録作成
  const createBulkHealthRecords = async (recordsData) => {
    loading.value = true;
    try {
      const response = await axios.post('/v1/health-records/bulk', {
        records: recordsData
      });
      
      if (response.data.success) {
        notificationStore.showSuccess(`${recordsData.length}件の健康記録を一括登録しました`);
        await fetchHealthRecords(); // リストを更新
        return response.data.data;
      }
    } catch (error) {
      handleApiError(error, '健康記録の一括登録に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  // 新機能: 同級生比較データ取得
  const getPeerComparison = async (classId, academicYear) => {
    try {
      const response = await axios.get(`/v1/health-records/peer-comparison`, {
        params: {
          class_id: classId,
          year: academicYear
        }
      });
      
      if (response.data.success) {
        return response.data.data;
      }
      return null;
    } catch (error) {
      handleApiError(error, '同級生比較データの取得に失敗しました');
      return null;
    }
  };

  // 新機能: 健康記録統計取得
  const getHealthRecordStatistics = async (filters = {}) => {
    try {
      const response = await axios.get('/v1/health-records/statistics', {
        params: filters
      });
      
      if (response.data.success) {
        return response.data.data;
      }
      return null;
    } catch (error) {
      handleApiError(error, '健康記録統計の取得に失敗しました');
      return null;
    }
  };

  // 新機能: BMIトレンド分析
  const analyzeBMITrend = (records) => {
    if (records.length < 2) return null;
    
    const sortedRecords = [...records]
      .filter(record => record.height && record.weight)
      .sort((a, b) => new Date(a.measured_date) - new Date(b.measured_date))
      .map(enrichRecordWithBMI);
    
    if (sortedRecords.length < 2) return null;
    
    const trendData = [];
    for (let i = 1; i < sortedRecords.length; i++) {
      const current = sortedRecords[i];
      const previous = sortedRecords[i - 1];
      
      trendData.push({
        date: current.measured_date,
        bmi: current.bmi,
        bmi_change: Math.round((current.bmi - previous.bmi) * 10) / 10,
        height: current.height,
        weight: current.weight,
        height_change: Math.round((current.height - previous.height) * 10) / 10,
        weight_change: Math.round((current.weight - previous.weight) * 10) / 10,
        category: current.bmi_category
      });
    }
    
    return {
      records: trendData,
      total_bmi_change: trendData.length > 0 ? 
        Math.round((sortedRecords[sortedRecords.length - 1].bmi - sortedRecords[0].bmi) * 10) / 10 : 0,
      average_bmi: sortedRecords.reduce((sum, record) => sum + record.bmi, 0) / sortedRecords.length,
      trend_direction: trendData.length > 0 && trendData[trendData.length - 1].bmi_change > 0 ? 'increasing' : 
                       trendData.length > 0 && trendData[trendData.length - 1].bmi_change < 0 ? 'decreasing' : 'stable'
    };
  };

  // BMI計算
  const calculateBMI = (height, weight) => {
    if (!height || !weight || height <= 0 || weight <= 0) return null;
    const heightInM = height / 100;
    return Math.round((weight / (heightInM * heightInM)) * 10) / 10;
  };

  // BMIカテゴリ判定
  const getBMICategory = (bmi) => {
    if (!bmi) return '不明';
    if (bmi < 18.5) return '低体重';
    if (bmi < 25) return '普通体重';
    if (bmi < 30) return '肥満(1度)';
    return '肥満(2度以上)';
  };

  // 健康記録にBMI情報を追加
  const enrichRecordWithBMI = (record) => {
    const bmi = calculateBMI(record.height, record.weight);
    return {
      ...record,
      bmi,
      bmi_category: getBMICategory(bmi)
    };
  };

  // 成長傾向分析（簡易版）
  const analyzeGrowthTrend = (records) => {
    if (records.length < 2) return null;
    
    const sortedRecords = [...records].sort((a, b) => new Date(a.record_date) - new Date(b.record_date));
    const latest = sortedRecords[sortedRecords.length - 1];
    const previous = sortedRecords[sortedRecords.length - 2];
    
    const heightGrowth = latest.height - previous.height;
    const weightChange = latest.weight - previous.weight;
    
    return {
      period: `${previous.record_date} → ${latest.record_date}`,
      height_change: Math.round(heightGrowth * 10) / 10,
      weight_change: Math.round(weightChange * 10) / 10,
      bmi_change: Math.round((calculateBMI(latest.height, latest.weight) - calculateBMI(previous.height, previous.weight)) * 10) / 10
    };
  };

  // Utility functions
  const clearFilters = () => {
    filters.value = {
      student_id: '',
      start_date: '',
      end_date: '',
      grade: ''
    };
  };

  const setCurrentRecord = (record) => {
    currentRecord.value = record;
  };

  const setDateRangeFilter = (startDate, endDate) => {
    filters.value.start_date = startDate;
    filters.value.end_date = endDate;
  };

  return {
    // State
    healthRecords,
    recentRecords,
    currentRecord,
    loading,
    filters,
    pagination,
    
    // Computed
    filteredRecords,
    recordsByMonth,
    latestRecordsByStudent,
    
    // Actions
    fetchHealthRecords,
    fetchHealthRecord,
    fetchRecentRecords,
    createHealthRecord,
    updateHealthRecord,
    deleteHealthRecord,
    fetchStudentHealthHistory,
    getHealthRecordsByStudent,
    createBulkHealthRecords,
    getPeerComparison,
    getHealthRecordStatistics,
    
    // Utilities
    calculateBMI,
    getBMICategory,
    enrichRecordWithBMI,
    analyzeGrowthTrend,
    analyzeBMITrend,
    clearFilters,
    setCurrentRecord,
    setDateRangeFilter
  };
});