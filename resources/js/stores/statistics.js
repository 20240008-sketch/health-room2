import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotificationStore } from './notification';

export const useStatisticsStore = defineStore('statistics', () => {
  const notificationStore = useNotificationStore();
  
  // State
  const systemStats = ref(null);
  const gradeStats = ref({});
  const classStats = ref({});
  const healthStats = ref(null);
  const loading = ref(false);
  const lastUpdated = ref(null);

  // Computed
  const totalStudents = computed(() => {
    return systemStats.value?.students?.total || 0;
  });

  const totalClasses = computed(() => {
    return systemStats.value?.classes?.total || 0;
  });

  const totalHealthRecords = computed(() => {
    return systemStats.value?.health_records?.total || 0;
  });

  const recentHealthRecords = computed(() => {
    return systemStats.value?.health_records?.recent || 0;
  });

  const studentsByGrade = computed(() => {
    return systemStats.value?.students?.by_grade || {};
  });

  const classesByGrade = computed(() => {
    return systemStats.value?.classes?.by_grade || {};
  });

  const healthRecordsTrend = computed(() => {
    return systemStats.value?.health_records?.by_month || [];
  });

  const bmiDistribution = computed(() => {
    if (!healthStats.value?.bmi_distribution) {
      return {
        underweight: 0,
        normal: 0,
        overweight: 0,
        obese: 0
      };
    }
    return healthStats.value.bmi_distribution;
  });

  const dashboardStats = computed(() => {
    if (!systemStats.value) {
      return {
        totalStudents: 0,
        totalClasses: 0,
        totalHealthRecords: 0,
        recentHealthRecords: 0
      };
    }
    
    return {
      totalStudents: systemStats.value.students?.total || 0,
      totalClasses: systemStats.value.classes?.total || 0,
      totalHealthRecords: systemStats.value.health_records?.total || 0,
      recentHealthRecords: systemStats.value.health_records?.recent || 0
    };
  });

  // Actions
  const fetchSystemStats = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/v1/statistics/system');
      
      if (response.data.success) {
        systemStats.value = response.data.data;
        lastUpdated.value = new Date();
      }
    } catch (error) {
      console.error('システム統計取得エラー:', error);
      notificationStore.showError('システム統計の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchGradeStats = async (grade) => {
    loading.value = true;
    try {
      const response = await axios.get('/v1/statistics/grade', {
        params: { grade }
      });
      
      if (response.data.success) {
        gradeStats.value[grade] = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('学年別統計取得エラー:', error);
      notificationStore.showError(`${grade}年生の統計取得に失敗しました`);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const fetchClassStats = async (classId) => {
    loading.value = true;
    try {
      const response = await axios.get(`/v1/statistics/class/${classId}`);
      
      if (response.data.success) {
        classStats.value[classId] = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('クラス別統計取得エラー:', error);
      notificationStore.showError('クラス統計の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const fetchHealthStats = async (startDate = null, endDate = null) => {
    loading.value = true;
    try {
      const params = {};
      if (startDate) params.start_date = startDate;
      if (endDate) params.end_date = endDate;

      const response = await axios.get('/v1/statistics/health', { params });
      
      if (response.data.success) {
        healthStats.value = response.data.data;
        return response.data.data;
      }
    } catch (error) {
      console.error('健康記録統計取得エラー:', error);
      notificationStore.showError('健康記録統計の取得に失敗しました');
      throw error;
    } finally {
      loading.value = false;
    }
  };

  // 複数の統計を一括取得
  const fetchAllStats = async () => {
    try {
      await Promise.all([
        fetchSystemStats(),
        fetchHealthStats()
      ]);
    } catch (error) {
      console.error('統計一括取得エラー:', error);
    }
  };

  // ダッシュボード用サマリー統計
  const fetchDashboardStats = async () => {
    loading.value = true;
    try {
      const [systemResponse, healthResponse] = await Promise.allSettled([
        axios.get('/v1/statistics/system'),
        axios.get('/v1/statistics/health', {
          params: {
            start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] // 30日前
          }
        })
      ]);

      if (systemResponse.status === 'fulfilled' && systemResponse.value.data.success) {
        systemStats.value = systemResponse.value.data.data;
      }

      if (healthResponse.status === 'fulfilled' && healthResponse.value.data.success) {
        healthStats.value = healthResponse.value.data.data;
      }

      lastUpdated.value = new Date();
    } catch (error) {
      console.error('ダッシュボード統計取得エラー:', error);
      notificationStore.showError('ダッシュボード統計の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchBmiDistribution = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/v1/statistics/health', {
        params: {
          start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] // 30日前
        }
      });
      
      if (response.data.success) {
        healthStats.value = response.data.data;
      }
    } catch (error) {
      console.error('BMI分布取得エラー:', error);
      notificationStore.showError('BMI分布の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  // データの可視化用フォーマット
  const formatChartData = (data, type = 'bar') => {
    if (!data) return null;

    switch (type) {
      case 'pie':
        return {
          labels: Object.keys(data),
          datasets: [{
            data: Object.values(data),
            backgroundColor: [
              '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
              '#8B5CF6', '#EC4899', '#6B7280', '#84CC16'
            ]
          }]
        };

      case 'line':
        return {
          labels: data.map(item => item.period || item.month),
          datasets: [{
            label: 'データ',
            data: data.map(item => item.count || item.value),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
          }]
        };

      case 'bar':
      default:
        return {
          labels: Object.keys(data),
          datasets: [{
            label: 'データ',
            data: Object.values(data),
            backgroundColor: 'rgba(59, 130, 246, 0.6)',
            borderColor: '#3B82F6',
            borderWidth: 1
          }]
        };
    }
  };

  // 統計データのCSVエクスポート
  const exportStatsToCSV = (statsData, filename = 'statistics.csv') => {
    if (!statsData) return;

    try {
      const csvContent = convertToCSV(statsData);
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      
      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
      
      notificationStore.showSuccess('統計データをエクスポートしました');
    } catch (error) {
      console.error('CSVエクスポートエラー:', error);
      notificationStore.showError('CSVエクスポートに失敗しました');
    }
  };

  // オブジェクトをCSV形式に変換
  const convertToCSV = (data) => {
    const headers = Object.keys(data);
    const rows = [headers.join(',')];
    
    if (typeof data === 'object' && !Array.isArray(data)) {
      // オブジェクト形式の場合
      const values = Object.values(data).map(value => 
        typeof value === 'object' ? JSON.stringify(value) : value
      );
      rows.push(values.join(','));
    } else if (Array.isArray(data)) {
      // 配列形式の場合
      data.forEach(item => {
        const values = headers.map(header => 
          item[header] !== undefined ? item[header] : ''
        );
        rows.push(values.join(','));
      });
    }
    
    return rows.join('\n');
  };

  // キャッシュクリア
  const clearCache = () => {
    systemStats.value = null;
    gradeStats.value = {};
    classStats.value = {};
    healthStats.value = null;
    lastUpdated.value = null;
  };

  // 統計更新が必要かチェック（5分間キャッシュ）
  const needsUpdate = computed(() => {
    if (!lastUpdated.value) return true;
    const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
    return lastUpdated.value < fiveMinutesAgo;
  });

  return {
    // State
    systemStats,
    gradeStats,
    classStats,
    healthStats,
    loading,
    lastUpdated,
    
    // Computed
    totalStudents,
    totalClasses,
    totalHealthRecords,
    recentHealthRecords,
    studentsByGrade,
    classesByGrade,
    healthRecordsTrend,
    bmiDistribution,
    dashboardStats,
    needsUpdate,
    
    // Actions
    fetchSystemStats,
    fetchGradeStats,
    fetchClassStats,
    fetchHealthStats,
    fetchAllStats,
    fetchDashboardStats,
    fetchBmiDistribution,
    formatChartData,
    exportStatsToCSV,
    clearCache
  };
});