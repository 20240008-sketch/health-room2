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
              let treatmentText = record.treatment_notes || '';
              
              if (record.category === 'absence' || record.category === 'late') {
                // For absence/late, use absence_reason
                typeLabel = getAbsenceReasonLabel(record.absence_reason) || '';
                
                // Add absence_reason to treatment notes if present
                if (record.absence_reason) {
                  const reasonLabel = getAbsenceReasonLabel(record.absence_reason);
                  if (treatmentText) {
                    treatmentText = reasonLabel + '、' + treatmentText;
                  } else {
                    treatmentText = reasonLabel;
                  }
                }
              } else {
                // For other categories, use type_detail
                typeLabel = getTypeLabel(record.type_detail) || '';
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
