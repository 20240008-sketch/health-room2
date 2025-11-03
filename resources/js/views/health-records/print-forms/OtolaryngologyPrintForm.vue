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
              <td class="number-cell clickable" @click="toggleFinding(1)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[1] }">1</span>
              </td>
              <td>耳垢</td>
              <td>耳あかが多く、耳の聞こえに影響することがあります。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(2)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[2] }">2</span>
              </td>
              <td>滲出性中耳炎</td>
              <td>鼓膜の内側に液がたまり、軽い難聴がみられることがあります。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(3)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[3] }">3</span>
              </td>
              <td>（慢性）中耳炎・鼓膜穿孔</td>
              <td>中耳に炎症や穴があり、耳だれ・聴力低下が続くことがあります。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(4)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[4] }">4</span>
              </td>
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
              <td class="number-cell clickable" @click="toggleFinding(5)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[5] }">5</span>
              </td>
              <td>鼻炎・慢性鼻炎</td>
              <td>鼻づまりや鼻水が続く状態です。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(6)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[6] }">6</span>
              </td>
              <td>アレルギー性鼻炎</td>
              <td>花粉やハウスダストなどで鼻水・くしゃみ・鼻づまりが出ます。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(7)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[7] }">7</span>
              </td>
              <td>副鼻腔炎</td>
              <td>鼻の奥の副鼻腔に炎症があり、鼻づまりや頭重感が続きます。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(8)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[8] }">8</span>
              </td>
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
              <td class="number-cell clickable" @click="toggleFinding(9)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[9] }">9</span>
              </td>
              <td>扁桃肥大</td>
              <td>扁桃腺が大きく、いびきや感染を起こしやすい状態です。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(10)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[10] }">10</span>
              </td>
              <td>扁桃炎</td>
              <td>のどの扁桃腺が赤く腫れ、痛みや発熱を伴うことがあります。</td>
            </tr>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(11)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[11] }">11</span>
              </td>
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
                <span class="clickable-inline" @click="toggleFinding(12)">
                  <span class="number-circle" :class="{ 'selected': selectedFindings[12] }">12</span>　口内炎
                </span>　　
                <span class="clickable-inline" @click="toggleFinding(13)">
                  <span class="number-circle" :class="{ 'selected': selectedFindings[13] }">13</span>　舌小帯異常
                </span>　　
                <span class="clickable-inline" @click="toggleFinding(14)">
                  <span class="number-circle" :class="{ 'selected': selectedFindings[14] }">14</span>　舌異常
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Other Section -->
        <div class="section-header">【その他】</div>
        <table class="exam-table">
          <tbody>
            <tr>
              <td class="number-cell clickable" @click="toggleFinding(15)">
                <span class="number-circle" :class="{ 'selected': selectedFindings[15] }">15</span>
              </td>
              <td>その他（<input type="text" v-model="formData.other_finding" class="input-inline" />）</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Exam Info -->
      <div class="exam-info">
        <div class="info-line">
          <span>検診実施日：
            <input type="text" v-model="formData.exam_date_year" class="input-date" />年
            <input type="text" v-model="formData.exam_date_month" class="input-date" />月
            <input type="text" v-model="formData.exam_date_day" class="input-date" />日
          </span>
          <span class="ml-4">校医名：
            <input type="text" v-model="formData.doctor_name" class="input-doctor" />
          </span>
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
            <td class="followup-value">
              <input type="text" v-model="formData.followup_disease" class="input-full" />
            </td>
          </tr>
          <tr>
            <td class="followup-label">治療の状況</td>
            <td class="followup-value">
              <label><input type="checkbox" v-model="formData.followup_treatment_status" value="treating" /> 治療中</label>　
              <label><input type="checkbox" v-model="formData.followup_treatment_status" value="observation" /> 経過観察</label>　
              <label><input type="checkbox" v-model="formData.followup_treatment_status" value="cured" /> 治癒</label>　
              <label><input type="checkbox" v-model="formData.followup_treatment_status" value="other" /> その他（<input type="text" v-model="formData.followup_other_treatment" class="input-short" />）</label>
            </td>
          </tr>
          <tr>
            <td class="followup-label">水泳について</td>
            <td class="followup-value">
              <label><input type="checkbox" v-model="formData.followup_swimming" value="possible" /> 可能</label>　
              <label><input type="checkbox" v-model="formData.followup_swimming" value="impossible" /> 不可</label>　
              <label><input type="checkbox" v-model="formData.followup_swimming" value="other" /> その他（<input type="text" v-model="formData.followup_other_swimming" class="input-short" />）</label>
            </td>
          </tr>
          <tr>
            <td class="followup-label">受診日</td>
            <td class="followup-value">
              <input type="text" v-model="formData.followup_visit_year" class="input-date" />年
              <input type="text" v-model="formData.followup_visit_month" class="input-date" />月
              <input type="text" v-model="formData.followup_visit_day" class="input-date" />日
            </td>
          </tr>
          <tr>
            <td class="followup-label">医療機関名</td>
            <td class="followup-value">
              <input type="text" v-model="formData.followup_clinic_name" class="input-full" />
            </td>
          </tr>
          <tr>
            <td class="followup-label">保護者氏名</td>
            <td class="followup-value">
              <input type="text" v-model="formData.followup_parent_name" class="input-med" />　　印　＿＿＿＿＿
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { reactive, ref } from 'vue';

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
    // 所見名から番号へのマッピング
    const findingNameToNumber = {
      '耳垢': 1,
      '滲出性中耳炎': 2,
      '（慢性）中耳炎・鼓膜穿孔': 3,
      '慢性中耳炎': 3,
      '中耳炎': 3,
      '鼓膜穿孔': 3,
      '難聴の疑い': 4,
      '難聴': 4,
      '鼻炎・慢性鼻炎': 5,
      '鼻炎': 5,
      '慢性鼻炎': 5,
      'アレルギー性鼻炎': 6,
      '副鼻腔炎': 7,
      '鼻中隔弯曲症': 8,
      '扁桃肥大': 9,
      '扁桃炎': 10,
      'アデノイド': 11,
      '口内炎': 12,
      '舌小帯異常': 13,
      '舌異常': 14,
      'その他': 15
    };

    const selectedFindings = reactive({
      1: false,  // 耳垢
      2: false,  // 滲出性中耳炎
      3: false,  // 中耳炎
      4: false,  // 難聴の疑い
      5: false,  // 鼻炎
      6: false,  // アレルギー性鼻炎
      7: false,  // 副鼻腔炎
      8: false,  // 鼻中隔弯曲症
      9: false,  // 扁桃肥大
      10: false, // 扁桃炎
      11: false, // アデノイド
      12: false, // 口内炎
      13: false, // 舌小帯異常
      14: false, // 舌異常
      15: false  // その他
    });

    // healthRecordから所見を読み込む
    if (props.healthRecord?.otolaryngology_result) {
      try {
        const results = typeof props.healthRecord.otolaryngology_result === 'string'
          ? JSON.parse(props.healthRecord.otolaryngology_result)
          : props.healthRecord.otolaryngology_result;
        
        if (Array.isArray(results)) {
          results.forEach(result => {
            // exam_resultから所見名を取得
            const findingName = result.exam_result || result.findings;
            if (findingName && findingNameToNumber[findingName]) {
              const number = findingNameToNumber[findingName];
              selectedFindings[number] = true;
            }
          });
        }
      } catch (error) {
        console.error('耳鼻科検診結果の読み込みエラー:', error);
      }
    }

    const formData = reactive({
      exam_date_year: '',
      exam_date_month: '',
      exam_date_day: '',
      doctor_name: '',
      other_finding: '',
      followup_disease: '',
      followup_treatment_status: '',
      followup_swimming: '',
      followup_other_treatment: '',
      followup_other_swimming: '',
      followup_visit_year: '',
      followup_visit_month: '',
      followup_visit_day: '',
      followup_clinic_name: '',
      followup_parent_name: ''
    });

    const getClassGrade = () => {
      if (props.student.school_class) {
        return `${props.student.school_class.grade}年${props.student.school_class.name}`;
      }
      return '　　　　　　　　　';
    };

    const toggleFinding = (number) => {
      selectedFindings[number] = !selectedFindings[number];
    };

    const getCheckmark = (number) => {
      return selectedFindings[number] ? '●' : '　';
    };

    const formatDate = (date) => {
      if (!date) return '　　　年　　月　　日';
      const d = new Date(date);
      return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
    };

    const getFormData = () => {
      // 選択された所見番号を配列に変換
      const selectedFindingNumbers = Object.keys(selectedFindings)
        .filter(key => selectedFindings[key])
        .map(key => parseInt(key));
      
      return {
        ...formData,
        selected_findings: selectedFindingNumbers
      };
    };

    return {
      selectedFindings,
      formData,
      getClassGrade,
      toggleFinding,
      getCheckmark,
      formatDate,
      getFormData
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

.number-circle {
  display: inline-block;
  width: 24px;
  height: 24px;
  line-height: 24px;
  text-align: center;
  border-radius: 50%;
  border: 2px solid transparent;
  transition: all 0.2s;
}

.number-circle.selected {
  border-color: #000;
  font-weight: bold;
}

.number-cell.clickable {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.2s;
}

.number-cell.clickable:hover {
  background-color: #f0f0f0;
}

.clickable-inline {
  cursor: pointer;
  user-select: none;
  padding: 2px 8px;
  border-radius: 3px;
  transition: background-color 0.2s;
  display: inline-block;
}

.clickable-inline:hover {
  background-color: #e0e0e0;
}

.number-cell-inline {
  padding: 2mm;
  font-size: 10pt;
}

.input-inline {
  border: none;
  border-bottom: 1px solid #333;
  width: 200px;
  font-size: 9.5pt;
  background: transparent;
}

.input-date {
  width: 40px;
  border: none;
  border-bottom: 1px solid #333;
  text-align: center;
  font-size: 10pt;
  background: transparent;
}

.input-doctor {
  width: 150px;
  border: none;
  border-bottom: 1px solid #333;
  font-size: 10pt;
  background: transparent;
}

.input-full {
  width: 95%;
  border: none;
  border-bottom: 1px solid #333;
  font-size: 10pt;
  background: transparent;
}

.input-med {
  width: 250px;
  border: none;
  border-bottom: 1px solid #333;
  font-size: 10pt;
  background: transparent;
}

.input-short {
  width: 100px;
  border: none;
  border-bottom: 1px solid #333;
  font-size: 9.5pt;
  background: transparent;
}

.followup-value label {
  cursor: pointer;
  user-select: none;
}

.followup-value input[type="checkbox"] {
  margin-right: 4px;
  cursor: pointer;
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

  /* 印刷時に丸囲みを確実に表示 */
  .number-circle.selected {
    border-color: #000 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* 印刷時にクリック可能な部分のホバー効果を無効化 */
  .clickable:hover,
  .clickable-inline:hover {
    background-color: transparent !important;
  }

  /* 印刷時に入力フィールドの枠線を表示 */
  .input-inline,
  .input-date,
  .input-doctor,
  .input-full,
  .input-med,
  .input-short {
    border-bottom: 1px solid #333 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
