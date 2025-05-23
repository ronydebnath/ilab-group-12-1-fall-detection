/**
 * Fetches weather information for NSW (Sydney as a proxy).
 * This is a MOCK implementation. Replace with a real weather API call.
 * @returns {Promise<object>} Weather data (e.g., { temp: '22°C', description: 'Sunny', city: 'Sydney, NSW' })
 */
export const fetchWeatherNSW = async () => {
    console.log("WeatherService: Fetching weather for NSW (mock)...");
    try {
      // --- MOCK IMPLEMENTATION ---
      // In a real app, you would use a service like OpenWeatherMap, AccuWeather, etc.
      // Example:
      // const API_KEY = 'YOUR_OPENWEATHERMAP_API_KEY';
      // const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=Sydney,AU&appid=${API_KEY}&units=metric`);
      // if (!response.ok) throw new Error('Failed to fetch weather data from API');
      // const data = await response.json();
      // return { 
      //   temp: `${Math.round(data.main.temp)}°C`, 
      //   description: data.weather[0].description.charAt(0).toUpperCase() + data.weather[0].description.slice(1), 
      //   city: data.name 
      // };
  
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