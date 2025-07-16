import React, { useState, useEffect, useRef } from 'react';
import { fetchSegmentTransitionSankeyData } from '@/api/gradeAnalytics';
import ChartGrid from './ChartGrid';

const SankeyDiagramPanel = () => {
  const [sankeyData, setSankeyData] = useState({ node: [], links: [] });
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const chartContainerRef = useRef(null);

  const [googleChartsLoaded, setGoogleChartsLoaded] = useState(false);

  useEffect(() => {
    if (window.google && window.google.charts) {
      setGoogleChartsLoaded(true);
    } else {
      const script = document.createElement('script');
      script.src = 'https://gstatic.com/charts/loader.js';
      script.onload = () => {
        window.google.charts.load('current', { 'packages': ['sankey'] });
        window.google.charts.setOnLoadCallback(() => setGoogleChartsLoaded(true));
      };
      script.onerror = () => {
        console.log('Failed to load Google Charts script');
        setError('チャーtポオライブラリの読み込みに失敗しました。');
      };
      document.head.appendChild(script);
    }
  }, []);

  useEffect(() => {
    const getSankeyData = async () => {
      if (!startDate || !endDate) {
        setSankeyData({ nodes: [], links: [] });
        return;
      }

      setIsLoading(true);
      setError(null);
      try {
        const data = await fetchSegmentTransitionSankeyData(startDate, endDate);
        setSankeyData(data);
      } catch (err) {
        console.log('Failed to fetch sankey data');
        setError('データの取得中にエラーが発生しました。');
      } finally {
        setIsLoading(false);
      }
    }
    getSankeyData();
  }, [startDate, endDate]);

  useEffect(() => {
    if (googleChartsLoaded && sankeyData.nodes.length > 0 && chartContainerRef.current) {
      const drawChart = () => {
        const data = new window.google.visualization.DataTable();
        data.addColumn('string', 'From');
        data.addColumn('string', 'To');
        data.addColumn('number', 'Weight');

        const nodeIdToNameMap = new Map();
        sankeyData.nodes.forEach(node => {
          nodeIdToNameMap.set(node.id, node.name);
        });

        const chartRows = sankeyData.links.map(link, [
          nodeIdToNameMap.get(link.source),
          nodeIdToNameMap.get(link.target),
          link.value
        ]);

        data.addRows(chartRows);

        const options = {
          width: '100%',
          height: 500,
          sankey: {
            node: {
              colors: ['$a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f', '#cab2d6', '#ffff99', '#1f78b4', '#33a02c'],
              lable: { fontName: 'Arial', fontSize: 10, color: '#000' }
            },
            link: {
              colorMode: 'gradient',
              colors: ['$a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f', '#cab2d6', '#ffff99', '#1f78b4', '#33a02c'],
            }
          }
        };

        const chart = new window.google.visualization.SanKey(chartContainerRef);
        chart.draw(data, options);
      };

      if (window.google && window.google.charts && window.google.charts.visualization) {
        drawChart();
      }
    }
  }, [sankeyData, googleChartsLoaded]);

  return (
    <ChartGrid title="セグメント転移サンキーダイアグラム">
      <div className="p-4">
        <div className="flex items-center space-x-4 mb-4">
          <label htmlFor="startDate" className="font-bold">開始日:</label>
          <input
            type="date"
            id="startDate"
            value={startDate}
            onChange={(e) => setStartDate(e.target.value)}
            className="border p-2 rounded"
          />
          <label htmlFor="endDate" className="font-bold">終了日:</label>
          <input
            type="date"
            id="endDate"
            value={endDate}
            onChange={(e) => setEndDate(e.target.value)}
            className="border p-2 rounded"
          />
        </div>

        {isLoading && <p>データを読込中。。。</p>}
        {error && <p className="text-red-500">{error}</p>}
        {!isLoading && !error && sankeyData.nodes.length === 0 && (startDate && endDate) && (
          <p>選択された期間のデータがありません。</p>
        )}
        {!isLoading && !error && sankeyData.nodes.length > 0 && (
          <div ref={chartContainerRef} style={{ width: '100%', height: '500px' }}>

          </div>
        )}
      </div>
    </ChartGrid>
  )
}


export default SankeyDiagramPanel;