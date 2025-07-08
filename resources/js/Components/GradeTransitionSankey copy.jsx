import React from "react";
import { Sankey, Tooltip } from "recharts";

const SEGMENT_COLORS = {
  core: '#4CAF50',
  middle: '#2196F3',
  light: '#FFC107',
  dormant: '#FF5722',
  churned: '#9E9E9E',
  new: '#9C27B0',
  unknown: '#E91E63',
};

export default function GradeTransitionSankey({ links }){
  const nodeNames = Array.from(
    new Set(links.flatMap(({ source, target }) => [source, target]))
  );

  const nodes = nodeNames.map(name => ({
    name,
    color: SEGMENT_COLORS[name] || "#888"
  }));

  if(!links.length || !nodes.length) return <div>Loading Sankey...</div>;

  return (
    <div className="w-full overflow-x-auto">
      <Sankey
        width={800}
        height={400}
        data={{ nodes, links }}
        nodePadding={20}
        nodeWidth={15}
        layout="horizontal"
        link={{ stroke: '#bbb', strokeOpacity: 0.5 }}
        node={{ fill: d => d.color }}
      >
        <Tooltip />
      </Sankey>
    </div>
  );
}