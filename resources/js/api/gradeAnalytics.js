import axios from "axios";

const API_BASE_URL = '/api';

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

export const fetchSegmentCompositionData = async (snapshotDate) => {
  try{
    const response = await axios.get('/api/segment-composition', {
      params: {
        snapshot_date: snapshotDate,
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching segment composition data:', error);
    throw error;
  }
};

export const fetchSegmentTransitionSankeyData = async (startDate, endDate) => {
  try {
    //const response = await axios.get(`${API_BASE_URL}/analytics/segment-transition`, {
    const response = await axios.get(`${API_BASE_URL}/segment-transition`, {
      params : {
        start_date : startDate,
        end_date: endDate,
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching segment composition data:', error);
    throw error;
  }
};