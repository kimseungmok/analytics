import React from "react";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";

const data = [
  { date: '7/01', users: 240 },
  { date: '7/02', users: 320 },
  { date: '7/03', users: 290 },
  { date: '7/04', users: 500 },
  { date: '7/05', users: 380 },
  { date: '7/06', users: 460 },
  { date: '7/07', users: 390 },
];

const LineChartBox = () => {
  return (
    <div className="chart-box">
      <h3 className="chart-title">Users</h3>
      <ResponsiveContainer width="100%" height={300}>
        <LineChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
            <YAxis />
            <Tooltip />
            <Line type="monotone" dataKey="users" stroke="#10b981" strokeWidth={3} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}

export default LineChartBox;