// Dashboard.jsx
import React from 'react';
import ChartGrid from '@/Components/ChartGrid';
import StatBox from '@/Components/StatBox';
import DashboardHeader from '@/Components/DashboardHeader';
import UserTable from '@/Components/UserTable';

import { FaUsers, FaChartLine, FaDollarSign, FaShoppingCart } from 'react-icons/fa';

const Dashboard = () => {

  const handleSearch = (start, end) => {
    console.log('検索期間：',start,'から',end);
  };

  const handleExport = (start, end) => {
    console.log('CSV出力期間：', start, end);
  };

  return (
    <div className="dashboard-page space-y-6 p-1">
      <DashboardHeader onSearch={handleSearch} onExport={handleExport} />
      <div className='grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6'></div>
      <ChartGrid />
      <UserTable />
    </div>
  );
};

export default Dashboard;
