import React from "react";
import BarChartBox from "./BarChartBox";
import PieChartBox from "./PieChartBox";
import LineChartBox from "./LineChartBox";

const ChartGrid = () => {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-4">
      <BarChartBox />
      <PieChartBox />
      <LineChartBox />
    </div>
  )
}
/*
grid               // CSS Grid 사용
grid-cols-1        // 기본: 1열
md:grid-cols-2     // medium(768px 이상): 2열
xl:grid-cols-3     // xl(1280px 이상): 3열
gap-6              // 차트 간 여백
p-4                // 안쪽 패딩
*/
export default ChartGrid;