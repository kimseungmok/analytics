import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, Legend, CartesianGrid, ResponsiveContainer } from 'recharts';
import axios from 'axios';

export default function GradeChangeTrend(){
  const [data, setData] = useState([]);
  const [start, setStart] = useState('2025-06-15');
  const [end, setEnd] = useState('2025-06-20');

  useEffect(() => {
    axios.get(`/api/grade-change-trend?start=${start}&end=${end}`)
      .then(response => {
        const trend = response.data.trend.map(item => ({
          date : item.SNAPSHOT_DATE,
          up : Number(item.up),
          down: Number(item.down),
          new : Number(item.new),
          same : Number(item.same),
        }));
        setData(trend);
      })
      .catch(error => {
        console.error('Error fetching trend data:', error);
      });
  }, [start, end]);

  return(
    <div>
      <h2 className='text-xi font-bold mb-4'> セグメントの変化</h2>

      <div className="flex-gap-2 mb-4">
        <input
          type="date"
          value={start}
          onChange={e => setStart((e.target.value))}
          className="border rounded px-2"
        />
        <input
          type="date"
          value={end}
          onChange={e => setEnd(e.target.value)}
          className="border rounded px-2"
        />
      </div>

      <ResponsiveContainer width="100%" height={400}>
        <BarChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Bar dataKey="up" stackId="a" fill='#4caf50' name="UP" />
          <Bar dataKey="down" stackId="a" fill='#f44336' name="DOWN" />
          <Bar dataKey="new" stackId="a" fill='#2196f3' name="NEW" />
          <Bar dataKey="same" stackId="a" fill='#9e9e9e' name="SAME" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}