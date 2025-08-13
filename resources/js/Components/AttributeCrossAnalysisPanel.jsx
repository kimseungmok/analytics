import React, { useState, useEffect, useMemo } from 'react';
import clsx from 'clsx';
import { fetchSegmentCompositionData } from '@/api/gradeAnalytics';

const AttributeCrossAnalysisPanel = ({ snapshotDate, selectedBranches }) => {
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
        const data = await fetchSegmentCompositionData(snapshotDate, selectedBranches);
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
      <div className="text-center py-4 text-xs">
        属性別クロス分析データローディング中...
      </div>
    );
  } else if (error) {
    content = (
      <div className="text-center py-4 text-red-600 text-xs">
        {error}
      </div>
    );
  } else if (!compositionData || !compositionData.data || compositionData.data.length === 0) {
    content = (
      <div className="text-center py-4 text-xs">
        表示する属性別クロス分析データがありません。
      </div>
    );
  } else {
    // Use sortedDisplayData instead of compositionData.data
    const displayData = sortedDisplayData;

    content = (
      <div className="overflow-x-auto">
        <table className="min-w-full table-fixed text-xs">
          <thead className="bg-gray-50">
            <tr>
              {/* 동적으로 헤더 렌더링 */}
              {headers.map((header, index) => (
                <th
                  key={`header-${index}`}
                  scope="col"
                  className={clsx(
                    "px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider cursor-pointer truncate",
                    header === "非コンバージョンユーザー" ? "w-32" : "w-auto"
                  )}
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
                {/* 동적으로 데이터 셀 렌더링 */}
                {headers.map((header, colIndex) => (
                  <td key={`cell-${rowIndex}-${colIndex}`} className="px-4 py-2 text-sm font-medium text-gray-900 truncate">
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
    <div className="bg-white shadow-md rounded-lg p-4">
      <h2 className="text-lg font-semibold mb-4">
        属性別クロス分析 ({snapshotDate})
      </h2>
      {content}
    </div>
  );
}

export default AttributeCrossAnalysisPanel;
