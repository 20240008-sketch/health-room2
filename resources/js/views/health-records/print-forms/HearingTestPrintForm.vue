<template>
  <div class="print-form hearing-test-form">
    <div class="form-header">
      <div class="recipient-line">
        <span class="recipient">保護者様</span>
        <span class="date-field">令和　　年（　　　　年）　月　　日</span>
      </div>
      <div class="school-info">
        <div class="school-name">誠英高等学校</div>
        <div class="principal">校長　大田　真一</div>
      </div>
      <h1 class="form-title">聴力検査について</h1>
    </div>

    <div class="student-info">
      <span class="student-grade">{{ getGrade() }}年</span>
      <span class="student-course">{{ getCourse() }}コース</span>
      <span class="student-class">{{ getClassNumber() }}組</span>
      <span class="student-number">{{ student.student_number }}番</span>
      <span class="student-name">{{ student.name }}</span>
    </div>

    <div class="notice-text">
      今年度の聴力検査の結果、耳鼻科の受診をお勧めします。<br>
      なお、受診後は下欄の連絡表に記入してもらい、切り取らずに学校に提出してください。
    </div>

    <table class="diagnosis-table">
      <thead>
        <tr>
          <th>　　　　　右　　　　　　　　　　　　左</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="ear-selection">
            <div class="spacer"></div>
            <div class="spacer"></div>
            <div class="spacer"></div>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="separator"></div>

    <div class="report-section">
      <h2 class="report-title">受　　診　　連　　絡　　票</h2>

      <table class="report-table">
        <tr>
          <th>診　断　名</th>
          <td></td>
        </tr>
        <tr>
          <th>治療の状況</th>
          <td>
            １　治療済み　　　２　治療中　　　３　治療を要しない
          </td>
        </tr>
        <tr>
          <th>備考</th>
          <td>
            <div class="note-space"></div>
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
  name: 'HearingTestPrintForm',
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
  padding: 15px;
}

.diagnosis-table th {
  background-color: #f5f5f5;
  font-weight: bold;
  text-align: center;
  font-size: 16px;
}

.ear-selection {
  height: 150px;
  vertical-align: top;
}

.spacer {
  height: 40px;
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

.note-space {
  height: 60px;
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
