const chartConfigs = {
    BTC: { chart: null, labels: [], prices: [], canvasId: 'btcPriceChart' },
    ETH: { chart: null, labels: [], prices: [], canvasId: 'ethPriceChart' },
    DOGE: { chart: null, labels: [], prices: [], canvasId: 'dogePriceChart' },
    BNB: { chart: null, labels: [], prices: [], canvasId: 'bnbPriceChart' },
    LTC: { chart: null, labels: [], prices: [], canvasId: 'ltcPriceChart' },
    XRP: { chart: null, labels: [], prices: [], canvasId: 'xrpPriceChart' },
    SOL: { chart: null, labels: [], prices: [], canvasId: 'solPriceChart' },
    TRUMP: { chart: null, labels: [], prices: [], canvasId: 'trumpPriceChart' },
    ADA: { chart: null, labels: [], prices: [], canvasId: 'adaPriceChart' },
    TRX: { chart: null, labels: [], prices: [], canvasId: 'trxPriceChart' },
    SUI: { chart: null, labels: [], prices: [], canvasId: 'suiPriceChart' },
    LINK: { chart: null, labels: [], prices: [], canvasId: 'linkPriceChart' }
};

let currentTimeRange = '24h';
let fetchTimeout = null;
const visibleCharts = new Set();

function setTimeRange(range) {
    currentTimeRange = range;
    debounceFetch();
}

function debounceFetch(delay = 300) {
    clearTimeout(fetchTimeout);
    fetchTimeout = setTimeout(fetchPrices, delay);
}

// Downsampling function for heavy charts
function downsample(data, maxPoints = 25) {
    if (!data || data.length === 0) return [];
    const step = Math.ceil(data.length / maxPoints);
    return data.filter((_, idx) => idx % step === 0);
}

// Initialize intersection observer after DOM is loaded
function initializeObserver() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const symbol = entry.target.getAttribute('data-symbol');
            if (entry.isIntersecting) {
                visibleCharts.add(symbol);
                console.log(`Chart ${symbol} is now visible`);
            } else {
                visibleCharts.delete(symbol);
            }
        });
    });

    // Observe each canvas
    document.querySelectorAll('canvas.chart').forEach(canvas => {
        observer.observe(canvas);
        // Add all charts to visible set initially for testing
        const symbol = canvas.getAttribute('data-symbol');
        if (symbol) visibleCharts.add(symbol);
    });
    
    console.log('Observer initialized, visible charts:', visibleCharts);
}

