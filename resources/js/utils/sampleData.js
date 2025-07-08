// 📁 sampleData.js
export const transitions = [
  {
    SNAPSHOT_DATE: "2025-06-15",
    before_segment_name: "NULL",
    after_segment_name: "core",
    transition_count: 366
  },
  {
    SNAPSHOT_DATE: "2025-06-15",
    before_segment_name: "NULL",
    after_segment_name: "middle",
    transition_count: 428
  },
  {
    SNAPSHOT_DATE: "2025-06-16",
    before_segment_name: "core",
    after_segment_name: "core",
    transition_count: 363
  },
  {
    SNAPSHOT_DATE: "2025-06-16",
    before_segment_name: "core",
    after_segment_name: "middle",
    transition_count: 200
  },
    {
    SNAPSHOT_DATE: "2025-06-16",
    before_segment_name: "core",
    after_segment_name: "light",
    transition_count: 200
  },
  {
    SNAPSHOT_DATE: "2025-06-16",
    before_segment_name: "middle",
    after_segment_name: "light",
    transition_count: 100
  },
    {
    SNAPSHOT_DATE: "2025-06-17",
    before_segment_name: "middle",
    after_segment_name: "light",
    transition_count: 100
  }
  ,
    {
    SNAPSHOT_DATE: "2025-06-15",
    before_segment_name: "core",
    after_segment_name: "light",
    transition_count: 100
  }
];


export const transitions2 = [
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "light",
    after_segment_name: "dormant",
    transition_count: 5
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "middle",
    after_segment_name: "light",
    transition_count: 5
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "light",
    after_segment_name: "middle",
    transition_count: 8
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "dormant",
    after_segment_name: "light",
    transition_count: 2
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "never",
    after_segment_name: "never",
    transition_count: 5
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "middle",
    after_segment_name: "core",
    transition_count: 7
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "dormant",
    after_segment_name: "dormant",
    transition_count: 2
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "never",
    after_segment_name: "light",
    transition_count: 1
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "light",
    after_segment_name: "light",
    transition_count: 3
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "middle",
    after_segment_name: "middle",
    transition_count: 2
  },
  {
    SNAPSHOT_DATE: "2013-12-03",
    before_segment_name: "core",
    after_segment_name: "core",
    transition_count: 1
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "light",
    after_segment_name: "dormant",
    transition_count: 1
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "middle",
    after_segment_name: "light",
    transition_count: 5
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "light",
    after_segment_name: "middle",
    transition_count: 9
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "core",
    after_segment_name: "middle",
    transition_count: 2
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "dormant",
    after_segment_name: "light",
    transition_count: 2
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "never",
    after_segment_name: "never",
    transition_count: 34792
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "middle",
    after_segment_name: "core",
    transition_count: 4
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "dormant",
    after_segment_name: "dormant",
    transition_count: 2387
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "never",
    after_segment_name: "light",
    transition_count: 4
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "light",
    after_segment_name: "light",
    transition_count: 3918
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "middle",
    after_segment_name: "middle",
    transition_count: 2195
  },
  {
    SNAPSHOT_DATE: "2013-12-04",
    before_segment_name: "core",
    after_segment_name: "core",
    transition_count: 1381
  }
];

export const transitions3 = [
  { from: "131203_light", to: "131204_dormant", value: 5 },
  { from: "131203_middle", to: "131204_light", value: 5 },
  { from: "131203_light", to: "131204_middle", value: 8 },
  { from: "131203_dormant", to: "131204_light", value: 2 },
  { from: "131203_never", to: "131204_never", value: 34794 },
  { from: "131203_middle", to: "131204_core", value: 7 },
  { from: "131203_dormant", to: "131204_dormant", value: 2384 },
  { from: "131203_never", to: "131204_light", value: 1 },
  { from: "131203_light", to: "131204_light", value: 3913 },
  { from: "131203_middle", to: "131204_middle", value: 2196 },
  { from: "131203_core", to: "131204_core", value: 1376 },
  { from: "131204_light", to: "131205_dormant", value: 1 },
  { from: "131204_middle", to: "131205_light", value: 5 },
  { from: "131204_light", to: "131205_middle", value: 9 },
  { from: "131204_core", to: "131205_middle", value: 2 },
  { from: "131204_dormant", to: "131205_light", value: 2 },
  { from: "131204_never", to: "131205_never", value: 34792 },
  { from: "131204_middle", to: "131205_core", value: 4 },
  { from: "131204_dormant", to: "131205_dormant", value: 2387 },
  { from: "131204_never", to: "131205_light", value: 4 },
  { from: "131204_light", to: "131205_light", value: 3918 },
  { from: "131204_middle", to: "131205_middle", value: 2195 },
  { from: "131204_core", to: "131205_core", value: 1381 }
];
