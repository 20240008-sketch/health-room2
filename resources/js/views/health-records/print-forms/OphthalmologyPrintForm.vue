<template>
  <div class="print-form ophthalmology-form">
    <div class="form-header">
      <div class="recipient-line">
        <span class="recipient">保護者様</span>
        <span class="date-field">令和{{ getWarekiYear() }}年（{{ getCurrentYear() }}年）{{ getCurrentMonth() }}月{{ getCurrentDay() }}日</span>
      </div>
      <div class="school-info">
        <div class="school-name">誠英高等学校</div>
        <div class="principal">校長　大田　真一</div>
      </div>
      <h1 class="form-title">眼科検診結果のお知らせ</h1>
    </div>

    <div class="student-info">
      <span class="student-grade">{{ getGrade() }}年</span>
      <span class="student-course">{{ getCourse() }}コース</span>
      <span class="student-class">{{ getClassNumber() }}組</span>
      <span class="student-number">{{ student.student_number }}番</span>
      <span class="student-name">{{ student.name }}</span>
    </div>

    <div class="notice-text">
      今年度の眼科検診の結果は下記◯印のとおりです。専門医で受診されますようお勧めします。<br>
      なお、受診後は下欄の連絡表に記入してもらい、切り取らずに学校に提出してください。
    </div>

    <table class="diagnosis-table">
      <thead>
        <tr>
          <th class="col-number">番号</th>
          <th class="col-disease">病　　　名</th>
          <th class="col-description">説　　　　　明</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(1)">
            <span class="number-circle" :class="{ 'selected': formData.findings[1] }">１</span>
          </td>
          <td class="col-disease">アレルギー性結膜炎</td>
          <td class="col-description">かゆみなどの不快感が続くことが多いので、医師にご相談ください。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(2)">
            <span class="number-circle" :class="{ 'selected': formData.findings[2] }">２</span>
          </td>
          <td class="col-disease">結膜炎</td>
          <td class="col-description">目が赤く充血しています。人にうつることもありますので、早く受診されますようお勧めします。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(3)">
            <span class="number-circle" :class="{ 'selected': formData.findings[3] }">３</span>
          </td>
          <td class="col-disease">麦粒腫</td>
          <td class="col-description">まぶたが炎症をおこしています。腫れや痛みが進むこともあります。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(4)">
            <span class="number-circle" :class="{ 'selected': formData.findings[4] }">４</span>
          </td>
          <td class="col-disease">眼瞼縁炎</td>
          <td class="col-description">目の縁が炎症をおこしています。症状が進むと、まつげが抜けたり、結膜炎をおこしやすくなります。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(5)">
            <span class="number-circle" :class="{ 'selected': formData.findings[5] }">５</span>
          </td>
          <td class="col-disease">霰粒腫</td>
          <td class="col-description">まぶたの中に硬いしこりができています。大きくなるとまぶたが開きにくくなることもあります。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(6)">
            <span class="number-circle" :class="{ 'selected': formData.findings[6] }">６</span>
          </td>
          <td class="col-disease">内反症</td>
          <td class="col-description">まぶたが内側に入り込んでいます。まつげが角膜をこすると、ゴロゴロして涙がでたり、視力が下がることもあります。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(7)">
            <span class="number-circle" :class="{ 'selected': formData.findings[7] }">７</span>
          </td>
          <td class="col-disease">斜視</td>
          <td class="col-description">そのままにしておくと弱視になる場合があります。できるだけ早く受診されますようにお勧めします。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(8)">
            <span class="number-circle" :class="{ 'selected': formData.findings[8] }">８</span>
          </td>
          <td class="col-disease">斜位</td>
          <td class="col-description">眼精疲労を起こしやすく、矯正が必要となる場合もあります。</td>
        </tr>
        <tr>
          <td class="number-cell clickable" @click="toggleFinding(9)">
            <span class="number-circle" :class="{ 'selected': formData.findings[9] }">９</span>
          </td>
          <td class="col-disease">
            その他（<input 
              type="text" 
              v-model="formData.otherFindings" 
              class="other-input"
              placeholder="その他の所見を入力"
              @click.stop
            >）
          </td>
          <td class="col-description"></td>
        </tr>
      </tbody>
    </table>

    <div class="separator"></div>

    <div class="report-section">
      <h2 class="report-title">眼　科　受　診　連　絡　票</h2>

      <table class="report-table">
        <tr>
          <th>診　断　名</th>
          <td></td>
        </tr>
        <tr>
          <th>治療の状況</th>
          <td>
            １　治療済み　　　２　治療中　　　３　経過観察<br>
            ４　治療を要しない
          </td>
        </tr>
        <tr>
          <th>運動について</th>
          <td>
            １水泳可　　　　　２水泳不可　　　３　その他
          </td>
        </tr>
      </table>

      <div class="signature-section">
        <div class="signature-date">令和　　年　　月　　日</div>
        <div class="signature-fields">
          <div class="field">医療機関名</div>
          <div class="field">保護者氏名</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OphthalmologyPrintForm',
  props: {
    student: {
      type: Object,
      required: true
    },
    healthRecord: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      formData: {
        findings: {
          1: false,  // アレルギー性結膜炎
          2: false,  // 結膜炎
          3: false,  // 麦粒腫
          4: false,  // 眼瞼縁炎
          5: false,  // 霰粒腫
          6: false,  // 内反症
          7: false,  // 斜視
          8: false,  // 斜位
          9: false   // その他
        },
        otherFindings: ''
      }
    };
  },
  methods: {
    getGrade() {
      if (!this.student.class_id) return '';
      // クラスIDから学年を抽出（例: 25111 → 1年）
      const classId = String(this.student.class_id);
      if (classId.length >= 3) {
        return classId.charAt(1);
      }
      return '';
    },
    getCourse() {
      if (!this.student.class_id) return '';
      const classId = String(this.student.class_id);
      if (classId.length >= 4) {
        const courseCode = classId.charAt(2);
        const courseMap = {
          '1': '特別進学',
          '2': '進学',
          '3': '総合',
          '4': '情報会計',
          '5': '福祉'
        };
        return courseMap[courseCode] || '';
      }
      return '';
    },
    getClassNumber() {
      if (!this.student.class_id) return '';
      const classId = String(this.student.class_id);
      if (classId.length >= 5) {
        return classId.charAt(4);
      }
      return '';
    },
    getCurrentYear() {
      const now = new Date();
      return now.getFullYear();
    },
    getCurrentMonth() {
      const now = new Date();
      return now.getMonth() + 1;
    },
    getCurrentDay() {
      const now = new Date();
      return now.getDate();
    },
    getWarekiYear() {
      const now = new Date();
      const year = now.getFullYear();
      // 令和は2019年5月1日から
      return year - 2018;
    },
    toggleFinding(number) {
      this.formData.findings[number] = !this.formData.findings[number];
    },
    getFormData() {
      // 選択された所見を配列に変換
      const selectedFindings = [];
      const findingNames = {
        1: 'アレルギー性結膜炎',
        2: '結膜炎',
        3: '麦粒腫',
        4: '眼瞼縁炎',
        5: '霰粒腫',
        6: '内反症',
        7: '斜視',
        8: '斜位',
        9: 'その他'
      };
      
      for (const [key, value] of Object.entries(this.formData.findings)) {
        if (value) {
          if (key === '9' && this.formData.otherFindings) {
            selectedFindings.push(`${findingNames[key]}（${this.formData.otherFindings}）`);
          } else {
            selectedFindings.push(findingNames[key]);
          }
        }
      }
      
      return {
        findings: selectedFindings,
        findingsText: selectedFindings.join('、'),
        otherFindings: this.formData.otherFindings
      };
    }
  }
};
</script>

