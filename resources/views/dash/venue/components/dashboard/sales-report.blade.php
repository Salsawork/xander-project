    <div class="flex justify-between items-center mb-2">
        <span class="text-white text-lg font-semibold">Sales Report</span>
        <span class="text-white text-2xl cursor-pointer">...</span>
    </div>
    <hr class="border-gray-600 mb-4">
    <div id="salesReportChart"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var options = {
        chart: {
            type: 'line',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
        },
        series: [{
            name: 'Sales',
            data: @json($salesData)
        }],
        xaxis: {
            categories: ['JAN', '', '', 'FEB', '', '', 'MAR', '', '', 'APR', '', '', 'MAY', '', '', 'JUN'],
            labels: {
                style: { colors: '#fff', fontWeight: 700, fontSize: '16px' }
            }
        },
        yaxis: {
            min: 0,
            max: 45,
            tickAmount: 5,
            labels: {
                style: { colors: '#ccc' }
            }
        },
        stroke: {
            curve: 'straight',
            width: 3,
            colors: ['#3b82f6']
        },
        grid: {
            borderColor: '#444',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } }
        },
        markers: { size: 0 },
        colors: ['#3b82f6'],
        tooltip: { theme: 'dark' }
    };

    document.addEventListener('DOMContentLoaded', function () {
        new ApexCharts(document.querySelector("#salesReportChart"), options).render();
    });
</script>
@endpush