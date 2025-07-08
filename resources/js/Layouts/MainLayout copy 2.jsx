// MainLayout.jsx
import React, { useState } from 'react';
import Header from './Header';
import Sidebar from './Sidebar';
import { Outlet } from 'react-router-dom';

const MainLayout = () => {
  const [theme, setTheme] = useState('light');

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
    <div className="app-container">
      <Sidebar />
      <div className='right-section'>
        <Header />
        <main className='main-content'>
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default MainLayout;
