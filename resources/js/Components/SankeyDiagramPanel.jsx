// resources/js/Components/SankeyDiagramPanel.jsx

import React, { useState, useEffect, useRef } from 'react';
import { fetchSegmentTransitionSankeyData } from '../api/gradeAnalytics';
import ChartGrid from './ChartGrid';

// 1. 세그먼트별 색상 맵 정의 - 일본어 이름으로 변경
const SEGMENT_COLOR_MAP = {
  'コア': '#4682B4',    // core
  'ライト': '#32CD32',   // light
  'ミドル': '#8A2BE2',  // middle
  '休眠': '#DAA520', // dormant
  '離反': '#FF6347', // churned
  '非コンバージョンユーザー': '#808080',   // never (백엔드 translationMap에 추가된 '非コンバージョンユーザー'에 맞춰 추가)
  // 필요에 따라 더 많은 세그먼트와 색상을 추가할 수 있습니다.
};

const SankeyDiagramPanel = ({ startDate, endDate }) => {
  const [sankeyData, setSankeyData] = useState({ nodes: [], links: [] });
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const chartContainerRef = useRef(null);

  const [googleChartsLoaded, setGoogleChartsLoaded] = useState(false);

  useEffect(() => {
    console.log('useEffect: Google Charts 로드 시도');
    if (window.google && window.google.charts) {
      console.log('Google Charts 이미 로드됨.');
      waitForVisualizationReady();
    } else {
      const script = document.createElement('script');
      script.src = 'https://www.gstatic.com/charts/loader.js';
      script.onload = () => {
        console.log('Google Charts 스크립트 로드 완료.');
        window.google.charts.load('current', { packages: ['sankey'] });
        window.google.charts.setOnLoadCallback(() => {
          console.log('Google Charts 패키지 로드 및 콜백 실행 완료.');
          waitForVisualizationReady();
        });
      };
      script.onerror = () => {
        console.error('Google Charts 스크립트 로드 실패');
        setError('チャートライブラリの読み込みに失敗しました。');
      };
      document.head.appendChild(script);
    }

    function waitForVisualizationReady(retryCount = 0) {
      if (window.google.visualization?.Sankey) {
        console.log('Google Sankey 로딩 완료됨.');
        setGoogleChartsLoaded(true);
      } else if (retryCount < 10) {
        console.log('Google Sankey 아직 준비 안됨. 재시도 중...', retryCount);
        setTimeout(() => waitForVisualizationReady(retryCount + 1), 200);
      } else {
        console.error('Google Sankey 로딩 실패 - 10회 시도 후 중단');
        setError('チャートの初期化に失敗しました。');
      }
    }
  }, []);

  useEffect(() => {
    const getSankeyData = async () => {
      console.log('useEffect: 데이터 가져오기 시도. startDate:', startDate, 'endDate:', endDate);
      if (!startDate || !endDate) {
        console.log('startDate 또는 endDate가 없어 데이터 가져오기 건너뜀.');
        setSankeyData({ nodes: [], links: [] });
        return;
      }

      setIsLoading(true);
      setError(null);
      try {
        const data = await fetchSegmentTransitionSankeyData(startDate, endDate);
        console.log('데이터 가져오기 성공:', data);
        setSankeyData(data);
      } catch (err) {
        setError('データの取得中にエラーが発生しました。');
        console.error('Failed to fetch Sankey data:', err);
      } finally {
        setIsLoading(false);
      }
    };

    getSankeyData();
  }, [startDate, endDate]);

  useEffect(() => {
    console.log('useEffect: 차트 그리기 시도. googleChartsLoaded:', googleChartsLoaded, 'sankeyData:', sankeyData, 'chartContainerRef.current:', chartContainerRef.current);
    if (googleChartsLoaded && sankeyData.nodes.length > 0 && chartContainerRef.current) {
      const drawChart = () => {
        console.log('drawChart 함수 실행 중...');
        const data = new window.google.visualization.DataTable();
        data.addColumn('string', 'From');
        data.addColumn('string', 'To');
        data.addColumn('number', 'Weight');

        const nodeIdToNameMap = new Map();
        sankeyData.nodes.forEach(node => {
          nodeIdToNameMap.set(node.id, node.name);
        });

        const chartRows = sankeyData.links.map(link => {
          const value = Number(link.value);
          return [
            nodeIdToNameMap.get(link.source),
            nodeIdToNameMap.get(link.target),
            isNaN(value) ? 0 : value
          ];
        });

        data.addRows(chartRows);
        console.log('Google Charts DataTable에 데이터 추가 완료. Rows:', chartRows.length);

        // 2. 노드 색상 동적 생성
        const nodeColorsArray = sankeyData.nodes.map(node => {
          // 노드 이름에서 세그먼트 이름 추출 (예: "離反 (2025-05-01)" -> "離反")
          // 괄호 '(' 또는 공백 ' '이 나오기 전까지의 문자열을 추출하도록 정규식 수정
          const segmentNameMatch = node.name.match(/^([^\s\(]+)/);
          const segmentName = segmentNameMatch ? segmentNameMatch[1] : 'default'; // 매칭 실패 시 'default' 사용

          // 정의된 색상 맵에서 색상 가져오기, 없으면 회색 기본값
          return SEGMENT_COLOR_MAP[segmentName] || '#CCCCCC';
        });

        const options = {
          width: '100%',
          height: 500,
          sankey: {
            node: {
              colors: nodeColorsArray, // 동적으로 생성된 세그먼트별 색상 적용
              label: { fontName: 'Arial', fontSize: 10, color: '#000' }
            },
            link: {
              colorMode: 'source', // 링크는 시작 노드의 색상을 따름
              // 'colors' 배열은 colorMode가 'gradient'일 때 주로 사용됩니다.
              // 'source' 모드에서는 별도로 지정할 필요가 없습니다.
            }
          }
        };

        try {
          const chart = new window.google.visualization.Sankey(chartContainerRef.current);
          chart.draw(data, options);
          console.log('Sankey 차트 그리기 명령 실행 완료.');
        } catch (e) {
          console.error('Sankey 차트 그리기 중 오류 발생:', e);
        }
      };

      // Google Charts가 완전히 로드되었는지 다시 확인 후 그리기
      if (window.google && window.google.visualization && window.google.visualization.Sankey) {
        drawChart();
      } else {
        console.log('Google Charts visualization 객체가 아직 준비되지 않음.');
      }
    } else {
      console.log('차트 그리기 조건 미충족:', { googleChartsLoaded, sankeyDataNodesLength: sankeyData.nodes.length, chartContainerRefCurrent: chartContainerRef.current });
    }
  }, [sankeyData, googleChartsLoaded]);

  return (
    <ChartGrid title={`セグメント遷移サンキーダイアグラム (${startDate} ~ ${endDate})`}>
      <div className="p-4">
        {isLoading && <p>データを読み込み中...</p>}
        {error && <p className="text-red-500">{error}</p>}
        {!isLoading && !error && sankeyData.nodes.length === 0 && (startDate && endDate) && (
          <p>選択された期間のデータがありません。</p>
        )}
        {!isLoading && !error && sankeyData.nodes.length > 0 && (
          <div ref={chartContainerRef} style={{ width: '100%', height: '500px' }}>
            {/* Sankey チャートがここにレンダリングされます。 */}
          </div>
        )}
      </div>
    </ChartGrid>
  );
};

export default SankeyDiagramPanel;