// 📁 GradeTransitionChart.jsx
import React, { useEffect, useState } from "react";
import { Chart } from "react-google-charts";
import axios from "axios";
import { convertToGoogleSankey } from "@/utils/convertToGoogleSankey";
import { transitions, transitions2, transitions3 } from "@/utils/sampleData";

export default function GradeTransitionCompare() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);


  useEffect(() => {
    axios.get(`/api/grade-transition-sankey`, {
      params: {
        start: '2013-12-01',
        end: "2014-04-15",
        interval: 1,
        interval_type: 'month',
      }
    })
      .then(response => {
        const sankey = response.data.sankey;
        console.log('sankey', sankey);
        const sankeyData = convertToGoogleSankey(sankey);
        setData(sankeyData);
      })
      .catch(error => {
        console.error('Error fetching Sankey data:', error);
      })
      .finally(() => {
        setLoading(false);
      });

    /*const datas = convertToGoogleSankey(transitions3);
    console.log(datas);
    setData(datas);*/
  }, [])


  const options = {
    sankey: {
      node: { labelPadding: 12, nodePadding: 20 },
      link: { colorMode: "source" }
    }
  };

  return (
    <div className="p-6">
      <h2 className="text-xl font-bold mb-4">セグメント転移 Sankey2</h2>
      {
        loading ? (
          <div className="text-center text-gray-500 animate-pulse">読み込み中...</div>
        ) : (
          <Chart chartType="Sankey" width="100%" height="500px" data={data} options={options} />
        )}
    </div>
  );
}
