// Header.jsx
import React from 'react';
import { useLocation } from 'react-router-dom';

const Header = ( pageTitle, children ) => {

  const location = useLocation();

  const getTitle = () => {
    switch (location.pathname) {
      case '/':
        return 'Dashboard';
      case '/reports':
        return 'Reports';
      case '/users':
        return 'Users';
      case '/settings':
        return 'Settings';
      default: 
        return 'My Analytics';
    }
  }

  return (
    <header className="header">
      <h2 className="page-title">{getTitle()}</h2>
    </header>
  );
};

export default Header;
