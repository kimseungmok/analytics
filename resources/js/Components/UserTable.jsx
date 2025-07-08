import React, { useState } from "react";
import { FaExclamation } from "react-icons/fa";

const initialUsers = [
  { name: '홍길동', email: 'hong@example.com', joined: '2025-06-20', status: 'Active' },
  { name: '이영희', email: 'lee@example.com', joined: '2025-06-18', status: 'Pending' },
  { name: '김철수', email: 'kim@example.com', joined: '2025-06-15', status: 'Suspended' },
  { name: '박민수', email: 'park@example.com', joined: '2025-06-12', status: 'Active' },
];

const UserTable = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('All');
  const [sortKey, setSortKey] = useState('name');
  const [sortOrder, setSortOrder] = useState('asc');
  const [selectedUser, setSelectedUser] = useState(null);
  const [showModal, setShowModal] = useState(false);
  
  const handleSort = key => {
    if (sortKey === key) {
      setSortOrder(prev => (prev === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortKey(key);
      setSortOrder('asc');
    }
  }

  const filteredUsers = [...initialUsers].filter(u =>
    `${u.name} ${u.email}`.toLowerCase().includes(searchQuery.toLowerCase())
  ).filter(u => statusFilter === 'All' || u.status === statusFilter);

  const sortedUsers = [...filteredUsers].sort((a, b) => {
    const valA = a[sortKey].toLowerCase?.() || a[sortKey];
    const valB = b[sortKey].toLowerCase?.() || b[sortKey];

    if (valA < valB) return sortOrder === 'asc' ? -1 : 1;
    if (valA > valB) return sortOrder === 'asc' ? 1 : -1;
    return 0;
  });

  const renderSortIcon = key => {
    if (key !== sortKey) return '';
    return sortOrder === 'asc' ? '▲' : '▼';
  };

  return (
    <div className="bg-white p-4 rounded-xl shadow">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 mb-4 gap-2">
        <h3 className="text-lg font-semibold mb-4">User Segment</h3>
        <div className="flex flex-col sm:flex-row gap-2">
          <input
            type="text"
            placeholder="search name or email"
            className="border rounded px-2 py-1 text-sm"
            value={searchQuery}
            onChange={e => setSearchQuery(e.target.value)}
          />
          <select
            className="border rounded px-2 py-1 text-sm"
            value={statusFilter}
            onChange={e => setStatusFilter(e.target.value)}
          >
            <option value="All">All</option>
            <option value="Active">Active</option>
            <option value="Pending">Pending</option>
            <option value="Suspended">Suspended</option>
          </select>
          <button
            onClick={() => {
              const today = new Date().toISOString().split('T')[0];
              exportToCsv(sortedUsers, `users_${today}.csv`);
            }}
            className="bg-blue-600 text-white text-sm px-3 py-1 rounded hover:bg-blue-700 transition"
          >
            CSV Download
          </button>
        </div>
      </div>
      <div className="overflow-x-auto">
        <table className="min-w-full text-sm border-collapse">
          <thead>
            <tr className="bg-gray-100 text-left cursor-pointer">
              <th className="p-2 border-b" onClick={() => handleSort('name')}>name{renderSortIcon('name')}</th>
              <th className="p-2 border-b" onClick={() => handleSort('email')}>email{renderSortIcon('email')}</th>
              <th className="p-2 border-b" onClick={() => handleSort('joined')}>joined{renderSortIcon('joined')}</th>
              <th className="p-2 border-b">status</th>
              <th className="p-2 border-b">action</th>
            </tr>
          </thead>
          <tbody>
            {sortedUsers.length > 0 ? (
            sortedUsers.map((u, i) => (
              <tr key={i} className="hover:bg-gray-50">
                <td className="p-2 border-b">{u.name}</td>
                <td className="p-2 border-b">{u.email}</td>
                <td className="p-2 border-b">{u.joined}</td>
                <td className="p-2 border-b">
                  <span
                    className={`px-2 py-1 rounded-full text-xs font-semibold
                       ${u.status === 'Active' ? 'bg-green-100 text-green-700'
                        : u.status === 'Pending' ? 'bg-yellow-100 text-yellow-700'
                          : 'bg-red-100 text-red-700'
                      }`}
                  >
                    {u.status}
                  </span>
                </td>
                <td className="p-2 border-b">
                  <button className="text-indigo-600 hover:underline text-xs"
                    onClick={() => {
                      setSelectedUser(u);
                      setShowModal(true);
                    }}
                  >View</button>
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="5" className="p-4 text-center text-gray-500">
                no search data
              </td>
            </tr>
          )}
          </tbody>
        </table>
      </div>
      {showModal && selectedUser && (
        <div className="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg w-96 p-6 relative ainmate-fade-in">
            <button
              onClick={() => {setShowModal(false)}}
              className="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
                ✕
              </button>
              <h2 className="text-lg font-semibold mb-4">User Information</h2>
              <div className="text-sm space-y-2">
                <div>
                  <span className="font-semibold">name: </span>
                  {selectedUser.name}
                </div>
                <div>
                  <span className="font-semibold">email: </span>
                  {selectedUser.email}
                </div>
                <div>
                  <span className="font-semibold">joined: </span>
                  {selectedUser.joined}
                </div>
                <div>
                  <span className="font-semibold">status: </span>
                  <span
                    className="{`px-2 py-1 rounded-full text-xs font-semibold ${
                      selectedUser.status === 'Active'
                      ? 'bg-green-100 text-green-700'
                      : selectedUser.status === 'Pending'
                      ? 'bg-yellow-100 text-yellow-700'
                      : 'bg-red-100 text-red-700'
                    }`}"
                    >
                      {selectedUser.status}
                    </span>
                </div>
              </div>
          </div>
        </div>
      )}
    </div>
  )
}

const exportToCsv = (data, filename = 'users.csv') => {
  const headers = ['name', 'email', 'joined', 'status'];
  const rows = data.map(u => [u.name, u.email, u.joined, u.status]);

  let csvContent = '\uFEFF'; //UTF-8 BOM
  csvContent += headers.join(',') + '\n';
  csvContent += rows.map(row => row.join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', filename);
  link.click();
};

export default UserTable;