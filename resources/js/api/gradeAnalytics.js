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

export const fetchSegmentMigrationData = async (currentDate, previousDate) => {
  try {
    const response = await axios.get('/api/segment-migration', {
      params: {
        current_date : currentDate,
        previous_date : previousDate,
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching segment migration data:', error);
    throw error;
  }
};

export const fetchSegmentCompositionData = async (snapshotDate, groupBy) => {
  try{
    const response = await axios.get('/api/segment-composition', {
      params: {
        snapshot_date: snapshotDate,
        group_by: groupBy,
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching segment composition data:', error);
    throw error;
  }
};