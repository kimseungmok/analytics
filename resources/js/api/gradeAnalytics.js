import axios from "axios";

export const fetchKPIComparisonData = async (currentDate, previousDate) => {
  try{
    const response = await axios.get('/api/kpi-comparison', {
      params: {
        current_date: currentDate,
        previous_date: previousDate,
      },
    });
    return response.data.data;
  }
  catch (error){
    console.error('Error fetching KPI comparison data:', error);
    throw error;
  }
};