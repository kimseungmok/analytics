export function processGradeTransitions(transitions) {
  const tableRows = [];
  const nameSet = new Set();

  const rawLinks = transitions.map(({ before_segment_name, after_segment_name, transition_count }) => {
    const source = before_segment_name || 'new';
    const target = after_segment_name || 'unknown';

    nameSet.add(source);
    nameSet.add(target);

    tableRows.push({ from: source, to: target, count: transition_count });

    return { source, target, value: transition_count };
  });

  // 사이클 제거: source === target 이거나, 동일 쌍의 중복 루프 제거
  const filteredLinks = rawLinks.filter(
    ({ source, target }) => source !== target
  );

  // 문자열 → 인덱스 매핑
  const nameList = Array.from(nameSet).sort();
  const nameToIndex = Object.fromEntries(nameList.map((name, idx) => [name, idx]));

  const sankeyNodes = nameList.map(name => ({ name }));

  const sankeyLinks = filteredLinks.map(({ source, target, value }) => ({
    source: nameToIndex[source],
    target: nameToIndex[target],
    value,
  }));

  return { sankeyLinks, sankeyNodes, tableRows };
}
