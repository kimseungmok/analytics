import React from 'react';

const Sidebar = () => {
  const url = '/';
  const nowUrl = window.location.pathname.split('/').pop();

  const isActive = (target) => nowUrl === target ? 'active' : '';

  return(
    <div className="sidebar">
      <div className="system_name flex">
      <p>BizSight</p>
      </div>
      <div className='menu_content'>
        <div className='upper'>
          <ul>
            <li>
              <a href={`${url}`} className={isActive('index.php')}>
                <i className='fas fa-tachometer-alt'></i>ダッシュボード
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  )
}