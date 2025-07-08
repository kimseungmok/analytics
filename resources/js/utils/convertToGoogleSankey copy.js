export function convertToGoogleSankey(transitions) {
  const rows = [["From", "To", "Value"]];

  useEffect

  transitions.forEach(t => {
    rows.push(t.from, t.to, Number(t.value));
  })

  return rows;
}
