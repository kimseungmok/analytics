// Sidebar.jsx
import React from 'react';

const Sidebar = () => {
  return (
    <aside className="sidebar">
      <div className='sidebar-logo'>
        鉄人エンタープライズ
      </div>

      <nav className='sidebar-menu'>
        <div className='upper'>
          <ul>
            <li>Dashboard</li>
            <li>Reports</li>
            <li>Users</li>
            <li>Settings</li>
          </ul>
        </div>
        <div className='middle'>
          <ul>
            <li><a href="">Dashboard</a></li>
            <li>Reports</li>
          </ul>
        </div>
        <div className='middle'>
          <ul>
            <li>Users</li>
            <li>Settings</li>
          </ul>
        </div>
      </nav>
    </aside>
  );
};

export default Sidebar;
