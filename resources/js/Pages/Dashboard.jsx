// Dashboard.jsx
import React, { useState } from 'react';
import ChartGrid from '@/Components/ChartGrid';
import DashboardHeader from '@/Components/DashboardHeader';
import UserTable from '@/Components/UserTable';
import KPIPanel from '@/Components/KPIPanel';
import SegmentMigrationPanel from '@/Components/SegmentMigrationPanel';
import AttributeCrossAnalysisPanel from '@/Components/AttributeCrossAnalysisPanel';

const Dashboard = () => {
  const [currentKpiDate, setCurrentKpiDate] = useState('2025-06-01');
  const [previousKpiDate, setPreviousKpiDate] = useState('2025-05-01');
  const [panelKey, setPanelKey] = useState(0);

  const handleSearch = (currentDate, previousDate) => {
    console.log('検索期間：', previousDate, 'から', currentDate);
    setCurrentKpiDate(currentDate);
    setPreviousKpiDate(previousDate);
    setPanelKey(prevKey => prevKey + 1);
  };

  const handleExport = (start, end) => {
    console.log('CSV出力期間：', start, end);
  };

  return (
    <div className="dashboard-page space-y-4 p-1">
      <DashboardHeader onSearch={handleSearch} onExport={handleExport} />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <KPIPanel
          key={`kpi-${panelKey}`}
          currentDate={currentKpiDate}
          previousDate={previousKpiDate}
        />
        <SegmentMigrationPanel
          key={`migration-${panelKey}`}
          currentDate={currentKpiDate}
          previousDate={previousKpiDate}
        />
      </div>

      <div className="mt-6">
        <AttributeCrossAnalysisPanel
          key={`cross-analysis-${panelKey}`}
          snapshotDate={currentKpiDate}
        />
      </div>


      <div className='grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6'></div>
      <ChartGrid />
      <UserTable />
    </div>
  );
};

export default Dashboard;
