import React, { useState, useEffect } from "react";
import api from '../api';
import { BarChart, Bar, XAxis, YAxis, Tooltip, CartesianGrid, ResponsiveContainer } from 'recharts';
import { useFormStatus } from "react-dom";

export default function Dashboard() {
  const [date, setDate] = useState(new Date().toISOString().substr(0, 10));
  const [data, setData] = useState([]);

  useEffect(() => {
    console.log("Dashboard Loaded");
    api.get(`/segment-distribution?date=${date}`)
      .then(res => {
        const dist = res.data.distribution;
        const chartData = Object.entries(dist).map(([segment, count]) => ({
          segment,
          count: Number(count),
        }));
        setData(chartData);
      })
      .catch(err => {
        console.log(err);
      });
  }, [date]);

  return (
    <div>
      <h1>User Grade Distribution for {date}</h1>
      <input
        type="date"
        value={date}
        onChange={e => setDate(e.target.value)}
        max={new Date().toISOString().substr(0,10)}
      />
      <ResponsiveContainer width="100%" height={400}>
        <BarChart data={data} margin={{ top: 20, right: 30, bottom: 20, left: 20 }}>
          <CartesianGrid strokeDasharray="3 3"/>
          <XAxis dataKey="segment" />
          <YAxis />
          <Tooltip />
          <Bar dataKey="count" fill="#8884d8" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}