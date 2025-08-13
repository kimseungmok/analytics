// Updated SankeyDiagramPanel.jsx with "新規 (YYYY-MM-DD)" 신규유저 노드 처리 추가
import React, { useState, useEffect, useRef } from 'react';
import { fetchSegmentTransitionSankeyData } from '../api/gradeAnalytics';
import ChartGrid from './ChartGrid';

const SEGMENT_COLOR_MAP = {
  '新規': '#00CED1', // 신규 유저 색상 추가
  'コア': '#4682B4',
  'ミドル': '#8A2BE2',
  'ライト': '#32CD32',
  '休眠': '#DAA520',
  '離反': '#FF6347',
  '非コンバージョンユーザー': '#808080',
};

const SankeyDiagramPanel = ({ startDate, endDate, selectedBranches }) => {
  const [sankeyData, setSankeyData] = useState({ nodes: [], links: [] });
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const chartContainerRef = useRef(null);

  const [googleChartsLoaded, setGoogleChartsLoaded] = useState(false);

  const [segmentVisibility, setSegmentVisibility] = useState({
    '新規': true, // 신규 노드 기본 표시
    'コア': true,
    'ミドル': true,
    'ライト': true,
    '休眠': true,
    '離反': false,
    '非コンバージョンユーザー': false,
  });

  const handleToggleSegment = (segmentName) => {
    setSegmentVisibility(prev => ({ ...prev, [segmentName]: !prev[segmentName] }));
  };

  useEffect(() => {
    if (window.google && window.google.charts) {
      waitForVisualizationReady();
    } else {
      const script = document.createElement('script');
      script.src = 'https://www.gstatic.com/charts/loader.js';
      script.onload = () => {
        window.google.charts.load('current', { packages: ['sankey'] });
        window.google.charts.setOnLoadCallback(() => {
          waitForVisualizationReady();
        });
      };
      script.onerror = () => {
        setError('チャートライブラリの読み込みに失敗しました。');
      };
      document.head.appendChild(script);
    }

    function waitForVisualizationReady(retryCount = 0) {
      if (window.google.visualization?.Sankey) {
        setGoogleChartsLoaded(true);
      } else if (retryCount < 10) {
        setTimeout(() => waitForVisualizationReady(retryCount + 1), 200);
      } else {
        setError('チャートの初期化に失敗しました。');
      }
    }
  }, []);

  useEffect(() => {
    const getSankeyData = async () => {
      if (!startDate || !endDate) return;
      setIsLoading(true);
      setError(null);
      try {
        const data = await fetchSegmentTransitionSankeyData(startDate, endDate, selectedBranches);
        setSankeyData(data);
      } catch (err) {
        setError('データの取得中にエラーが発生しました。');
      } finally {
        setIsLoading(false);
      }
    };
    getSankeyData();
  }, [startDate, endDate]);

  useEffect(() => {
    if (!googleChartsLoaded || sankeyData.nodes.length === 0 || !chartContainerRef.current) return;

    const visibleSegmentNames = Object.keys(segmentVisibility).filter(name => segmentVisibility[name]);
    const filteredNodes = sankeyData.nodes.filter(node => {
      const nameMatch = node.name.match(/^([^\s\(]+)/);
      const segmentName = nameMatch ? nameMatch[1] : 'default';
      return visibleSegmentNames.includes(segmentName);
    });

    const visibleNodeIdToNameMap = new Map();
    filteredNodes.forEach(node => visibleNodeIdToNameMap.set(node.id, node.name));

    const filteredLinks = sankeyData.links.filter(link =>
      visibleNodeIdToNameMap.has(link.source) && visibleNodeIdToNameMap.has(link.target)
    );

    if (filteredLinks.length === 0 && filteredNodes.length === 0) {
      chartContainerRef.current.innerHTML = '<p>表示するデータがありません。</p>';
      return;
    }

    const data = new window.google.visualization.DataTable();
    data.addColumn('string', 'From');
    data.addColumn('string', 'To');
    data.addColumn('number', 'Weight');

    const getShortDateLabel = (fullName) => {
      const match = fullName.match(/^([^\s$]+)\s\((\d{4}-\d{2}-\d{2})$/);
      if (match) {
        const segment = match[1];
        const date = new Date(match[2]);
        return `${segment} (${String(date.getFullYear() % 100).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')})`;
      }
      return fullName;
    };

    data.addRows(filteredLinks.map(link => [
      getShortDateLabel(visibleNodeIdToNameMap.get(link.source)),
      getShortDateLabel(visibleNodeIdToNameMap.get(link.target)),
      Number(link.value)
    ]));

    const nodeColorsArray = filteredNodes.map(node => {
      const match = node.name.match(/^([^\s\(]+)/);
      const name = match ? match[1] : 'default';
      return SEGMENT_COLOR_MAP[name] || '#CCCCCC';
    });

    const options = {
      width: '100%',
      height: 500,
      sankey: {
        node: {
          colors: nodeColorsArray,
          label: { fontName: 'Arial', fontSize: 10, color: '#000' }
        },
        link: {
          colorMode: 'source'
        }
      }
    };

    try {
      const chart = new window.google.visualization.Sankey(chartContainerRef.current);
      chart.draw(data, options);
    } catch (e) {
      setError('チャートの描画中にエラーが発生しました。');
    }
  }, [sankeyData, googleChartsLoaded, segmentVisibility]);

  return (
    <ChartGrid title={`セグメント遷移サンキーダイアグラム (${startDate} ~ ${endDate})`}>
      <div className="p-2">
        <div className="mb-2 flex flex-wrap gap-2">
          {Object.keys(SEGMENT_COLOR_MAP).map(segmentName => (
            <button
              key={segmentName}
              onClick={() => handleToggleSegment(segmentName)}
              className={`px-2 py-1 rounded-md text-xs font-medium transition-colors duration-200
                ${segmentVisibility[segmentName] ? 'text-white hover:opacity-90' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'}`}
              style={{ backgroundColor: segmentVisibility[segmentName] ? SEGMENT_COLOR_MAP[segmentName] : undefined }}
            >
              {segmentName}
            </button>
          ))}
        </div>

        {isLoading && <p>データを読み込み中...</p>}
        {error && <p className="text-red-500">{error}</p>}
        {!isLoading && !error && sankeyData.nodes.length === 0 && startDate && endDate && (
          <p>選択された期間のデータがありません。</p>
        )}
        {!isLoading && !error && sankeyData.nodes.length > 0 && (
          <div ref={chartContainerRef} style={{ width: '100%', height: '500px' }}></div>
        )}
      </div>
    </ChartGrid>
  );
};

export default SankeyDiagramPanel;
