import React, { useState, useEffect } from "react";
import { fetchKPIComparisonData } from "@/api/gradeAnalytics";

const KPIPanel = ({ currentDate, previousDate, selectedBranches }) => {
  const [kpiData, setKpiData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const data = await fetchKPIComparisonData(currentDate, previousDate, selectedBranches);
        setKpiData(data);
      } catch (err) {
        setError('fail to get kpi data');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    if (currentDate && previousDate) {
      fetchData();
    }
  }, [currentDate, previousDate]);

  if (loading) {
    return <div className="bg-white shadow-md rounded-lg p-4 text-center text-xs">全体KPIデータローディング中...</div>;
  }

  if (error) {
    return <div className="bg-white shadow-md rounded-lg p-4 text-center text-red-600 text-xs">{error}</div>;
  }

  const getChangeIndicator = (changeType) => {
    switch (changeType) {
      case 'positive':
        return <span className="text-green-500">▲</span>;
      case 'negative':
        return <span className="text-red-500">▼</span>;
      case 'neutral':
        return <span className="text-blue-500">●</span>;
      default:
        return null;
    }
  };

  return (
    <div className="bg-white shadow-md rounded-lg p-4">
      <h2 className="text-lg font-semibold mb-4">全体KPIパネル T1({previousDate}) vs T2({currentDate})</h2>
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200 text-xs">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">指標名</th>
              <th scope="col" className="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">数値</th>
              <th scope="col" className="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">比較</th>
              <th scope="col" className="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">備考</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {kpiData.map((kpi, index) => (
              <tr key={index}>
                <td className="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{kpi.metric}</td>
                <td className="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{kpi.value}</td>
                <td className="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{getChangeIndicator(kpi.changeType)} {kpi.change}</td>
                <td className="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{kpi.note}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default KPIPanel;
