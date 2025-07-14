import React, { useState, useEffect } from "react";
import { fetchKPIComparisonData } from "@/api/gradeAnalytics";


const KPIPanel = ({ currentDate, previousDate }) => {
  const [kpiData, setKpiData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try{
        const data = await fetchKPIComparisonData(currentDate, previousDate);
        setKpiData(data);
      } catch(err){
        setError('fail to get kpi data');
        console.error(err);
      } finally{
        setLoading(false);
      }
    };

    if(currentDate && previousDate){
      fetchData();
    }
  }, [currentDate, previousDate]);

  if(loading) {
    return  <div className="bg-white shadow-md rounded-lg p-6 text-center">全体KPIデータローディング中...</div>;
  }

  if (error) {
    return <div className="bg-white shadow-md rounded-lg p-6 text-center text-red-600">{error}</div>;
  }

  const getChangeIndicator = (changeType) => {
    switch (changeType) {
      case 'positive':
        return <span className="text-green-500">▲</span>
      case 'negative':
        return <span className="text-red-500">▼</span>
      case 'neutral':
        return <span className="text-gray-500">→</span>
      default:
        return null;
    }
  };

  return(
    <div className="bg-white shadow-md rounded-lg p-6">
      <h2 className="text-xl font-semibold mb-4">全体KPIパネル ({previousDate} vs {currentDate})</h2>
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">指標名</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数値</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">前月比</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備考</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {kpiData.map((kpi, index) => (
              <tr key={index}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{kpi.metric}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{kpi.value}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{getChangeIndicator(kpi.changeType)} {kpi.change}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{kpi.note}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

export default KPIPanel;