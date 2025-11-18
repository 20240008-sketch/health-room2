<template>
  <div class="print-form ophthalmology-form">
    <div class="form-header">
      <div class="recipient-line">
        <span class="recipient">保護者様</span>
        <span class="date-field">令和　　年（　　　　年）　月　　日</span>
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
          <th class="col-number"></th>
          <th class="col-disease">病　　　名</th>
          <th class="col-description">説　　　　　明</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="col-number">１</td>
          <td class="col-disease">アレルギー性結膜炎</td>
          <td class="col-description">かゆみなどの不快感が続くことが多いので、医師にご相談ください。</td>
        </tr>
        <tr>
          <td class="col-number">２</td>
          <td class="col-disease">結膜炎</td>
          <td class="col-description">目が赤く充血しています。人にうつることもありますので、早く受診されますようお勧めします。</td>
        </tr>
        <tr>
          <td class="col-number">３</td>
          <td class="col-disease">麦粒腫</td>
          <td class="col-description">まぶたが炎症をおこしています。腫れや痛みが進むこともあります。</td>
        </tr>
        <tr>
          <td class="col-number">４</td>
          <td class="col-disease">眼瞼縁炎</td>
          <td class="col-description">目の縁が炎症をおこしています。症状が進むと、まつげが抜けたり、結膜炎をおこしやすくなります。</td>
        </tr>
        <tr>
          <td class="col-number">５</td>
          <td class="col-disease">霰粒腫</td>
          <td class="col-description">まぶたの中に硬いしこりができています。大きくなるとまぶたが開きにくくなることもあります。</td>
        </tr>
        <tr>
          <td class="col-number">６</td>
          <td class="col-disease">内反症</td>
          <td class="col-description">まぶたが内側に入り込んでいます。まつげが角膜をこすると、ゴロゴロして涙がでたり、視力が下がることもあります。</td>
        </tr>
        <tr>
          <td class="col-number">７</td>
          <td class="col-disease">斜視</td>
          <td class="col-description">そのままにしておくと弱視になる場合があります。できるだけ早く受診されますようにお勧めします。</td>
        </tr>
        <tr>
          <td class="col-number">８</td>
          <td class="col-disease">斜位</td>
          <td class="col-description">眼精疲労を起こしやすく、矯正が必要となる場合もあります。</td>
        </tr>
        <tr>
          <td class="col-number">９</td>
          <td class="col-disease">その他（　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　）</td>
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
  width: 40px;
  text-align: center;
}

.col-disease {
  width: 150px;
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