<style scoped>
.print-form {
  background: white;
  padding: 40px;
  font-family: 'MS Mincho', 'Yu Mincho', serif;
  line-height: 1.8;
  font-size: 14px;
}

.form-header {
  margin-bottom: 30px;
}

.recipient-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.recipient {
  font-size: 16px;
}

.date-field {
  font-size: 14px;
}

.school-info {
  text-align: right;
  margin-bottom: 20px;
}

.school-name {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 5px;
}

.principal {
  font-size: 14px;
}

.form-title {
  text-align: center;
  font-size: 20px;
  font-weight: bold;
  margin: 30px 0;
}

.student-info {
  margin-bottom: 20px;
  font-size: 16px;
}

.student-info span {
  margin-right: 15px;
}

.notice-text {
  margin-bottom: 30px;
  line-height: 2;
}

.diagnosis-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 40px;
}

.diagnosis-table th,
.diagnosis-table td {
  border: 1px solid #000;
  padding: 10px;
}

.diagnosis-table th {
  background-color: #f5f5f5;
  font-weight: bold;
  text-align: center;
}

.col-number {
  width: 60px;
  text-align: center;
}

.number-cell {
  text-align: center;
  padding: 12px 8px;
}

.number-cell.clickable {
  cursor: pointer;
}

.number-cell.clickable:hover {
  background-color: #f0f0f0;
}

.number-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 2px solid #333;
  border-radius: 50%;
  font-weight: bold;
  font-size: 16px;
  transition: all 0.2s ease;
  user-select: none;
}

.number-circle.selected {
  background-color: #333;
  color: white;
  border-color: #333;
}

.col-disease {
  width: 200px;
}

.col-disease .other-input {
  width: 300px;
  border: none;
  border-bottom: 1px solid #000;
  padding: 2px 5px;
  font-family: inherit;
  font-size: inherit;
}

.col-description {
  text-align: left;
}

.separator {
  border-top: 2px dashed #000;
  margin: 40px 0;
}

.report-section {
  margin-top: 40px;
}

.report-title {
  text-align: center;
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 30px;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
}

.report-table th,
.report-table td {
  border: 1px solid #000;
  padding: 15px;
}

.report-table th {
  background-color: #f5f5f5;
  font-weight: bold;
  width: 150px;
  text-align: left;
}

.signature-section {
  margin-top: 30px;
}

.signature-date {
  text-align: right;
  margin-bottom: 20px;
}

.signature-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.signature-fields .field {
  border-bottom: 1px solid #000;
  padding: 10px 0;
}

@media print {
  .print-form {
    padding: 20mm;
  }
}
</style>
