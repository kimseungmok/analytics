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
            <li><a href="/">Dashboard</a></li>
            <li><a href="/reports">Reports</a></li>
            <li>Users</li>
            <li>Settings</li>
          </ul>
        </div>
        <div className='middle'>
          <ul>
            <li><a href="/">Dashboard</a></li>
            <li><a href="/reports">Reports</a></li>
          </ul>
        </div>
        <div className='middle'>
          <ul>
            <li><a href="/Users">Users</a></li>
            <li>Settings</li>
          </ul>
        </div>
      </nav>
    </aside>
  );
};

export default Sidebar;
