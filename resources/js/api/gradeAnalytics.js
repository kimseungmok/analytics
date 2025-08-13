//gradeAnalytics.js
import axios from "axios";

const API_BASE_URL = '/api';

export const fetchKPIComparisonData = async (currentDate, previousDate, selectedBranches) => {
  const reqData = {
    current_date: currentDate,
    previous_date: previousDate,
    selected_branches: selectedBranches,
  };

  try {
    const response = await axios.post('/api/kpi-comparison', reqData,
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });
    return response.data.data;
  }
  catch (error) {
    console.error('Error fetching KPI comparison data:', error);
    throw error;
  }
};

export const fetchSegmentMigrationData = async (currentDate, previousDate, selectedBranches) => {
  const reqData = {
    current_date: currentDate,
    previous_date: previousDate,
    selected_branches: selectedBranches,
  };

  try {
    const response = await axios.post('/api/segment-migration', reqData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });
    return response.data.data;
  } catch (error) {
    console.error('Error fetching segment migration data:', error);
    throw error;
  }
};

export const fetchSegmentCompositionData = async (snapshotDate, selectedBranches) => {
  const reqData = {
    snapshot_date: snapshotDate,
    selected_branches: selectedBranches,
  };

  try {
    const response = await axios.post('/api/segment-composition', reqData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });
    return response.data.data;
  } catch (error) {
    console.error('Error fetching segment composition data:', error);
    throw error;
  }
};

export const fetchSegmentTransitionSankeyData = async (startDate, endDate, selectedBranches) => {

  const reqData = {
    start_date: startDate,
    end_date: endDate,
    selected_branches: selectedBranches,
  };

  try {
    //const response = await axios.get(`${API_BASE_URL}/analytics/segment-transition`, {
    const response = await axios.post(`${API_BASE_URL}/segment-transition`, reqData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching segment composition data:', error);
    throw error;
  }
};

export const fetchSegmentSummaryData = async (snapshotDate, selectedBranches) => {

  const reqData = {
    snapshot_date: snapshotDate,
    selected_branches: selectedBranches,
  };

  try {
    const response = await axios.post(`${API_BASE_URL}/segment-summary`, reqData, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
    });
    return response.data.data;
  } catch (error) {
    console.error('Error fetching segment summary data: ', error);
    throw error;
  }
};

export const fetchBranchList = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/branches`);
    return response.data;
  } catch (error) {
    console.error('Error fetching segment summary data: ', error);
    throw error;
  }
}