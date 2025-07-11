// 📍 맨 위에 추가
import React, { useEffect, useState } from 'react';

const mockUsers = [
  { id: 1, name: '홍길동', email: 'hong@example.com', status: '활성' },
  { id: 2, name: '김영희', email: 'kim@example.com', status: '비활성' },
  { id: 3, name: '이철수', email: 'lee@example.com', status: '활성' },
  { id: 4, name: '박지수', email: 'park@example.com', status: '대기' },
];

const UserTable = () => {
  const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('전체');
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5;

  useEffect(() => {
    setCurrentPage(1);
  }, [search, statusFilter, sortConfig]);

  const filteredUsers = mockUsers
    .filter(user =>
    (user.name.toLowerCase().includes(search.toLowerCase()) ||
      user.email.toLowerCase().includes(search.toLowerCase()))
    )
    .filter(user =>
      statusFilter === '전체' ? true : user.status === statusFilter
    )
    .sort((a, b) => {
      if (!sortConfig.key) return 0;
      const aValue = a[sortConfig.key].toString().toLowerCase();
      const bValue = b[sortConfig.key].toString().toLowerCase();
      if (aValue < bValue) return sortConfig.direction === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortConfig.direction === 'asc' ? 1 : -1;
      return 0;
    });

  const handleSort = (key) => {
    let direction = 'asc';
    if (sortConfig.key === key && sortConfig.direction === 'asc') {
      direction = 'desc';
    }
    setSortConfig({ key, direction });
  };

  const renderArrow = (key) => {
    if (sortConfig.key !== key) return null;
    return sortConfig.direction === 'asc' ? '▲' : '▼';
  };

  const handleDownloadCSV = () => {
    const headers = ['이름', '이메일', '상태'];
    const rows = filteredUsers.map(user => [user.name, user.email, user.status]);

    const csvContent = [headers, ...rows]
      .map(e => e.map(val => `"${val}"`).join(','))
      .join('\n');

    // UTF-8 BOM 추가 (엑셀 일본어 깨짐 방지용)
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'users.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const paginatedUsers = filteredUsers.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
  const pageNumbers = Array.from({ length: totalPages }, (_, i) => i + 1);

  const statusOptions = ['전체', '활성', '비활성', '대기'];

  return (
    <div className="overflow-x-auto rounded-lg border mt-6 shadow bg-white dark:bg-gray-800">
      <div className="flex flex-wrap items-center justify-between p-4">
        {/* 상태 필터 버튼 */}
        <div className="flex flex-wrap gap-2 mb-2 md:mb-0">
          {statusOptions.map(option => (
            <button
              key={option}
              onClick={() => setStatusFilter(option)}
              className={`px-3 py-1 text-sm rounded border ${statusFilter === option
                  ? 'bg-blue-500 text-white'
                  : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                }`}
            >
              {option}
            </button>
          ))}
        </div>

        {/* 오른쪽 상단: 검색 + 다운로드 */}
        <div className="flex gap-2">
          <input
            type="text"
            placeholder="이름 또는 이메일 검색"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="border px-3 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
          />
          <button
            onClick={handleDownloadCSV}
            className="bg-green-500 text-white text-sm px-3 py-1 rounded hover:bg-green-600"
          >
            CSV 다운로드
          </button>
        </div>
      </div>

      {/* 테이블 */}
      <table className="min-w-full text-sm text-left">
        <thead className="bg-gray-100 dark:bg-gray-700">
          <tr>
            <th className="p-3 cursor-pointer" onClick={() => handleSort('name')}>
              이름 {renderArrow('name')}
            </th>
            <th className="p-3 cursor-pointer" onClick={() => handleSort('email')}>
              이메일 {renderArrow('email')}
            </th>
            <th className="p-3 cursor-pointer" onClick={() => handleSort('status')}>
              상태 {renderArrow('status')}
            </th>
          </tr>
        </thead>
        <tbody>
          {filteredUsers.map(user => (
            <tr key={user.id} className="border-t hover:bg-gray-50 dark:hover:bg-gray-700">
              <td className="p-3">{user.name}</td>
              <td className="p-3">{user.email}</td>
              <td className="p-3">{user.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
      
      <div className="flex justify-center mt-4 gap-2">
        {pageNumbers.map((number) => (
          <button
            key={number}
            onClick={() => setCurrentPage(number)}
            className={`px-3 py-1 text-sm rounded border ${number === currentPage
                ? 'bg-blue-500 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              }`}
          >
            {number}
          </button>
        ))}
      </div>
    </div>
  );
};

export default UserTable;
