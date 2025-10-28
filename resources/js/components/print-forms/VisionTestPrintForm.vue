<template>
  <div class="vision-test-form">
    <div class="header">
      <div class="header-top">
        <span>※保護者様</span>
        <span class="date-right">令和　　年　　月</span>
      </div>
      <div class="school-info">
        <span class="school-name">誠英高等学校</span>
      </div>
      <div class="principal">
        <span>校長　　　　　　　　　　</span>
      </div>
    </div>

    <h1 class="title">視力検査結果のお知らせ</h1>

    <div class="student-info">
      <span>{{ student?.school_class?.grade_id || '_' }}年</span>
      <span class="ml-8">{{ getCourseName() }}コース</span>
      <span class="ml-8">{{ getClassName() }}組</span>
      <span class="ml-8">{{ student?.student_number || '_' }}番</span>
      <span class="ml-8">氏名　{{ student?.name || '________________' }}</span>
    </div>

    <div class="notice-text">
      このたびの視力検査結果は下記の通りです。視力低下の疑いがありますので、専門医で受診されますようにお勧めします。<br>
      なお、受診は下欄の連絡票に記入してもらい、切り取らずに学校へ提出してください。
    </div>

    <div class="section-title">学校での検査</div>

    <table class="vision-table">
      <thead>
        <tr>
          <th></th>
          <th>裸眼視力</th>
          <th>矯正視力</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label">右</td>
          <td class="result">{{ healthRecord?.vision_right || '' }}</td>
          <td class="result">{{ healthRecord?.vision_right_corrected || '' }}</td>
        </tr>
        <tr>
          <td class="label">左</td>
          <td class="result">{{ healthRecord?.vision_left || '' }}</td>
          <td class="result">{{ healthRecord?.vision_left_corrected || '' }}</td>
        </tr>
      </tbody>
    </table>

    <div class="criteria">
      〈判断基準〉<br>
      <span class="ml-8">A　1.0以上　　　B　0.9〜0.7　　C　0.6〜0.3　　D　0.3未満</span>
    </div>

    <div class="separator"></div>

    <div class="section-title">眼科検診受診連絡票</div>

    <table class="vision-table">
      <thead>
        <tr>
          <th></th>
          <th>裸眼視力</th>
          <th>矯正視力</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label">右</td>
          <td class="empty-cell"></td>
          <td class="empty-cell"></td>
        </tr>
        <tr>
          <td class="label">左</td>
          <td class="empty-cell"></td>
          <td class="empty-cell"></td>
        </tr>
      </tbody>
    </table>

    <div class="diagnosis-section">
      <div class="diagnosis-item">
        （１）屈折異常<br>
        <div class="ml-16">
          調節緊張症　・近視　・近視性乱視　・遠視　・遠視性乱視<br>
          その他　（　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　）
        </div>
      </div>

      <div class="diagnosis-item">
        （２）眼位異常　　　（　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　）
      </div>

      <div class="diagnosis-item">
        （３）指導又は治療方針<br>
        <div class="ml-16">
          １　　経過観察　　　　　２　　点眼治療<br>
          <br>
          ３　　眼科・コンタクト処方　　（　新規　・　更新　）<br>
          <br>
          ４　　その他　（　　　　　　　　　　　　　　　　　　　　　　）
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VisionTestPrintForm',
  props: {
    student: {
      type: Object,
      required: true
    },
    healthRecord: {
      type: Object,
      required: true
    }
  },
  mounted() {
    console.log('VisionTestPrintForm mounted - Health Record:', this.healthRecord);
  },
  methods: {
    getCourseName() {
      if (this.student?.school_class?.name) {
        // クラス名から「進学」「総合」などを抽出
        const match = this.student.school_class.name.match(/([^\d]+)/);
        return match ? match[1] : '';
      }
      return '________';
    },
    getClassName() {
      if (this.student?.school_class?.name) {
        // クラス名から数字を抽出
        const match = this.student.school_class.name.match(/(\d+)/);
        return match ? match[1] : '';
      }
      return '__';
    }
  }
};
</script>

<style scoped>
.vision-test-form {
  font-family: 'MS Gothic', 'Hiragino Sans', monospace;
  padding: 10mm;
  background: white;
  width: 210mm;
  min-height: 297mm;
  margin: 0 auto;
  box-sizing: border-box;
  font-size: 11pt;
  line-height: 1.8;
}

.header {
  margin-bottom: 15px;
}

.header-top {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.date-right {
  text-align: right;
}

.school-info {
  text-align: center;
  margin-bottom: 5px;
}

.school-name {
  font-size: 13pt;
  font-weight: bold;
}

.principal {
  text-align: right;
  margin-bottom: 10px;
}

.title {
  font-size: 16pt;
  font-weight: bold;
  text-align: center;
  margin: 20px 0;
  border-bottom: 2px solid #000;
  padding-bottom: 8px;
}

.student-info {
  margin: 15px 0;
  font-size: 11pt;
}

.ml-8 {
  margin-left: 2em;
}

.ml-16 {
  margin-left: 4em;
}

.notice-text {
  margin: 20px 0;
  line-height: 1.8;
  text-indent: 1em;
}

.section-title {
  font-weight: bold;
  margin: 20px 0 10px 0;
  font-size: 12pt;
}

.vision-table {
  width: 100%;
  max-width: 400px;
  border-collapse: collapse;
  margin: 15px 0;
  margin-left: 2em;
}

.vision-table th,
.vision-table td {
  border: 1px solid #000;
  padding: 8px 12px;
  text-align: center;
}

.vision-table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.vision-table .label {
  font-weight: bold;
  width: 80px;
}

.vision-table .result {
  font-size: 13pt;
  font-weight: bold;
  min-width: 100px;
}

.vision-table .empty-cell {
  height: 35px;
  background-color: #fafafa;
}

.criteria {
  margin: 15px 0;
  font-size: 10pt;
  line-height: 1.6;
}

.separator {
  border-top: 2px dashed #666;
  margin: 25px 0;
}

.diagnosis-section {
  margin-top: 20px;
}

.diagnosis-item {
  margin: 15px 0;
  line-height: 2;
}

/* 印刷用スタイル */
@media print {
  .vision-test-form {
    padding: 10mm;
    width: 210mm;
    height: 297mm;
    page-break-after: avoid;
  }

  @page {
    size: A4;
    margin: 0;
  }
}
</style>
