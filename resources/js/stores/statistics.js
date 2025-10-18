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
  const gradeAverages = ref([]);
  const classAverages = ref([]);
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
        overweight: 0
      };
    }
    
    return healthStats.value.bmi_distribution;
  });

  // Actions
  const fetchSystemStats = async () => {
    try {
      loading.value = true;
      console.log('statisticsStore: fetchSystemStats 開始');
      const response = await axios.get('/api/v1/statistics/system');
      
      console.log('statisticsStore: fetchSystemStats レスポンス', response.data);
      
      if (response.data.success) {
        systemStats.value = response.data.data;
        lastUpdated.value = new Date();
        console.log('statisticsStore: systemStats updated', systemStats.value);
      }
    } catch (error) {
      console.error('システム統計取得エラー:', error);
      console.error('エラー詳細:', error.response?.data);
      notificationStore.showError('システム統計の取得に失敗しました');
    } finally {
      loading.value = false;
    }
  };

  const fetchBmiDistribution = async () => {
    try {
      const response = await axios.get('/api/v1/statistics/health-records');
      
      if (response.data.success) {
        healthStats.value = response.data.data;
      }
    } catch (error) {
      console.error('BMI分布取得エラー:', error);
      notificationStore.showError('BMI分布の取得に失敗しました');
    }
  };

  const fetchGradeAverages = async () => {
    try {
      const response = await axios.get('/api/v1/statistics/grade-averages');
      
      if (response.data.success) {
        gradeAverages.value = response.data.data.map(grade => ({
          ...grade,
          avg_height: grade.avg_height ? parseFloat(grade.avg_height).toFixed(1) : '---',
          avg_weight: grade.avg_weight ? parseFloat(grade.avg_weight).toFixed(1) : '---',
          avg_bmi: grade.avg_bmi ? parseFloat(grade.avg_bmi).toFixed(1) : '---',
          avg_vision: grade.avg_vision ? parseFloat(grade.avg_vision).toFixed(1) : null
        }));
      }
    } catch (error) {
      console.error('学年平均取得エラー:', error);
      gradeAverages.value = [];
    }
  };

  const fetchClassAverages = async () => {
    try {
      const response = await axios.get('/api/v1/statistics/class-averages');
      
      if (response.data.success) {
        classAverages.value = response.data.data.map(cls => ({
          ...cls,
          avg_height: cls.avg_height ? parseFloat(cls.avg_height).toFixed(1) : '---',
          avg_weight: cls.avg_weight ? parseFloat(cls.avg_weight).toFixed(1) : '---',
          avg_bmi: cls.avg_bmi ? parseFloat(cls.avg_bmi).toFixed(1) : '---',
          avg_vision: cls.avg_vision ? parseFloat(cls.avg_vision).toFixed(1) : null
        }));
      }
    } catch (error) {
      console.error('クラス平均取得エラー:', error);
      classAverages.value = [];
    }
  };

  const refreshAll = async () => {
    await Promise.all([
      fetchSystemStats(),
      fetchBmiDistribution(),
      fetchGradeAverages(),
      fetchClassAverages()
    ]);
  };

  return {
    // State
    systemStats,
    gradeStats,
    classStats,
    healthStats,
    gradeAverages,
    classAverages,
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
    
    // Actions
    fetchSystemStats,
    fetchBmiDistribution,
    fetchGradeAverages,
    fetchClassAverages,
    refreshAll
  };
});
