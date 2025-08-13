import React, { useState, useEffect } from 'react';
import { fetchSegmentMigrationData } from '@/api/gradeAnalytics';

const SegmentMigrationPanel = ({ currentDate, previousDate, selectedBranches }) => {
  const [migrationData, setMigrationData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const data = await fetchSegmentMigrationData(currentDate, previousDate, selectedBranches );
        setMigrationData(data);
      } catch (err) {
        setError('セグメント移転データを読み込むのに失敗しました。');
      } finally {
        setLoading(false);
      }
    };

    if (currentDate && previousDate) {
      fetchData();
    }
  }, [currentDate, previousDate]);

  const getMigrationIndicator = (value, prevSegmentId, currSegmentId) => {
    if (prevSegmentId === 'total' || currSegmentId === 'total' || prevSegmentId === 'other_branch' || currSegmentId === 'other_branch') {
      return;
    } else if (Number(value) === 0) {
      return <span className="text-gray-500 mr-1">→</span>;
    } else if (prevSegmentId === 'new') {
      return <span className="text-green-500 mr-1">▲</span>;
    } else if (currSegmentId < prevSegmentId) {
      return <span className="text-green-500 mr-1">▲</span>;
    } else if (currSegmentId > prevSegmentId) {
      return <span className="text-red-500 mr-1">▼</span>;
    }else {
      return <span className="text-blue-500 mr-1">●</span>;
    }
  };

  if (loading) {
    return (
      <div className="bg-white shadow-md rounded-lg p-4 text-center text-xs">
        セグメント遷移データローディング中...
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white shadow-md rounded-lg p-4 text-center text-red-600 text-xs">
        {error}
      </div>
    );
  }

  if (!migrationData || !migrationData.matrix_data || migrationData.matrix_data.length === 0) {
    return (
      <div className="bg-white shadow-md rounded-lg p-4 text-center text-xs">
        表示するセグメント遷移データがありません。
      </div>
    );
  }

  const { row_headers, col_headers, matrix_data } = migrationData;

  return (
    <div className="bg-white shadow-md rounded-lg p-4">
      <h2 className="text-lg font-semibold mb-3">
        
        セグメント遷移チャート T1({previousDate}) → T2({currentDate})
      </h2>
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200 text-xs">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                T1/T2
              </th>
              {col_headers.map((header, index) => (
                  <th key={index} scope="col" className={`px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider ${index === (col_headers.length -1) ? 'font-bold' : ''}`}>
                    {header.name}
                  </th>
              ))}
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {row_headers.map((rowHeader, rowIndex) => (
              <tr key={rowIndex} className={`${rowIndex === (row_headers.length -1) ? 'font-bold' : ''}`}>
                <td className="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                  {rowHeader.name}
                </td>
                {matrix_data[rowIndex] && matrix_data[rowIndex].map((value, colIndex) => (
                    col_headers[colIndex] ? (
                      <td key={colIndex} className={`px-4 py-2 whitespace-nowrap text-sm text-gray-500 ${colIndex === (col_headers.length -1) ? 'font-bold' : ''}`}>
                        {getMigrationIndicator(value, rowHeader.id, col_headers[colIndex].id)}
                        {value.toLocaleString()}
                      </td>
                    ) : null
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default SegmentMigrationPanel;
