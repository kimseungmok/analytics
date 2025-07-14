import React, { useState, useEffect } from 'react';
import { fetchSegmentCompositionData } from '@/api/gradeAnalytics';

const AttributeCrossAnalysisPanel = ({ snapshotDate }) => {
  const [compositionData, setCompositionData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [groupBy, setGroupBy] = useState('age');

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      setCompositionData(null);

      try{
        const data = await fetchSegmentCompositionData(snapshotDate, groupBy);
        setCompositionData(data);
      } catch (error) {
        setError('属性別クロス分析データを読み込むのに失敗しました。');
        console.log(error);
      } finally {
        setLoading(false);
      }
    };

    if (snapshotDate && groupBy) {
      fetchData();
    }
  },[snapshotDate, groupBy]);

  let content;

  if(loading) {
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
    const { attribute_headers, segment_headers, data } = compositionData;
    content = (
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              {attribute_headers.map((header, index) => (
                <th key={`attr-header-${index}`} scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {header}
                </th>
              ))}
              {segment_headers.map((header, index) => (
                <th key={`seg-haeder-${header}`} scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {header}比率
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
              {data.map((row, rowIndex) => (
                <tr key={`row-${rowIndex}`}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {row.attribute_value}
                  </td>
                  {segment_headers.map((segmentName, colIndex) => (
                    <td key={`cell-${rowIndex}-${colIndex}`} className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {row[segmentName]}
                    </td>
                  ))}
                </tr>
              ))}
          </tbody>
        </table>
      </div>
    )
  }

  return(
    <div className="bg-white shadow-md rounded-lg p-6">
      <h2 className="text-xl font-semibold mb-4">
        属性別クロス分析 ({snapshotDate})
      </h2>
      <div className="mb-4">
        <label htmlFor="groupBy" className="mr-2 text-gray-700">グループ化:</label>
        <select
          id="groupBy"
          className="border border-gray-300 rounded-md p-2 text-sm"
          value={groupBy}
          onChange={(e) => setGroupBy(e.target.value)}
        >
          <option value="age">年齢</option>
          <option value="gender">性別</option>
          <option value="store">店舗別</option>
        </select>
      </div>
      {content}
    </div>
  )
}

export default AttributeCrossAnalysisPanel;