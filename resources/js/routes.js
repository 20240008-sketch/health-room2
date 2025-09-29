// Views/Pages
import Dashboard from './views/Dashboard.vue';
import StudentIndex from './views/students/StudentIndex.vue';
import StudentShow from './views/students/StudentShow.vue';
import StudentCreate from './views/students/StudentCreate.vue';
import StudentEdit from './views/students/StudentEdit.vue';
import ClassIndex from './views/classes/ClassIndex.vue';
import ClassShow from './views/classes/ClassShow.vue';
import HealthRecordIndex from './views/health-records/HealthRecordIndex.vue';
import HealthRecordCreate from './views/health-records/HealthRecordCreate.vue';
import HealthRecordEdit from './views/health-records/HealthRecordEdit.vue';

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { title: 'ダッシュボード' }
  },
  
  // 生徒管理
  {
    path: '/students',
    name: 'StudentIndex',
    component: StudentIndex,
    meta: { title: '生徒一覧' }
  },
  {
    path: '/students/create',
    name: 'StudentCreate',
    component: StudentCreate,
    meta: { title: '生徒新規登録' }
  },
  {
    path: '/students/:id',
    name: 'StudentShow',
    component: StudentShow,
    props: true,
    meta: { title: '生徒詳細' }
  },
  {
    path: '/students/:id/edit',
    name: 'StudentEdit',
    component: StudentEdit,
    props: true,
    meta: { title: '生徒編集' }
  },
  
  // クラス管理
  {
    path: '/classes',
    name: 'ClassIndex',
    component: ClassIndex,
    meta: { title: 'クラス一覧' }
  },
  {
    path: '/classes/:id',
    name: 'ClassShow',
    component: ClassShow,
    props: true,
    meta: { title: 'クラス詳細' }
  },
  
  // 健康記録
  {
    path: '/health-records',
    name: 'HealthRecordIndex',
    component: HealthRecordIndex,
    meta: { title: '健康記録一覧' }
  },
  {
    path: '/health-records/create',
    name: 'HealthRecordCreate',
    component: HealthRecordCreate,
    meta: { title: '健康記録新規登録' }
  },
  {
    path: '/health-records/:id/edit',
    name: 'HealthRecordEdit',
    component: HealthRecordEdit,
    props: true,
    meta: { title: '健康記録編集' }
  },
  
  // 統計情報
  {
    path: '/statistics',
    name: 'Statistics',
    component: Dashboard, // 後で統計専用コンポーネントに変更
    meta: { title: '統計情報' }
  },
  
  // 404 Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: Dashboard, // 後で404専用コンポーネントに変更
    meta: { title: 'ページが見つかりません' }
  }
];

export default routes;