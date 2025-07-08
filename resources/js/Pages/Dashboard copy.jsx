// Dashboard.jsx
import React from 'react';
import ChartGrid from '@/Components/ChartGrid';
import StatBox from '@/Components/StatBox';
import DashboardHeader from '@/Components/DashboardHeader';

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
      <div className='grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6'>
        <StatBox title="Total Users" value="3,456" icon={<FaUsers />} />
        <StatBox title="New Signups" value="123" icon={<FaChartLine />} />
        <StatBox title="Revenue" value="$12,345" icon={<FaDollarSign />} />
        <StatBox title="Orders" value="789" icon={<FaShoppingCart />} />
      </div>
      <ChartGrid />
    </div>
  );
};

export default Dashboard;
