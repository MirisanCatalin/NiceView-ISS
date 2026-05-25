$(document).ready(function() {
    var uploadsCtx = document.getElementById('uploadsChart').getContext('2d');
    var uploadsChart = new Chart(uploadsCtx, {
        type: 'bar',
        data: {
            labels: ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Numar uploaduri',
                data: [12, 19, 8, 15, 22, 18, 25, 20, 16, 14, 9, 11],
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Număr uploaduri'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Luna'
                    }
                }
            }
        }
    });

    var gaussianCtx = document.getElementById('gaussianChart').getContext('2d');
    
    function generateGaussianData(mean, stdDev, numPoints) {
        var data = [];
        for (var i = 0; i < numPoints; i++) {
            var x = i;
            var y = (1 / (stdDev * Math.sqrt(2 * Math.PI))) * 
                      Math.exp(-0.5 * Math.pow((x - mean) / stdDev, 2));
            data.push(y);
        }
        return data;
    }

    var gaussianData = generateGaussianData(2.5, 1, 10);
    
} 
