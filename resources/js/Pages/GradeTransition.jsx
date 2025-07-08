import React, { useEffect, useState } from 'react';
import axios from 'axios';

export default function GradeTransition() {
  const [data, setData] = useState([]);
  const [start, setStart] = useState('2025-06-15');
  const [end, setEnd] = useState('2025-06-20');

  useEffect(() => {
    axios.get(`/api/grade-transitions?start=${start}&end=${end}`)
      .then(res => {
        setData(res.data.transitions);
      })
      .catch(err => {
        console.error(err);
      });
  }, [start, end]);

  return (
    <div className="p-6">
      <h2 className='text-2xl font-bold mb-4'>セグメント転移分析</h2>

      <div className='mb-4 space-x-4'>
        <label>
          StartDate:
          <input type='date' value={start} onChange={e => setStart(e.target.value)} className="ml-2 border px-2 py-1"/>
        </label>
        <label>
          EndDate:
          <input type='date' value={end} onChange={e => setEnd(e.target.value)} className="ml-2 border px-2 py-1"/>
        </label>
      </div>

      <table className='min-w-full border border-gray-300'>
        <thead>
          <tr className='bg-gray-100'>
            <th className='border px-4 py-2'>Date</th>
            <th className='border px-4 py-2'>From</th>
            <th className='border px-4 py-2'>To</th>
            <th className='border px-4 py-2'>Count</th>
          </tr>
        </thead>
        <tbody>
          {data.map((row, idx) => (
            <tr key={idx}>
              <td className='border px-4 py-2'>{row.SNAPSHOT_DATE}</td>
              <td className='border px-4 py-2'>{row.before_segment_name}</td>
              <td className='border px-4 py-2'>{row.after_segment_name}</td>
              <td className='border px-4 py-2 text-right'>{row.transition_count.toLocaleString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}