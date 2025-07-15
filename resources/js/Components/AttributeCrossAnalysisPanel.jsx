import React, { useState, useEffect, useMemo } from 'react';
import { fetchSegmentCompositionData } from '@/api/gradeAnalytics';

const AttributeCrossAnalysisPanel = ({ snapshotDate }) => {
  const [compositionData, setCompositionData] = useState(null);
  const [headers, setHeaders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [sortColumn, setSortColumn] = useState(null);
  const [sortDirection, setSortDirection] = useState('asc'); // 'asc' or 'desc'

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      setCompositionData(null);
      setHeaders([]);
      setSortColumn(null); // Reset sort column on new data fetch
      setSortDirection('asc'); // Reset sort direction

      try {
        const data = await fetchSegmentCompositionData(snapshotDate);
        setCompositionData(data);
        setHeaders(data.headers);

        // Set the first header as the default sort column if data exists
        if (data.headers.length > 0) {
          setSortColumn(data.headers[0]);
        }
      } catch (error) {
        setError('属性別クロス分析データを読み込むのに失敗しました。');
        console.log(error);
      } finally {
        setLoading(false);
      }
    };

    if (snapshotDate) {
      fetchData();
    }
  }, [snapshotDate]);

  const handleHeaderClick = (header) => {
    if (sortColumn === header) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortColumn(header);
      setSortDirection('asc'); // Default to ascending when changing sort column
    }
  };

  // Helper function to render sort arrow
  const renderSortArrow = (header) => {
    if (sortColumn !== header) return null;
    return sortDirection === 'asc' ? ' ▲' : ' ▼';
  };

  const sortedDisplayData = useMemo(() => {
    if (!compositionData || !compositionData.data || !sortColumn) {
      return [];
    }

    const dataToSort = [...compositionData.data]; // Create a shallow copy to sort

    dataToSort.sort((a, b) => {
      let valA = a[sortColumn];
      let valB = b[sortColumn];

      // Custom parsing for percentage strings and general numbers
      const parseValue = (value) => {
        if (typeof value === 'string') {
          if (value.endsWith('%')) {
            return parseFloat(value.replace('%', '')) || 0;
          }
          const num = parseFloat(value);
          if (!isNaN(num) && isFinite(num)) {
            return num;
          }
        }
        return value; // Return as is if not a string or not a parseable number
      };

      valA = parseValue(valA);
      valB = parseValue(valB);

      // Numeric comparison
      if (typeof valA === 'number' && typeof valB === 'number') {
        if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
      }

      // Fallback to string comparison for non-numeric or mixed types
      const strA = String(valA).toLowerCase();
      const strB = String(valB).toLowerCase();

      if (strA < strB) return sortDirection === 'asc' ? -1 : 1;
      if (strA > strB) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });

    return dataToSort;
  }, [compositionData, sortColumn, sortDirection]);

  let content;

  if (loading) {
    content = (
      <div className="text-center py-8">
        属性別クロス分析データローディング中...
      </div>
    );
  } else if (error) {
    content = (
      <div className="text-center py-8 text-red-600">
        {error}
      </div>
    );
  } else if (!compositionData || !compositionData.data || compositionData.data.length === 0) {
    content = (
      <div className="text-center py-8">
        表示する属性別クロス分析データがありません。
      </div>
    );
  } else {
    // Use sortedDisplayData instead of compositionData.data
    const displayData = sortedDisplayData;

    content = (
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              {/* 통합된 headers 배열을 사용하여 모든 헤더 동적 렌더링 */}
              {headers.map((header, index) => (
                <th
                  key={`header-${index}`}
                  scope="col"
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" // Added cursor-pointer
                  onClick={() => handleHeaderClick(header)} // Added onClick handler
                >
                  {header}{renderSortArrow(header)} {/* Added sort arrow */}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {displayData.map((row, rowIndex) => ( // Changed to displayData
              <tr key={`row-${rowIndex}`}>
                {/* 통합된 headers 배열을 사용하여 모든 데이터 셀 동적 렌더링 */}
                {headers.map((header, colIndex) => (
                  <td key={`cell-${rowIndex}-${colIndex}`} className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {/* row 객체의 키가 헤더 이름과 직접 일치하므로 바로 접근 */}
                    {row[header]}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    )
  }

  return (
    <div className="bg-white shadow-md rounded-lg p-6">
      <h2 className="text-xl font-semibold mb-4">
        属性別クロス分析 ({snapshotDate})
      </h2>
      {content}
    </div>
  )
}

export default AttributeCrossAnalysisPanel;