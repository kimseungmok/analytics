// MainLayout.jsx
import React, { useEffect, useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import Header from './Header';
import Sidebar from './Sidebar';


const MainLayout = () => {
  const [theme, setTheme] = useState('light');
  const location = useLocation();

  const pageTitleMap = {
    //'/': 'Dashboard',
    '/': 'TestDashboard',
    '/Dashboard': 'Dashboard',
    '/reports': 'Reports',
    '/users': 'Users',
  }

  const pageTitle = pageTitleMap[location.pathname] || 'My Analtics';

  useEffect(() => {
    const saved = localStorage.getItem('theme');
    if(saved === 'dark'){
      setTheme('dark');
      document.documentElement.classList.add('dark');
    }
  },[]);

  const toggleTheme = () => {
    const newTheme = theme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
    localStorage.setItem('theme', newTheme);
    document.documentElement.classList.toggle('dark', newTheme === 'dark');
  };

  return (
    <div className="flex h-screen bg-gray-100 dark:bg-gray-900 dark:text-white transition-all duration-300">
      <Sidebar />
      <div className='flex flex-col flex-1'>
        <Header pageTitle={pageTitle}>
          <button
          onClick={toggleTheme}
          className="ml-auto bg-gray-300 dark:bg-gray-700 text-xs px-3 py-1 rounded hover:opacity-80 transition"
          >
            {theme === 'dark' ? 'light mode' : 'dark mode'}
          </button>
        </Header>
        <main className='p-4 overflow-auto h-screen'>
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default MainLayout;
