// 📁 resources/js/__mock__/sampleTransitions.js

export const sampleTransitions = [
  // 신규 유입
  { before_segment_name: null, after_segment_name: 'light', transition_count: 2000 },
  { before_segment_name: null, after_segment_name: 'middle', transition_count: 1000 },
  { before_segment_name: null, after_segment_name: 'core', transition_count: 300 },

  // light → 다른 등급
  { before_segment_name: 'light', after_segment_name: 'core', transition_count: 1200 },
  { before_segment_name: 'light', after_segment_name: 'light', transition_count: 1200 },
  { before_segment_name: 'light', after_segment_name: 'middle', transition_count: 400 },
  { before_segment_name: 'light', after_segment_name: 'dormant', transition_count: 100 },

  // middle → 다른 등급
  { before_segment_name: 'middle', after_segment_name: 'middle', transition_count: 800 },
  { before_segment_name: 'middle', after_segment_name: 'core', transition_count: 150 },
  { before_segment_name: 'middle', after_segment_name: 'dormant', transition_count: 100 },

  // core → 유지 or 하락
  { before_segment_name: 'core', after_segment_name: 'core', transition_count: 250 },
  { before_segment_name: 'core', after_segment_name: 'middle', transition_count: 100 },
  { before_segment_name: 'core', after_segment_name: 'light', transition_count: 100 },

  // dormant → 이탈
  { before_segment_name: 'dormant', after_segment_name: 'dormant', transition_count: 400 },
  { before_segment_name: 'dormant', after_segment_name: 'churned', transition_count: 100 },

  // churned → 유지
  { before_segment_name: 'churned', after_segment_name: 'churned', transition_count: 1000 }
];
