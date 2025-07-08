import React, { useEffect, useState } from "react";
import axios from "axios";
import { processGradeTransitions } from "@/utils/gradeTransitionProcessor";
import GradeTransitionTable from "@/Components/GradeTransitionTable";
import GradeTransitionSankey from "@/Components/GradeTransitionSankey";

export default function GradeTransitionCompare() {
  const [rows, setRows] = useState([]);
  const [links, setLinks] = useState([]);
  const [nodes, setNodes] = useState([]);

  useEffect(() => {
  axios.get('/api/grade-transition-compare2?base_date=2025-06-16&target_date=2025-06-18')
    .then(response => {
      console.log("🟩 original :", response.data.transitions);
      const { sankeyLinks, sankeyNodes, tableRows } = processGradeTransitions(response.data.transitions);
      console.log("🟦 Sankey Links:", sankeyLinks);
      console.log("🟨 Sankey Nodes:", sankeyNodes); 
      console.log(" Table Rows:", tableRows); 

      setLinks(sankeyLinks);
      setNodes(sankeyNodes);
      setRows(tableRows);
    })
    .catch(err => {
      console.error("API FAIL:", err);
    });
}, []);

  return (
    <div className="p-4 space-y-6">
      <h1 className="text-xl font-bold">セグメント転移分析</h1>
      <GradeTransitionSankey links={links} nodes={nodes} />
      <GradeTransitionTable rows={rows} />
    </div>
  );
}
