<template>
  <div class="otolaryngology-print-form">
    <div class="form-container">
      <!-- Header -->
      <h1 class="form-title">耳鼻咽喉科健康検診結果のお知らせ</h1>
      
      <!-- Student Info -->
      <div class="student-info">
        <div class="info-row">
          <span class="info-label">生徒氏名：</span>
          <span class="info-value underline">{{ student.name }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">学年・組：</span>
          <span class="info-value underline">{{ getClassGrade() }}</span>
        </div>
      </div>

      <!-- Description -->
      <p class="description">
        耳鼻咽喉科健康検診の結果、下記のとおりでした。
        該当する番号の前に○印をつけてお知らせします。耳鼻咽喉科の受診をお勧めします。なお、受診後は下欄の「耳鼻咽喉科受診連絡表」に記入してもらい、切り取らずに学校へ提出してください。
      </p>

      <!-- Exam Results Table -->
      <div class="exam-section">
        <!-- Ear Section -->
        <div class="section-header">【耳】</div>
        <table class="exam-table">
          <thead>
            <tr>
              <th class="col-number">番号</th>
              <th class="col-finding">所見項目</th>
              <th class="col-description">説明</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="number-cell">{{ getCheckmark('耳垢') }}1</td>
              <td>耳垢</td>
              <td>耳あかが多く、耳の聞こえに影響することがあります。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('滲出性中耳炎') }}2</td>
              <td>滲出性中耳炎</td>
              <td>鼓膜の内側に液がたまり、軽い難聴がみられることがあります。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('中耳炎') }}3</td>
              <td>（慢性）中耳炎・鼓膜穿孔</td>
              <td>中耳に炎症や穴があり、耳だれ・聴力低下が続くことがあります。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('難聴の疑い') }}4</td>
              <td>難聴の疑い</td>
              <td>聴力検査で聞こえが弱い可能性があります。精密検査が必要です。</td>
            </tr>
          </tbody>
        </table>

        <!-- Nose Section -->
        <div class="section-header">【鼻】</div>
        <table class="exam-table">
          <thead>
            <tr>
              <th class="col-number">番号</th>
              <th class="col-finding">所見項目</th>
              <th class="col-description">説明</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="number-cell">{{ getCheckmark('鼻炎') }}5</td>
              <td>鼻炎・慢性鼻炎</td>
              <td>鼻づまりや鼻水が続く状態です。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('アレルギー性鼻炎') }}6</td>
              <td>アレルギー性鼻炎</td>
              <td>花粉やハウスダストなどで鼻水・くしゃみ・鼻づまりが出ます。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('副鼻腔炎') }}7</td>
              <td>副鼻腔炎</td>
              <td>鼻の奥の副鼻腔に炎症があり、鼻づまりや頭重感が続きます。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('鼻中隔弯曲症') }}8</td>
              <td>鼻中隔弯曲症</td>
              <td>鼻の仕切りが曲がっており、鼻づまりの原因になることがあります。</td>
            </tr>
          </tbody>
        </table>

        <!-- Throat Section -->
        <div class="section-header">【咽喉（のど）】</div>
        <table class="exam-table">
          <thead>
            <tr>
              <th class="col-number">番号</th>
              <th class="col-finding">所見項目</th>
              <th class="col-description">説明</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="number-cell">{{ getCheckmark('扁桃肥大') }}9</td>
              <td>扁桃肥大</td>
              <td>扁桃腺が大きく、いびきや感染を起こしやすい状態です。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('扁桃炎') }}10</td>
              <td>扁桃炎</td>
              <td>のどの扁桃腺が赤く腫れ、痛みや発熱を伴うことがあります。</td>
            </tr>
            <tr>
              <td class="number-cell">{{ getCheckmark('アデノイド') }}11</td>
              <td>アデノイド</td>
              <td>鼻の奥のリンパ組織が大きく、鼻づまりやいびきの原因になります。</td>
            </tr>
          </tbody>
        </table>

        <!-- Oral Section -->
        <div class="section-header">【口腔】</div>
        <table class="exam-table">
          <tbody>
            <tr>
              <td class="number-cell-inline">
                {{ getCheckmark('口内炎') }}12　口内炎　　{{ getCheckmark('舌小帯異常') }}13　舌小帯異常　　{{ getCheckmark('舌異常') }}14　舌異常
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Other Section -->
        <div class="section-header">【その他】</div>
        <table class="exam-table">
          <tbody>
            <tr>
              <td class="number-cell">{{ getCheckmark('その他') }}15</td>
              <td>その他（　　　　　　　　　）</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Exam Info -->
      <div class="exam-info">
        <div class="info-line">
          <span>検診実施日：<span class="underline-short">{{ formatDate(healthRecord?.measured_date) }}</span></span>
          <span class="ml-4">校医名：<span class="underline-med">　　　　　　　　　　　　　　</span></span>
          <span class="ml-4">確認印：<span class="underline-short">　　　　　　</span></span>
        </div>
      </div>

      <!-- Divider -->
      <div class="divider"></div>

      <!-- Follow-up Form -->
      <h2 class="followup-title">耳鼻咽喉科受診連絡表</h2>
      <table class="followup-table">
        <tbody>
          <tr>
            <td class="followup-label">病名</td>
            <td class="followup-value">_____________________________________________</td>
          </tr>
          <tr>
            <td class="followup-label">治療の状況</td>
            <td class="followup-value">
              □ 治療中　□ 経過観察　□ 治癒　□ その他（　　　　　　　）
            </td>
          </tr>
          <tr>
            <td class="followup-label">水泳について</td>
            <td class="followup-value">
              □ 可能　□ 不可　□ その他（　　　　　　　）
            </td>
          </tr>
          <tr>
            <td class="followup-label">受診日</td>
            <td class="followup-value">＿＿年＿＿月＿＿日</td>
          </tr>
          <tr>
            <td class="followup-label">医療機関名</td>
            <td class="followup-value">_____________________________________________</td>
          </tr>
          <tr>
            <td class="followup-label">保護者氏名</td>
            <td class="followup-value">
              _____________________________________________　　印　＿＿＿＿＿
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtolaryngologyPrintForm',
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
  setup(props) {
    const getClassGrade = () => {
      if (props.student.school_class) {
        return `${props.student.school_class.grade_id}年${props.student.school_class.name}`;
      }
      return '　　　　　　　　　';
    };

    const getOtolaryngologyItems = () => {
      if (!props.healthRecord?.otolaryngology_result) return [];
      try {
        return JSON.parse(props.healthRecord.otolaryngology_result);
      } catch (e) {
        return [];
      }
    };

    const getCheckmark = (findingName) => {
      const items = getOtolaryngologyItems();
      const hasItem = items.some(item => 
        item.exam_result?.includes(findingName) || 
        item.diagnosis?.includes(findingName)
      );
      return hasItem ? '○' : '　';
    };

    const formatDate = (date) => {
      if (!date) return '　　　年　　月　　日';
      const d = new Date(date);
      return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
    };

    return {
      getClassGrade,
      getCheckmark,
      formatDate
    };
  }
};
</script>

