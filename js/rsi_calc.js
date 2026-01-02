// rsi.js
const axios = require('axios');
const { RSI } = require('technicalindicators');

(async () => {
  const symbolRaw = process.argv[2] || 'BTCEUR';
  const symbol = symbolRaw.toUpperCase();
  const interval = '1h';
  const limit = 100;

  try {
    const url = `https://api.binance.com/api/v3/klines?symbol=${symbol}&interval=${interval}&limit=${limit}`;
    console.log("Calling URL:", url);
    const response = await axios.get(url);
    const candles = response.data;
    const closes = candles.map(c => parseFloat(c[4]));

    const rsiValues = RSI.calculate({ values: closes, period: 14 });
    const currentRSI = rsiValues[rsiValues.length - 1];

    if (isNaN(currentRSI)) {
      console.error("Not enough data to calculate RSI.");
    } else {
      console.log(currentRSI.toFixed(2));
    }
  } catch (err) {
    console.error("Error:", err.message);
  }
})();