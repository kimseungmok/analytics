export function processGradeTransitions(transitions) {
  const nodesSet = new Set();
  const linksMap = new Map();
  const rows = [];

  for (const transition of transitions) {
    const from = transition.before_segment_name ?? 'NULL';
    const to = transition.after_segment_name ?? 'NULL';
    const value = transition.count ?? transition.COUNT ?? transition.value ?? 1;
    const direction = transition.direction ?? 'same';
    const date = transition.SNAPSHOT_DATE ?? transition.date ?? null;

    if (typeof value !== 'number' || value === 0) continue;

    nodesSet.add(from);
    nodesSet.add(to);

    const key = `${from}|${to}|${direction}`;
    linksMap.set(key, (linksMap.get(key) ?? 0) + value);

    if (date) {
      rows.push({
        date,
        from,
        to,
        count: value,
        direction,
      });
    }
  }

  const nodeNameToIndex = {};
  const nodes = [...nodesSet].map((name, index) => {
    nodeNameToIndex[name] = index;
    return { id: name, name };
  });

  const links = Array.from(linksMap.entries()).map(([key, value]) => {
    const [from, to, direction] = key.split('|');
    return {
      source: nodeNameToIndex[from],
      target: nodeNameToIndex[to],
      value,
      direction,
    };
  });

  return {
    nodes,
    links,
    rows,
  };
}
