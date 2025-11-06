<template>
  <AppLayout>
    <!-- Page Header -->
    <template #header>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            養護日誌
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            保健室の執務記録
          </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
          <BaseButton
            variant="secondary"
            @click="$router.push('/')"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            戻る
          </BaseButton>
          <BaseButton
            variant="secondary"
            @click="downloadPdf"
          >
            <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
            PDF出力
          </BaseButton>
          <BaseButton
            variant="secondary"
            @click="printDiary"
          >
            <PrinterIcon class="h-4 w-4 mr-2" />
            印刷
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="saveDiary"
          >
            <DocumentCheckIcon class="h-4 w-4 mr-2" />
            保存
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-6">
      <!-- Date Selection -->
      <BaseCard>
        <div class="flex items-center space-x-4">
          <BaseInput
            type="date"
            v-model="diary.date"
            label="日付"
            @change="loadDiary"
          />
          <div class="flex-1 text-center text-lg font-medium text-gray-700 pt-6">
            {{ getDayOfWeek(diary.date) }}曜日
          </div>
        </div>
      </BaseCard>

      <!-- Nursing Diary Form -->
      <BaseCard>
        <div class="nursing-diary">
          <!-- Header Section -->
          <div class="diary-header border-2 border-gray-800 p-4 mb-4">
            <div class="text-center mb-4">
              <h2 class="text-2xl font-bold">養護日誌</h2>
            </div>
            
            <div class="grid grid-cols-12 gap-2 text-sm">
              <!-- Date Info -->
              <div class="col-span-6 flex items-center space-x-2">
                <span class="font-medium">令和</span>
                <input type="text" v-model="diary.year" class="border border-gray-300 px-2 py-1 w-16 text-center" />
                <span>年</span>
                <input type="text" v-model="diary.month" class="border border-gray-300 px-2 py-1 w-12 text-center" />
                <span>月</span>
                <input type="text" v-model="diary.day" class="border border-gray-300 px-2 py-1 w-12 text-center" />
                <span>日</span>
                <span class="font-medium">({{ getDayOfWeek(diary.date) }})</span>
              </div>
              
              <!-- Weather and Temperature -->
              <div class="col-span-6 grid grid-cols-2 gap-2">
                <div class="flex items-center space-x-2">
                  <span class="font-medium">天候</span>
                  <input type="text" v-model="diary.weather" class="border border-gray-300 px-2 py-1 flex-1" placeholder="晴" />
                </div>
                <div class="flex items-center space-x-2">
                  <span class="font-medium">温度</span>
                  <input type="text" v-model="diary.temperature" class="border border-gray-300 px-2 py-1 w-16 text-center" />
                  <span>度</span>
                </div>
              </div>
              
              <!-- Humidity and Stamps -->
              <div class="col-span-12 grid grid-cols-2 gap-4 mt-2">
                <div class="flex items-center space-x-2">
                  <span class="font-medium">湿度</span>
                  <input type="text" v-model="diary.humidity" class="border border-gray-300 px-2 py-1 w-16 text-center" />
                  <span>％</span>
                </div>
                <div class="flex items-center space-x-4 text-xs">
                  <span>校長印</span>
                  <span>副校長印</span>
                  <span>教頭印</span>
                  <span>記入者印</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Absence Survey Section -->
          <div class="border-2 border-gray-800 mb-4">
            <div class="bg-gray-100 border-b-2 border-gray-800 px-4 py-2 font-bold text-center">
              欠席調査
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-800">
                  <th class="border-r border-gray-800 px-2 py-1" rowspan="2"></th>
                  <th class="border-r border-gray-800 px-2 py-1" colspan="6">学年別</th>
                  <th class="border-r border-gray-800 px-2 py-1" rowspan="2">計</th>
                </tr>
                <tr class="border-b border-gray-800">
                  <th class="border-r border-gray-800 px-2 py-1 w-16">１年</th>
                  <th class="border-r border-gray-800 px-2 py-1 w-16">２年</th>
                  <th class="border-r border-gray-800 px-2 py-1 w-16">３年</th>
                  <th class="border-r border-gray-800 px-2 py-1 w-16">４年</th>
                  <th class="border-r border-gray-800 px-2 py-1 w-16">５年</th>
                  <th class="border-r border-gray-800 px-2 py-1 w-16">６年</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b border-gray-800">
                  <td class="border-r border-gray-800 px-2 py-1 font-medium">欠席</td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade1" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade2" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade3" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade4" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade5" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.absent.grade6" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1 font-bold text-center">{{ absenceTotal('absent') }}</td>
                </tr>
                <tr class="border-b border-gray-800">
                  <td class="border-r border-gray-800 px-2 py-1 font-medium">病欠</td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade1" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade2" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade3" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade4" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade5" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.sick.grade6" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1 font-bold text-center">{{ absenceTotal('sick') }}</td>
                </tr>
                <tr class="border-b border-gray-800">
                  <td class="border-r border-gray-800 px-2 py-1 font-medium">事故欠</td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade1" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade2" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade3" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade4" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade5" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.accident.grade6" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1 font-bold text-center">{{ absenceTotal('accident') }}</td>
                </tr>
                <tr class="border-b border-gray-800">
                  <td class="border-r border-gray-800 px-2 py-1 font-medium">出停</td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade1" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade2" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade3" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade4" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade5" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.suspension.grade6" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1 font-bold text-center">{{ absenceTotal('suspension') }}</td>
                </tr>
                <tr class="border-b border-gray-800">
                  <td class="border-r border-gray-800 px-2 py-1 font-medium">忌引</td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade1" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade2" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade3" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade4" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade5" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1"><input type="number" v-model.number="absence.mourning.grade6" class="w-full text-center border-0 bg-transparent" /></td>
                  <td class="border-r border-gray-800 px-2 py-1 font-bold text-center">{{ absenceTotal('mourning') }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- School Events and Health Events -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="border-2 border-gray-800">
              <div class="bg-gray-100 border-b border-gray-800 px-4 py-2 font-bold text-center">
                学校行事・保健行事
              </div>
              <textarea
                v-model="diary.schoolEvents"
                class="w-full px-4 py-2 border-0 resize-none"
                rows="4"
                placeholder="学校行事や保健行事を記入してください"
              ></textarea>
            </div>
            
            <div class="border-2 border-gray-800">
              <div class="bg-gray-100 border-b border-gray-800 px-4 py-2 font-bold text-center">
                水質検査・残留塩素
              </div>
              <div class="px-4 py-2">
                <div class="space-y-2">
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium w-24">水質検査:</span>
                    <input type="text" v-model="diary.waterQuality" class="flex-1 border border-gray-300 px-2 py-1" />
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium w-24">残留塩素:</span>
                    <input type="text" v-model="diary.chlorine" class="flex-1 border border-gray-300 px-2 py-1" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Health Room Visits -->
          <div class="border-2 border-gray-800 mb-4">
            <div class="bg-gray-100 border-b border-gray-800 px-4 py-2 font-bold text-center">
              保健室来室記録
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-xs">
                <thead>
                  <tr class="border-b border-gray-800 bg-gray-50">
                    <th class="border-r border-gray-800 px-2 py-1 w-12">時間</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-16">年</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-16">組</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-16">番</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-32">氏名</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-12">性</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-20">分類</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-24">種別</th>
                    <th class="border-r border-gray-800 px-2 py-1 w-24">発生時</th>
                    <th class="border-r border-gray-800 px-2 py-1">処置・原因・備考</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(visit, index) in visits" :key="index" class="border-b border-gray-300">
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.time" class="w-full text-center border-0 bg-transparent text-xs" placeholder="10:30" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.grade" class="w-full text-center border-0 bg-transparent text-xs" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.class" class="w-full text-center border-0 bg-transparent text-xs" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.number" class="w-full text-center border-0 bg-transparent text-xs" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.name" class="w-full border-0 bg-transparent text-xs" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.gender" class="w-full text-center border-0 bg-transparent text-xs" maxlength="1" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.category" class="w-full text-center border-0 bg-transparent text-xs" placeholder="内科" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.type" class="w-full border-0 bg-transparent text-xs" placeholder="けが" />
                    </td>
                    <td class="border-r border-gray-300 px-1 py-1">
                      <input type="text" v-model="visit.occurrence" class="w-full border-0 bg-transparent text-xs" placeholder="休み時間" />
                    </td>
                    <td class="px-1 py-1">
                      <input type="text" v-model="visit.treatment" class="w-full border-0 bg-transparent text-xs" placeholder="体温37.2度、冷却、休養後下校" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="p-2 text-center">
              <BaseButton variant="secondary" size="sm" @click="addVisit">
                <PlusIcon class="h-3 w-3 mr-1" />
                来室記録追加
              </BaseButton>
            </div>
          </div>

          <!-- Diary and Notes -->
          <div class="border-2 border-gray-800">
            <div class="bg-gray-100 border-b border-gray-800 px-4 py-2 font-bold text-center">
              執務日記及び特記事項
            </div>
            <textarea
              v-model="diary.notes"
              class="w-full px-4 py-2 border-0 resize-none"
              rows="6"
              placeholder="執務内容や特記事項を記入してください"
            ></textarea>
          </div>
        </div>
      </BaseCard>
    </div>
  </AppLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useNotificationStore } from '@/stores/notification.js';
