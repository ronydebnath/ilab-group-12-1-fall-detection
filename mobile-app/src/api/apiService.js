import { API_BASE_URL } from '../constants/appConstants';

// A helper function for making API requests.
const apiService = {
  /**
   * Makes an HTTP request to the specified endpoint.
   * @param {string} endpoint - The API endpoint (e.g., '/users').
   * @param {string} method - HTTP method (GET, POST, PUT, PATCH, DELETE).
   * @param {object|null} data - Data to send in the request body (for POST, PUT, PATCH).
   * @param {string|null} token - Authorization token.
   * @returns {Promise<any>} - The JSON response from the server.
   * @throws {Error} - If the network request fails or the server returns an error.
   */
  request: async (endpoint, method = 'GET', data = null, token = null) => {
    const config = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    };

    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    if (data && (method === 'POST' || method === 'PATCH' || method === 'PUT')) {
      config.body = JSON.stringify(data);
    }

    console.log(`API Request: ${method} ${API_BASE_URL}${endpoint}`, data ? `Data: ${JSON.stringify(data)}` : '');

    try {
      const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
      const contentType = response.headers.get("content-type");
      let responseData;

      if (contentType && contentType.indexOf("application/json") !== -1) {
        responseData = await response.json();
      } else {
        // Handle non-JSON responses, e.g., plain text or empty response for 204
        responseData = await response.text();
        if (response.ok && responseData === "" && response.status === 204) {
            // Handle 204 No Content specifically if needed
            console.log(`API Response: ${response.status} No Content`);
            return null; // Or an empty object/array as appropriate for the call
        }
        if (!response.ok) {
            // If not ok and not JSON, use text as error message or default
            throw new Error(responseData || `HTTP error! status: ${response.status}`);
        }
        // If it's OK but not JSON, it might be an unexpected response type
        // For now, we'll log it and return the text. Adjust as needed.
        console.warn(`API Response: ${response.status} - Received non-JSON response:`, responseData);
        return responseData;
      }
      
      console.log(`API Response: ${response.status}`, responseData);

      if (!response.ok) {
        // Try to get a message from the JSON response, otherwise use a generic error.
        const errorMessage = responseData?.message || responseData?.error || `HTTP error! status: ${response.status}`;
        throw new Error(errorMessage);
      }
      return responseData;
    } catch (error) {
      console.error('API Service Error:', error.message);
      // Add more specific error handling or re-throw for components to handle
      throw error;
    }
  },

  // --- Authentication Endpoints ---
  registerElderly: (userData) => apiService.request('/elderly/register', 'POST', userData),
  loginElderly: (credentials) => apiService.request('/elderly/login', 'POST', credentials),
  logoutElderly: (token) => apiService.request('/elderly/logout', 'POST', null, token), // Assuming backend invalidates token
  getElderlyUser: (token) => apiService.request('/elderly/user', 'GET', null, token),

  // --- Fall Event Endpoints ---
  // fallData should include elderly_profile_id and timestamp
  createFallEvent: (token, fallData) => apiService.request('/fall-events', 'POST', fallData, token),
  markFalseAlarm: (token, fallEventId) => apiService.request(`/fall-events/${fallEventId}/false-alarm`, 'PATCH', null, token),
  
  // --- Device Token Endpoint (for Push Notifications) ---
  updateDeviceToken: (token, deviceToken) => apiService.request('/device-token', 'POST', { device_token: deviceToken }, token),
};

export default apiService;