async function fetchPrices() {
    console.log('Fetching prices...');
    try {
        const response = await fetch('back.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const prices = await response.json();
        console.log('Received prices:', prices);

        if (prices.error) {
            console.error("API Error:", prices.error);
            return;
        }

        // Update card prices
        for (const symbol in prices) {
            const card = document.getElementById(symbol);
            if (card) {
                const priceEl = card.querySelector('.price');
                if (prices[symbol]?.price) {
                    const price = parseFloat(prices[symbol].price);
                    priceEl.textContent = price.toFixed(2) + '€';
                    card.dataset.price = price;
                } else {
                    priceEl.textContent = "Price not available";
                }
            }

            const changeEl = document.getElementById(symbol + '-change');
            if (changeEl && prices[symbol]?.highPrice && prices[symbol]?.lowPrice) {
                changeEl.textContent = `${symbol}: Aukščiausia: ${parseFloat(prices[symbol].highPrice).toFixed(2)}€ Žemiausia: ${parseFloat(prices[symbol].lowPrice).toFixed(2)}€`;
            }
        }

        // Update charts
        updateCharts(prices);

    } catch (error) {
        console.error("Fetch prices failed:", error);
    }
}

function updateCharts(prices) {
    console.log('Updating charts...');
    
    for (const symbol in prices) {
        if (!chartConfigs[symbol]) {
            console.log(`No config for ${symbol}`);
            continue;
        }
        
        // For debugging, temporarily comment out this line to show all charts
        // if (!visibleCharts.has(symbol)) continue;

        const config = chartConfigs[symbol];
        let historicalPrices;

        switch (currentTimeRange) {
            case '7d':
                historicalPrices = prices[symbol].weeklyPrices || [];
                break;
            case '30d':
                historicalPrices = prices[symbol].monthlyPrices || [];
                break;
            case '24h':
            default:
                historicalPrices = prices[symbol].prices || [];
                break;
        }

        console.log(`${symbol} historical prices:`, historicalPrices);

        if (!historicalPrices || historicalPrices.length === 0) {
            console.log(`No historical prices for ${symbol}`);
            continue;
        }

        if (currentTimeRange !== '24h') {
            historicalPrices = downsample(historicalPrices, 30);
        }

        config.labels = [];
        config.prices = [];

        for (const entry of historicalPrices) {
            const date = new Date(entry.timestamp);
            const label = currentTimeRange === '24h'
                ? date.toLocaleTimeString('lt-LT', { hour: '2-digit', minute: '2-digit', hour12: false })
                : date.toLocaleDateString('lt-LT', { month: 'short', day: 'numeric' });
            config.labels.push(label);
            config.prices.push(parseFloat(entry.price));
        }

        const canvas = document.getElementById(config.canvasId);
        if (!canvas) {
            console.error(`Canvas not found: ${config.canvasId}`);
            continue;
        }

        console.log(`Creating/updating chart for ${symbol}`);

        if (!config.chart) {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded!');
                return;
            }

            const ctx = canvas.getContext('2d');
            config.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: config.labels,
                    datasets: [{
                        label: symbol,
                        data: config.prices,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            title: { display: true, text: 'Laikas' }
                        },
                        y: { 
                            title: { display: true, text: 'Kaina (€)' }
                        }
                    }
                }
            });
            console.log(`Chart created for ${symbol}`);
        } else {
            // Update existing chart
            config.chart.data.labels = config.labels;
            config.chart.data.datasets[0].data = config.prices;
            config.chart.update('none'); // Use 'none' mode for faster updates
            console.log(`Chart updated for ${symbol}`);
        }
    }
}

// Sort cards
function initializeSorting() {
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            const sortBy = this.value;
            const container = document.querySelector('.price-cards');
            const cards = Array.from(container.querySelectorAll('.card'));

            cards.sort((a, b) => {
                if (sortBy === 'name') {
                    return a.dataset.name.localeCompare(b.dataset.name);
                } else if (sortBy === 'price') {
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                }
                return 0;
            });

            cards.forEach(card => container.appendChild(card));
        });
    }
}

// Form submission handler
function initializeForms() {
    const forms = document.querySelectorAll('form.alertForm');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const messageBox = document.getElementById('messageBox');

            fetch(form.action, {
                method: form.method,
                body: formData,
            })
                .then(response => response.text())
                .then(data => {
                    messageBox.textContent = data;
                    messageBox.style.display = 'block';
                    messageBox.style.opacity = 0;
                    setTimeout(() => messageBox.style.opacity = 1, 10);
                    setTimeout(() => {
                        messageBox.style.opacity = 0;
                        setTimeout(() => messageBox.style.display = 'none', 1000);
                    }, 3000);
                })
                .catch(err => {
                    console.error('Error:', err);
                    messageBox.textContent = 'Klaida: Nepavyko nustatyti įspėjimo.';
                    messageBox.style.display = 'block';
                    messageBox.style.opacity = 1;
                });
        });
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing...');
    
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded! Make sure to include Chart.js before this script.');
        return;
    }
    
    initializeObserver();
    initializeSorting();
    initializeForms();
    
    // Initial fetch
    setTimeout(() => {
        fetchPrices();
    }, 100);
    
    // Periodic updates every 30s
    setInterval(fetchPrices, 30000);
});