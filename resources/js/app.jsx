// resources/js/app.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import MainLayout from './Layouts/MainLayout';
import Dashboard from './Pages/Dashboard';
import Reports from './Pages/Reports';
import Users from './Pages/Users';
import TestDashboard from './Pages/TestDashboard/TestDashboard';
import '../css/app.css';

const App = () => {

  return (
    <BrowserRouter>
      <Routes>
        <Route path='/' element={<Navigate to="/test" replace />} />

        <Route element={<MainLayout />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/reports" element={<Reports />} />
          <Route path="/users" element={<Users />} />
          <Route path="/test" element={<TestDashboard />} />
        </Route>a
      </Routes>
    </BrowserRouter>
  );
};

const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(<App />);
