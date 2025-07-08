// Header.jsx
import React from 'react';

const Header = ({ pageTitle, children }) => {
  return (
    <header className="flex justify-between items-center bg-white dark:bg-gray-800 shadow px-4 py-2">
      <h1 className='text-lg font-bold'>{pageTitle}</h1>
      <div className='flex items-center gap-2'>
        {children}
      </div>
    </header>
  );
};

export default Header;
