/**
 * Fetches weather information for NSW (Sydney as a proxy).
 * This is a MOCK implementation. Replace with a real weather API call.
 * @returns {Promise<object>} Weather data (e.g., { temp: '22°C', description: 'Sunny', city: 'Sydney, NSW' })
 */
export const fetchWeatherNSW = async () => {
    console.log("WeatherService: Fetching weather for NSW (mock)...");
    try {
      // Simulating network delay with a Promise and setTimeout
      return new Promise(resolve => {
          setTimeout(() => {
              const mockWeatherData = { 
                temp: '18°C', // Typical Sydney weather around this time of year (May)
                description: 'Partly Cloudy', 
                city: 'Sydney, NSW' 
              };
              console.log("WeatherService: Mock weather data loaded.", mockWeatherData);
              resolve(mockWeatherData);
          }, 1200); // Simulate 1.2 second delay
      });
  
    } catch (error) {
      console.error("WeatherService: Failed to fetch weather:", error);
      return { temp: 'N/A', description: 'Weather data unavailable', city: 'NSW' }; // Fallback data
    }
  };