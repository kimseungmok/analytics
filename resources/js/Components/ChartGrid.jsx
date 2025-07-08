import React from "react";
import BarChartBox from "./BarChartBox";
import PieChartBox from "./PieChartBox";
import StatBox from "./StatBox";
import { FaUsers, FaShoppingCart, FaDollarSign, FaChartLine } from 'react-icons/fa';

const ChartGrid = () => {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
      <div>
        <BarChartBox />
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <StatBox title="New Users" value="3,456" icon={<FaUsers />} />
        <StatBox title="New Orders" value="123" icon={<FaChartLine />} />
        <StatBox title="Revenue Today" value="$12,345" icon={<FaDollarSign />} />
        <StatBox title="Growth Rate" value="789" icon={<FaShoppingCart />} />
      </div>

      <div>
        <PieChartBox />
      </div>
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