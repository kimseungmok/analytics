import { Barchart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts';

const data = [
  { name: 'jan', sales: 4000 },
  { name: 'feb', sales: 3000 },
  { name: 'mar', sales: 5000 },
];

const BarChartBox = ({ title }) => {
  <div className='chart-box'>
    <h3>{title}</h3>
    <ResponsiveContainer width="100%" height={200}>
      <Barchart data={data}>
        <XAxis dataKey="name">
          <YAxis />
          <Tooltip />
          <Bar dataKey="sales" fill='#8884d8' />
        </XAxis>
      </Barchart>
    </ResponsiveContainer>
  </div>
}

export default BarChartBox;