<style scoped>
.otolaryngology-print-form {
  width: 210mm;
  min-height: 297mm;
  padding: 10mm;
  background: white;
  font-family: 'MS Gothic', 'Hiragino Sans', sans-serif;
  font-size: 10pt;
  line-height: 1.4;
}

.form-container {
  width: 100%;
}

.form-title {
  text-align: center;
  font-size: 16pt;
  font-weight: bold;
  margin-bottom: 8mm;
  border-bottom: 2px solid #000;
  padding-bottom: 3mm;
}

.student-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5mm;
  font-size: 11pt;
}

.info-row {
  display: flex;
  align-items: center;
}

.info-label {
  font-weight: bold;
  margin-right: 2mm;
}

.info-value {
  min-width: 40mm;
}

.underline {
  border-bottom: 1px solid #000;
  padding: 0 2mm;
}

.underline-short {
  border-bottom: 1px solid #000;
  padding: 0 1mm;
  display: inline-block;
  min-width: 20mm;
}

.underline-med {
  border-bottom: 1px solid #000;
  padding: 0 1mm;
  display: inline-block;
  min-width: 40mm;
}

.description {
  margin-bottom: 5mm;
  text-align: justify;
  font-size: 9.5pt;
  line-height: 1.6;
}

.exam-section {
  margin-bottom: 5mm;
}

.section-header {
  font-weight: bold;
  font-size: 11pt;
  margin: 3mm 0 1mm 0;
  background-color: #f0f0f0;
  padding: 1mm 2mm;
  border-left: 3px solid #333;
}

.exam-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2mm;
  font-size: 9.5pt;
}

.exam-table th,
.exam-table td {
  border: 1px solid #333;
  padding: 1mm 2mm;
}

.exam-table th {
  background-color: #e0e0e0;
  font-weight: bold;
  text-align: center;
}

.col-number {
  width: 12%;
}

.col-finding {
  width: 25%;
}

.col-description {
  width: 63%;
}

.number-cell {
  text-align: center;
  font-size: 10pt;
  font-weight: bold;
}

.number-cell-inline {
  padding: 2mm;
  font-size: 10pt;
}

.exam-info {
  margin: 5mm 0;
  font-size: 10pt;
}

.info-line {
  display: flex;
  align-items: center;
}

.ml-4 {
  margin-left: 8mm;
}

.divider {
  border-top: 2px dashed #666;
  margin: 5mm 0;
}

.followup-title {
  text-align: center;
  font-size: 13pt;
  font-weight: bold;
  margin-bottom: 3mm;
  padding: 2mm;
  background-color: #f5f5f5;
  border: 2px solid #333;
}

.followup-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 10pt;
}

.followup-table td {
  border: 1px solid #333;
  padding: 2mm 3mm;
}

.followup-label {
  width: 25%;
  background-color: #f0f0f0;
  font-weight: bold;
  text-align: center;
}

.followup-value {
  width: 75%;
}

@media print {
  .otolaryngology-print-form {
    padding: 5mm;
  }
  
  @page {
    size: A4;
    margin: 5mm;
  }
}
</style>
