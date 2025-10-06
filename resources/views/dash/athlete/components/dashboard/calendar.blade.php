<div class="flex flex-col w-full">
    <div class="flex justify-between items-center mb-4">
        <button id="prevMonth" class="text-gray-400 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <p class="text-lg font-semibold" id="currentMonth">February 2025</p>
        <button id="nextMonth" class="text-gray-400 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    
    <div class="grid grid-cols-7 gap-4 mb-2 text-center text-xs">
        <div class="text-gray-400">S</div>
        <div class="text-gray-400">M</div>
        <div class="text-gray-400">T</div>
        <div class="text-gray-400">W</div>
        <div class="text-gray-400">T</div>
        <div class="text-gray-400">F</div>
        <div class="text-gray-400">S</div>
    </div>
    
    <div id="calendarDays" class="grid grid-cols-7 gap-4 text-center text-xs">
        <!-- Kalender akan di-render oleh JavaScript -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentDate = new Date(2025, 1); // February 2025 (month is 0-indexed in JS)
        const eventDates = [1, 5, 7]; // Tanggal dengan jadwal (hardcoded)
        
        // Render kalender saat halaman dimuat
        renderCalendar(currentDate);
        
        // Event listener untuk tombol previous
        document.getElementById('prevMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });
        
        // Event listener untuk tombol next
        document.getElementById('nextMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });
        
        // Fungsi untuk render kalender
        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            
            // Update judul bulan
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            // Bersihkan kalender
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Dapatkan tanggal awal bulan
            const firstDay = new Date(year, month, 1);
            const startingDay = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
            
            // Dapatkan jumlah hari dalam bulan
            const lastDay = new Date(year, month + 1, 0);
            const totalDays = lastDay.getDate();
            
            // Dapatkan tanggal akhir bulan sebelumnya
            const prevMonthLastDay = new Date(year, month, 0).getDate();
            
            // Render tanggal-tanggal bulan sebelumnya
            for (let i = startingDay - 1; i >= 0; i--) {
                const dayElem = document.createElement('div');
                dayElem.classList.add('py-2', 'text-gray-400');
                dayElem.textContent = prevMonthLastDay - i;
                calendarDays.appendChild(dayElem);
            }
            
            // Render tanggal-tanggal bulan saat ini
            for (let i = 1; i <= totalDays; i++) {
                const dayElem = document.createElement('div');
                dayElem.classList.add('py-1');
                
                // Cek apakah tanggal ini punya event
                if (eventDates.includes(i)) {
                    dayElem.classList.add('bg-blue-500', 'rounded-full', 'text-white');
                }
                
                dayElem.textContent = i;
                calendarDays.appendChild(dayElem);
            }
            
            // Render tanggal-tanggal bulan selanjutnya
            const totalCells = 42; // 6 baris x 7 kolom
            const remainingCells = totalCells - (startingDay + totalDays);
            
            for (let i = 1; i <= remainingCells; i++) {
                const dayElem = document.createElement('div');
                dayElem.classList.add('py-1', 'text-gray-400');
                dayElem.textContent = i;
                calendarDays.appendChild(dayElem);
            }
        }
    });
</script>
