import React from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer } from 'recharts';

const data = [
  { name: 'Chrome', value: 68 },
  { name: 'Safari', value: 18 },
  { name: 'Firefox', value: 10 },
  { name: 'Edge', value: 4 },
];

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'];

const PieChartBox = () => {
  return (
    <div className='bg-white p-4 rounded-xl shadow'>
      <h3 className='text-lg font-semibold mb-2'>Browser</h3>
      <ResponsiveContainer width="100%" height={300}>
        <PieChart>
          <Pie
            data={data}
            dataKey="value"
            nameKey="name"
            outerRadius={100}
            fill='#8884d8'
            label>
              {data.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
              ))}
            </Pie>
            <Tooltip />
        </PieChart>
      </ResponsiveContainer>
    </div>
  );
};

export default PieChartBox;