import {
  AppLayout,
  BaseCard,
  BaseInput,
  BaseButton
} from '@/components/index.js';

// Icons
const ArrowLeftIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
  `
};

const PlusIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
  `
};

const DocumentArrowDownIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
    </svg>
  `
};

const PrinterIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/>
    </svg>
  `
};

const DocumentCheckIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
};

export default {
  name: 'AttendanceIndex',
  components: {
    AppLayout,
    BaseCard,
    BaseInput,
    BaseButton,
    ArrowLeftIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    PrinterIcon,
    DocumentCheckIcon
  },
  
  setup() {
    const notificationStore = useNotificationStore();
    
    // Diary data
    const diary = ref({
      date: new Date().toISOString().split('T')[0],
      year: new Date().getFullYear() - 2018, // 令和年
      month: new Date().getMonth() + 1,
      day: new Date().getDate(),
      weather: '',
      temperature: '',
      humidity: '',
      schoolEvents: '',
      waterQuality: '',
      chlorine: '',
      notes: ''
    });

    // Absence data
    const absence = ref({
      absent: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
      sick: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
      accident: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
      suspension: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
      mourning: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 }
    });

    // Health room visits
    const visits = ref([
      { time: '', grade: '', class: '', number: '', name: '', gender: '', category: '', type: '', occurrence: '', treatment: '' },
      { time: '', grade: '', class: '', number: '', name: '', gender: '', category: '', type: '', occurrence: '', treatment: '' },
      { time: '', grade: '', class: '', number: '', name: '', gender: '', category: '', type: '', occurrence: '', treatment: '' },
      { time: '', grade: '', class: '', number: '', name: '', gender: '', category: '', type: '', occurrence: '', treatment: '' },
      { time: '', grade: '', class: '', number: '', name: '', gender: '', category: '', type: '', occurrence: '', treatment: '' }
    ]);

    // Methods
    const getDayOfWeek = (dateStr) => {
      const days = ['日', '月', '火', '水', '木', '金', '土'];
      const date = new Date(dateStr);
      return days[date.getDay()];
    };

    const absenceTotal = (type) => {
      const data = absence.value[type];
      return Object.values(data).reduce((sum, val) => sum + (val || 0), 0);
    };

    const getTypeLabel = (typeDetail) => {
      const typeLabels = {
        // 内科
        'headache': '頭痛',
        'stomachache': '腹痛',
        'stomach_pain': '胃痛',
        'cold': 'かぜ症状',
        'diarrhea': '下痢',
        'nausea': '吐き気・嘔吐',
        'constipation': '便秘',
        'menstrual_pain': '月経痛',
        'toothache': '歯痛',
        'sleep_deprivation': '睡眠不足',
        'feeling_sick': '気分が悪い',
        'asthma': '喘息',
        'poor_health': '体調不良',
        'hyperventilation': '過呼吸',
        'fatigue': '倦怠感',
        'dizziness': 'めまい・貧血',
        'seizure': '発作',
        'fever': '発熱',
        'cough': '咳',
        // 外科
        'scrape': 'すり傷',
        'cut': '切傷',
        'stab': '刺傷',
        'bruise': '打撲・打ち身',
        'sprain': '捻挫',
        'finger_jam': '突き指',
        'muscle_pain': '筋肉痛',
        'nosebleed': '鼻出血',
        'eye_pain': '眼痛',
        'back_pain': '腰痛',
        'fracture': '骨折・脱臼',
        'hives': 'じんましん',
        'suppuration': '化膿',
        'ear_pain': '耳痛',
        'burn': '火傷',
        'insect_bite': '虫さされ',
        'nail_detachment': '爪剥離',
        'skin_condition': '皮むけ・皮膚疾患',
        'foot_pain': '足痛',
        'pain': '痛み',
        'concussion': '脳震盪',
        'wound_disinfection': '傷口消毒',
        'tooth_extraction': '抜歯・歯が欠ける',
        // その他
        'mental_counseling': 'こころ(相談)',
        'physical_counseling': 'からだ(相談)',
        'somehow': '何となく',
        'psychogenic': '心因性',
        'call_waiting': '呼び出し・待機',
        'measurement': '計測等',
        'infirmary_attendance': '保健室登校',
        'other_counseling': 'その他の相談',
        'safe_place': '居場所',
        'observation': '経過観察',
        'infirmary_exam': '保健室受験',
        'counseling': '相談',
        'rest': '休養',
        'other': 'その他',
        // 欠席（旧）
        'sick': '病欠',
        'accident': '事故欠',
        'suspension': '出停',
        'mourning': '忌引',
        'injury': '怪我',
        'family': '家庭の事情'
      };
      return typeLabels[typeDetail] || typeDetail;
    };

    const getAbsenceReasonLabel = (reason) => {
      const labels = {
        // 欠席
        'headache': '頭痛',
        'cold': 'かぜ症状',
        'stomachache': '腹痛',
        'diarrhea': '下痢',
        'fever': '発熱',
        'sleep_deprivation': '睡眠不足',
        'asthma': '喘息',
        'nausea': '吐き気・嘔吐',
        'nephritis': '腎炎',
        'injury': '外傷',
        'ent_disease': '耳鼻疾患',
        'influenza': 'インフルエンザ',
        'chickenpox': '水痘',
        'mumps': '耳下腺炎',
        'epidemic_keratitis': 'はやり目',
        'other_infectious': 'その他の伝染病',
        'accident': '事故欠',
        'unknown': '理由不明',
        'mourning': '忌引',
        'housework': '家事',
        'poor_health': '体調不良',
        'hospitalization': '入院',
        'truancy': '不登校',
        'hospital_visit': '通院',
        'psychogenic': '心因性',
        'laziness': '怠惰',
        'appendicitis': '虫垂炎',
        'covid19': 'コロナウイルス感染症',
        'orthostatic': '起立性調節障害',
        // 遅刻他
        'carelessness': '不注意',
        'truancy_tendency': '不登校傾向',
        'counseling_room': '相談室登校',
        'other': 'その他'
      };
      return labels[reason] || reason;
    };

    const getOccurrenceLabel = (occurrenceTime) => {
      const labels = {
        'break': '休み時間',
        'lunch': '昼休み',
        'cleaning': '掃除',
        'before_school': '始業前',
        'after_school': '放課後',
        'class_1': '1時間目',
        'class_2': '2時間目',
        'class_3': '3時間目',
        'class_4': '4時間目',
        'class_5': '5時間目',
        'class_6': '6時間目',
        'club': '部活',
        'event': '行事',
        'exam': '試験中',
        'supplementary': '補習',
        'extracurricular': '課外授業',
        'during_class': '授業中',
        'self_study': '自習中',
        'commute': '登下校中',
        'other': 'その他'
      };
      return labels[occurrenceTime] || occurrenceTime;
    };

    const getCategoryLabel = (category) => {
      const categoryLabels = {
        'internal': '内科',
        'surgical': '外科',
        'other': 'その他',
        'absence': '欠席',
        'late': '遅刻他'
      };
      return categoryLabels[category] || category;
    };

    // 詳細情報をフォーマットする関数
    const formatDetailedInfo = (record) => {
      const details = [];
      
      // 内科関連情報
      if (record.breakfast) {
        const breakfastLabels = {
          'ate': '朝食:食べた',
          'not_ate': '朝食:食べていない',
          'never_eat': '朝食:いつも食べない',
          'no_appetite': '朝食:欲しくない',
          'no_time': '朝食:時間がない',
          'other': '朝食:その他'
        };
        if (breakfastLabels[record.breakfast]) {
          details.push(breakfastLabels[record.breakfast]);
        }
      }
      
      if (record.bowel_movement) {
        const bowelLabels = {
          'this_morning': '便通:今朝した',
          'normal': '便通:普通便',
          'diarrhea': '便通:下痢便',
          'not_this_morning': '便通:今朝はしなかった',
          'never_morning': '便通:朝はいつもしない',
          'no_time': '便通:時間がなかった',
          'constipated': '便通:便秘ぎみ',
          'no_urge': '便通:便意がなかった',
          'other': '便通:その他'
        };
        if (bowelLabels[record.bowel_movement]) {
          details.push(bowelLabels[record.bowel_movement]);
        }
      }
      
      if (record.treatment) {
        const treatmentLabels = {
          'observe_classroom': '処置:教室で観察',
          'rest_infirmary': '処置:保健室で休養',
          'health_consultation': '処置:健康相談',
          'infirmary_attendance': '処置:保健室登校',
          'infirmary_exam': '処置:保健室試験',
          'safe_place': '処置:居場所',
          'rest_early_leave': '処置:休養早退',
          'early_leave': '処置:早退',
          'hospital_visit': '処置:病院受診',
          'separate_exam': '処置:別室受験',
          'separate_move': '処置:別室移動',
          'other': '処置:その他'
        };
        if (treatmentLabels[record.treatment]) {
          details.push(treatmentLabels[record.treatment]);
        }
      }
      
      // 外科関連情報
      if (record.injury_location) {
        const locationLabels = {
          'hand_wrist': '部位:手・手首',
          'arm': '部位:腕',
          'finger_hand': '部位:指(手)',
          'finger_foot': '部位:指(足)',
          'foot_ankle': '部位:足・足首',
          'leg': '部位:脚',
          'knee': '部位:膝',
          'head': '部位:頭',
          'face': '部位:顔',
          'abdomen': '部位:腹',
          'chest': '部位:胸',
          'waist': '部位:腰',
          'back_shoulder_neck': '部位:背中・肩・首',
          'tooth': '部位:歯',
          'eye': '部位:眼',
          'ear': '部位:耳',
          'nose': '部位:鼻',
          'other': '部位:その他'
        };
        if (locationLabels[record.injury_location]) {
          details.push(locationLabels[record.injury_location]);
        }
      }
      
      if (record.injury_place) {
        const placeLabels = {
          'classroom': '場所:教室',
          'hallway': '場所:廊下',
          'stairs': '場所:階段',
          'gymnasium': '場所:体育館',
          'ground': '場所:グランド',
          'schoolyard': '場所:校庭',
          'music_room': '場所:音楽室',
          'art_room': '場所:美術室',
          'science_room': '場所:理科室',
          'auditorium': '場所:講堂',
          'home_economics': '場所:家庭科室',
          'cooking_room': '場所:調理室',
          'computer_room': '場所:コンピュータ室',
          'commute_route': '場所:通学路',
          'toilet': '場所:トイレ',
          'unknown': '場所:不明',
          'bicycle_parking': '場所:自転車置き場',
          'martial_arts': '場所:格技室',
          'practice_room': '場所:実習室',
          'cafeteria': '場所:食堂',
          'other': '場所:その他'
        };
        if (placeLabels[record.injury_place]) {
          details.push(placeLabels[record.injury_place]);
        }
      }
      
      if (record.surgical_treatment) {
        const surgicalTreatmentLabels = {
          'disinfection': '処置:消毒',
          'icing': '処置:アイシング・湿布',
          'warm_compress': '処置:温罨法',
          'foreign_removal': '処置:異物除去',
          'parent_contact': '処置:保護者連絡',
          'hospital_instruction': '処置:病院受診を指示',
          'hospital_transport': '処置:病院へ搬送',
          'observation': '処置:経過観察',
          'rest_observation': '処置:経過観察(安静)',
          'teacher_contact': '処置:担任・部活顧問連絡',
          'bandaid': '処置:カットバン貼付',
          'ointment': '処置:塗り薬',
          'other': '処置:その他'
        };
        if (surgicalTreatmentLabels[record.surgical_treatment]) {
          details.push(surgicalTreatmentLabels[record.surgical_treatment]);
        }
      }
      
      // 教科
      if (record.subject) {
        const subjectLabels = {
          'japanese': '教科:国語',
          'social_studies': '教科:社会',
          'mathematics': '教科:数学',
          'science': '教科:理科',
          'english': '教科:英語',
          'music': '教科:音楽',
          'art': '教科:美術',
          'pe': '教科:体育',
          'technology': '教科:技術',
          'home_economics': '教科:家庭',
          'moral': '教科:道徳',
          'integrated': '教科:総合',
          'other': '教科:その他'
        };
        if (subjectLabels[record.subject]) {
          details.push(subjectLabels[record.subject]);
        }
      }
      
      // 部活動
      if (record.club_activity) {
        details.push('部活:' + record.club_activity);
      }
      
      // 行事
      if (record.event_type) {
        const eventLabels = {
          'sports_festival': '行事:体育祭',
          'cultural_festival': '行事:文化祭',
          'excursion': '行事:遠足',
          'field_trip': '行事:校外学習',
          'marathon': '行事:マラソン大会',
          'chorus_contest': '行事:合唱コンクール',
          'entrance_ceremony': '行事:入学式',
          'graduation_ceremony': '行事:卒業式',
          'entrance_rehearsal': '行事:入学式予行',
          'graduation_rehearsal': '行事:卒業式予行',
          'clean_operation': '行事:クリーン作戦',
          'open_school': '行事:オープンスクール',
          'other': '行事:その他'
        };
        if (eventLabels[record.event_type]) {
          details.push(eventLabels[record.event_type]);
        }
      }
      
      // 備考・原因・その他
      if (record.treatment_notes) {
        details.push(record.treatment_notes);
      }
      
      return details.join('、');
    };

    const addVisit = () => {
      visits.value.push({
        time: '',
        grade: '',
        class: '',
        number: '',
        name: '',
        gender: '',
        category: '',
        type: '',
        occurrence: '',
        treatment: ''
      });
    };

    const loadDiary = async () => {
      try {
        // Update date fields
        const date = new Date(diary.value.date);
        diary.value.year = date.getFullYear() - 2018;
        diary.value.month = date.getMonth() + 1;
        diary.value.day = date.getDate();

        // Reset absence data
        absence.value = {
          absent: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
          sick: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
          accident: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
          suspension: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 },
          mourning: { grade1: 0, grade2: 0, grade3: 0, grade4: 0, grade5: 0, grade6: 0 }
        };

        // Load attendance records for the selected date
        const attendanceResponse = await fetch(`/api/v1/attendance-records?date=${diary.value.date}&status=absent`);
        if (attendanceResponse.ok) {
          const attendanceData = await attendanceResponse.json();
          
          // Process attendance records (status=absent)
          if (attendanceData.data) {
            attendanceData.data.forEach(record => {
              const grade = record.student?.school_class?.grade || record.grade;
              if (grade >= 1 && grade <= 6) {
                const gradeKey = `grade${grade}`;
                absence.value.absent[gradeKey]++;
              }
            });
          }
        }

        // Load nursing visit records for the selected date
        const nursingResponse = await fetch(`/api/v1/nursing-visits?date=${diary.value.date}`);
        if (nursingResponse.ok) {
          const nursingData = await nursingResponse.json();
          
          // Clear existing visits
          visits.value = [];

          // Process nursing visit records
          if (nursingData.data && nursingData.data.length > 0) {
            nursingData.data.forEach(record => {
              const grade = record.grade;
              
              // Count absence types from nursing visits with category='absence'
              if (record.category === 'absence' && grade >= 1 && grade <= 6) {
                const gradeKey = `grade${grade}`;
                
                // Count total absences
                absence.value.absent[gradeKey]++;
                
                // Map absence_reason to absence categories
                const reason = record.absence_reason;
                
                // 病欠に該当する原因
                const sickReasons = ['headache', 'cold', 'stomachache', 'diarrhea', 'fever', 'sleep_deprivation', 
                                    'asthma', 'nausea', 'nephritis', 'ent_disease', 'poor_health', 
                                    'hospitalization', 'appendicitis', 'orthostatic'];
                
                // 伝染病（出停に該当）
                const suspensionReasons = ['influenza', 'chickenpox', 'mumps', 'epidemic_keratitis', 
                                          'other_infectious', 'covid19'];
                
                if (sickReasons.includes(reason)) {
                  absence.value.sick[gradeKey]++;
                } else if (reason === 'accident' || reason === 'injury') {
                  absence.value.accident[gradeKey]++;
                } else if (suspensionReasons.includes(reason)) {
                  absence.value.suspension[gradeKey]++;
                } else if (reason === 'mourning') {
                  absence.value.mourning[gradeKey]++;
                }
              }
              
              // Extract class name without grade prefix (e.g., "3進学" -> "進学")
              const className = record.class_name ? record.class_name.replace(/^[0-9]/, '') : '';
              
              // Determine type label based on category
              let typeLabel = '';
              let treatmentText = '';
              
              if (record.category === 'absence' || record.category === 'late') {
                // For absence/late, use absence_reason
                typeLabel = getAbsenceReasonLabel(record.absence_reason) || '';
                
                // Add absence_reason to treatment notes if present
                if (record.absence_reason) {
                  const reasonLabel = getAbsenceReasonLabel(record.absence_reason);
                  if (reasonLabel) {
                    treatmentText = reasonLabel;
                  }
                }
              } else {
                // For other categories, use type_detail
                typeLabel = getTypeLabel(record.type_detail) || '';
              }
              
              // フォーマットされた詳細情報を取得
              const detailedInfo = formatDetailedInfo(record);
              if (detailedInfo) {
                if (treatmentText) {
                  treatmentText = treatmentText + '、' + detailedInfo;
                } else {
                  treatmentText = detailedInfo;
                }
              }
              
              // Add to visits list
              visits.value.push({
                time: record.time || '',
                grade: record.grade || '',
                class: className,
                number: record.student_number || '',
                name: record.student_name || '',
                gender: record.gender === '男' ? '男' : record.gender === '女' ? '女' : '',
                category: getCategoryLabel(record.category) || '',
                type: typeLabel,
                occurrence: getOccurrenceLabel(record.occurrence_time) || '',
                treatment: treatmentText
              });
            });
          }
          
          // Add empty rows if needed (minimum 5 rows)
          while (visits.value.length < 5) {
            visits.value.push({
              time: '',
              grade: '',
              class: '',
              number: '',
              name: '',
              gender: '',
              category: '',
              type: '',
              occurrence: '',
              treatment: ''
            });
          }
        }

        console.log('Loaded diary data for:', diary.value.date);
        console.log('Absence data:', absence.value);
        console.log('Visits data:', visits.value);

      } catch (error) {
        console.error('Error loading diary data:', error);
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: 'データの読み込みに失敗しました'
        });
      }
    };

    const saveDiary = async () => {
      try {
        // TODO: Save diary data to API
        notificationStore.addNotification({
          type: 'success',
          title: '保存完了',
          message: '養護日誌を保存しました'
        });
      } catch (error) {
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: '保存に失敗しました'
        });
      }
    };

    const downloadPdf = async () => {
      try {
        console.log('Starting PDF download...');
        
        const payload = {
          date: diary.value.date,
          year: diary.value.year,
          month: diary.value.month,
          day: diary.value.day,
          weather: diary.value.weather,
          temperature: diary.value.temperature,
          humidity: diary.value.humidity,
          absence: absence.value,
          visits: visits.value,
          schoolEvents: diary.value.schoolEvents,
          waterQuality: diary.value.waterQuality,
          chlorine: diary.value.chlorine,
          notes: diary.value.notes
        };
        
        console.log('Payload:', payload);
        
        // axiosを使用してPDF生成APIにリクエスト
        const apiUrl = `${window.location.origin}/api/v1/nursing-log/generate-pdf`;
        console.log('Request URL:', apiUrl);
        
        const response = await axios.post(apiUrl, payload, {
          responseType: 'blob',
          headers: {
            'Accept': 'application/pdf'
          }
        });

        console.log('Response received:', {
          status: response.status,
          headers: response.headers,
          dataType: typeof response.data,
          dataSize: response.data.size
        });

        // レスポンスのContent-Typeを確認
        const contentType = response.headers['content-type'];
        console.log('Response Content-Type:', contentType);

        // Blobのサイズを確認
        const blob = response.data;
        console.log('Blob size:', blob.size, 'Blob type:', blob.type);
        
        if (blob.size === 0) {
          throw new Error('PDFファイルが空です');
        }

        // Blobの内容がPDFかどうか確認
        if (!contentType || !contentType.includes('application/pdf')) {
          // エラーレスポンスの可能性
          const text = await blob.text();
          console.error('Unexpected response:', text);
          throw new Error('PDFではないレスポンスを受信しました: ' + text.substring(0, 100));
        }

        // PDFファイルをダウンロード
        const blobUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = `nursing_log_${diary.value.date.replace(/-/g, '')}.pdf`;
        document.body.appendChild(a);
        a.click();
        
        // クリーンアップ
        setTimeout(() => {
          window.URL.revokeObjectURL(blobUrl);
          document.body.removeChild(a);
        }, 100);

        notificationStore.addNotification({
          type: 'success',
          title: 'PDF出力完了',
          message: '養護日誌をPDFで出力しました'
        });
      } catch (error) {
        console.error('PDF download error:', error);
        console.error('Error details:', {
          message: error.message,
          response: error.response,
          stack: error.stack
        });
        
        let errorMessage = 'PDF出力に失敗しました';
        
        if (error.response) {
          // サーバーからのエラーレスポンス
          if (error.response.data instanceof Blob) {
            const text = await error.response.data.text();
            console.error('Server error response:', text);
            try {
              const jsonError = JSON.parse(text);
              errorMessage = jsonError.message || errorMessage;
            } catch (e) {
              errorMessage = text.substring(0, 100);
            }
          } else {
            errorMessage = error.response.data.message || errorMessage;
          }
        } else if (error.message) {
          errorMessage = error.message;
        }
        
        notificationStore.addNotification({
          type: 'danger',
          title: 'エラー',
          message: errorMessage
        });
      }
    };

    const printDiary = () => {
      window.print();
    };

    // Lifecycle
    onMounted(() => {
      loadDiary();
    });
    
    return {
      diary,
      absence,
      visits,
      getDayOfWeek,
      absenceTotal,
      getCategoryLabel,
      getTypeLabel,
      getAbsenceReasonLabel,
      getOccurrenceLabel,
      formatDetailedInfo,
      addVisit,
      loadDiary,
      saveDiary,
      downloadPdf,
      printDiary
    };
  }
};
</script>

<style scoped>
.nursing-diary {
  background: white;
}

.nursing-diary input[type="text"],
.nursing-diary input[type="number"],
.nursing-diary textarea {
  font-size: inherit;
}

.nursing-diary input[type="number"]::-webkit-inner-spin-button,
.nursing-diary input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.nursing-diary input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

/* Print styles */
@media print {
  .nursing-diary {
    font-size: 10pt;
  }
  
  button,
  .no-print {
    display: none !important;
  }
  
  .nursing-diary table {
    page-break-inside: avoid;
  }
  
  .nursing-diary input,
  .nursing-diary textarea {
    border: none !important;
    background: transparent !important;
  }
}
</style>
