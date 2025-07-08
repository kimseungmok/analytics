export function convertToGoogleSankey(transitions) {
  const rows = [["From", "To", "Value"]];
  console.log('transitions',transitions);

  transitions.forEach(t => {
    const from = t.from ?? "NULL";
    const to = t.to ?? "NULL";

    const rawValue = Number(t.value);
    if (!rawValue || rawValue <= 0) return;

    rows.push([from, to, rawValue]);
    console.log(from,to,rawValue);
  });

  return rows;
}

export function convertToGoogleSankey2(transitions) {
  const rows = [["From", "To", "Value"]];
  console.log('transitions',transitions);

  // 날짜를 정렬해서 다음 날짜로만 흐르도록 구성
  const dateOrder = [...new Set(transitions.map(t => t.SNAPSHOT_DATE))].sort();
  console.log(dateOrder);

  transitions.forEach(t => {
    console.log(t.SNAPSHOT_DATE);
    const dateIndex = dateOrder.indexOf(t.SNAPSHOT_DATE);
    console.log(dateIndex);
    if (dateIndex === -1 || dateIndex === dateOrder.length - 1) return; // 마지막 날짜는 다음이 없으므로 제외

    const nextDate = dateOrder[dateIndex + 1];

    const from = `${t.SNAPSHOT_DATE}-${t.before_segment_name ?? "NULL"}`;
    const to = `${nextDate}-${t.after_segment_name ?? "NULL"}`;

    const rawValue = Number(t.transition_count);
    if (!rawValue || rawValue <= 0) return;

    rows.push([from, to, rawValue]);
    console.log(from,to,rawValue);
  });

  return rows;
}
