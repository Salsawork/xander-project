@extends('app')

@section('title', 'Payment Success - Xander Billiard')

@section('content')
<div class="min-h-screen bg-[#1E1E1F] text-white py-16">
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-[#2D2D2D] rounded-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-check text-white text-4xl"></i>
                </div>
            </div>

            <h1 class="text-3xl font-bold mb-4">Please Finish Your Payment</h1>
            <p class="text-gray-400 mb-8">Thank you for your purchase. Please send us your proof of payment to complete the order.</p>

            <div class="bg-[#222222] rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold mb-4 text-left">Order Details</h2>
                <div class="space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Order ID:</span>
                        <span>{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Date:</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Method:</span>
                        <span>{{ $order->payment_method === 'transfer_manual' ? 'Transfer manual' : ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Status:</span>
                        <span class="text-green-500 font-medium">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total:</span>
                        <span class="font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-center space-x-4">
                <a href="{{ route('index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-md">
                    Back to Home
                </a>
                <button onclick="showUploadModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-md">
                    Payment
                </button>

                <!-- Upload Payment Modal -->
                <div id="uploadModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4" onclick="hideUploadModalIfOutside(event)">
                    <div class="bg-neutral-800 rounded-xl max-w-3xl w-full shadow-xl overflow-hidden" onclick="event.stopPropagation()">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-3 border-b border-neutral-700 bg-neutral-800/80">
                            <h3 class="text-lg font-bold text-white">Pembayaran</h3>
                            <button onclick="hideUploadModal()" class="text-neutral-400 hover:text-white transition-colors p-1 hover:bg-neutral-700 rounded">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-4 max-h-[calc(100vh-6rem)] overflow-y-auto">
                            <div class="grid md:grid-cols-2 gap-4">
                                <!-- Left Column: Form -->
                                <div class="space-y-3">
                                    <!-- Bank Info Card -->
                                    <div class="bg-[#222222] from-blue-600 to-blue-700 rounded-lg p-3 shadow text-center">
                                        <p class="text-xs text-blue-100 mb-1 font-medium">Transfer ke rekening:</p>
                                        <div class="space-y-1">
                                            <div>
                                                <div class="font-bold text-white text-sm">Bank Syariah Indonesia (BSI)</div>
                                                <div class="flex items-baseline gap-1 mt-1 justify-center">
                                                    <span class="text-xs text-blue-100">No Rek:</span>
                                                    <span class="font-mono font-bold text-white">7575707097</span>
                                                </div>
                                                <div class="text-xs text-blue-100">A.N: BANGKIT MEMBANGUN NEGERI</div>
                                            </div>
                                        </div>
                                        <div class="border-t border-blue-500 pt-1 mt-1">
                                            <p class="text-xs text-blue-100 mb-1 font-medium">Total:</p>
                                            <p class="font-bold text-lg text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    <!-- Payment Form -->
                                    <form action="{{ route('checkout.updatePayment', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                        @method('PUT')
                                        @csrf

                                        <div class="bg-[#222222] rounded-lg p-3 space-y-2">
                                            <h6 class="font-bold text-white text-sm mb-1 flex items-center gap-1">
                                                Detail Transfer
                                            </h6>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-300 mb-1">Bank Pengirim</label>
                                                <select name="bank_id" class="w-full bg-neutral-900 border border-neutral-600 rounded px-3 py-1.5 text-white text-sm focus:ring-1 focus:ring-blue-500">
                                                    @foreach($bank as $item)
                                                    <option value="{{ $item->id_bank }}">{{ $item->nama_bank }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-300 mb-1">Nomor Rekening</label>
                                                <input type="text" name="no_rekening" placeholder="1234567890" class="w-full bg-neutral-900 border border-neutral-600 rounded px-3 py-1.5 text-white text-sm placeholder-gray-500 focus:ring-1 focus:ring-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-300 mb-1">Nama Pemilik Rekening</label>
                                                <input type="text" name="atas_nama" placeholder="Nama sesuai rekening" class="w-full bg-neutral-900 border border-neutral-600 rounded px-3 py-1.5 text-white text-sm placeholder-gray-500 focus:ring-1 focus:ring-blue-500">
                                            </div>
                                        </div>

                                        <div class="bg-[#222222] rounded-lg p-3">
                                            <h6 class="font-bold text-white text-sm mb-1 flex items-center gap-1">
                                                Bukti Transfer
                                            </h6>
                                            <div class="border border-dashed border-neutral-600 rounded p-3 bg-neutral-900 hover:border-blue-500 transition-all">
                                                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-gray-300 text-sm file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" style="display:none;">
                                                <button type="button" onclick="this.previousElementSibling.click()" class="w-full text-left text-gray-300 text-sm bg-neutral-800 hover:bg-neutral-700 rounded p-2">
                                                    <i class="fas fa-upload mr-2"></i>Pilih file…
                                                </button>
                                                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                                    <i class="fas fa-info-circle"></i>
                                                    JPG, PNG, PDF (Max 5MB)
                                                </p>
                                                <script>
                                                    document.querySelector('input[name="file"]').addEventListener('change', function(e) {
                                                        const btn = e.target.nextElementSibling;
                                                        const fileName = e.target.files[0]?.name || 'Pilih file…';
                                                        btn.innerHTML = `<i class="fas fa-file-alt mr-2"></i>${fileName}`;
                                                    });
                                                </script>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button type="button" onclick="hideUploadModal()" class="flex-1 bg-neutral-700 text-white font-semibold py-1.5 rounded border border-neutral-600 hover:bg-neutral-600 text-sm">
                                                Batal
                                            </button>
                                            <button type="submit" class="flex-1 bg-blue-600 text-white font-semibold py-1.5 rounded hover:bg-blue-700 text-sm">
                                                Kirim
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Right Column: Transfer Instructions -->
                                <div class="bg-[#222222] rounded-lg h-auto p-3 sticky top-2">
                                    <h6 class="font-bold text-white text-sm mb-2 flex items-center gap-1">
                                        Panduan Transfer
                                    </h6>

                                    <!-- Tabs -->
                                    <div class="flex bg-neutral-900 rounded p-1 mb-2 text-xs">
                                        @php
                                        $tabs = ['atm', 'mbanking', 'internet-banking'];
                                        $activeTab = request('tab', 'atm');
                                        @endphp
                                        @foreach($tabs as $tab)
                                        <button class="flex-1 py-1 px-2 font-semibold rounded transition-all {{ $activeTab === $tab ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-neutral-800' }}" onclick="switchTab('{{ $tab }}', this)">
                                            {{ $tab === 'mbanking' ? 'M-Banking' : ucfirst(str_replace('-', ' ', $tab)) }}
                                        </button>
                                        @endforeach
                                    </div>

                                    <!-- Tab Content -->
                                    <div class="tab-content text-xs text-gray-300 space-y-1 max-h-96 overflow-y-auto">
                                        <div id="atm-guide" class="{{ $activeTab === 'atm' ? '' : 'hidden' }}">
                                            <ol class="list-decimal list-inside space-y-1">
                                                <li>Masukkan kartu ATM & PIN</li>
                                                <li>Pilih menu "Transfer"</li>
                                                <li>Pilih "Transfer Antar Bank"</li>
                                                <li>Masukkan kode bank BSI (451)</li>
                                                <li>Masukkan nomor rekening: <span class="font-mono font-semibold">7575707097</span></li>
                                                <li>Masukkan nominal transfer</li>
                                                <li>Periksa detail transaksi</li>
                                                <li>Konfirmasi dan selesai</li>
                                            </ol>
                                        </div>

                                        <div id="mbanking-guide" class="{{ $activeTab === 'mbanking' ? '' : 'hidden' }}">
                                            <ol class="list-decimal list-inside space-y-1">
                                                <li>Login aplikasi M-Banking</li>
                                                <li>Pilih menu Transfer</li>
                                                <li>Pilih Bank BSI</li>
                                                <li>Masukkan No Rekening <span class="font-mono font-semibold">7575707097</span></li>
                                                <li>Masukkan nominal Rp {{ number_format($order->total, 0, ',', '.') }}</li>
                                                <li>Konfirmasi & simpan bukti transfer</li>
                                            </ol>
                                        </div>

                                        <div id="internet-banking-guide" class="{{ $activeTab === 'internet-banking' ? '' : 'hidden' }}">
                                            <ol class="list-decimal list-inside space-y-1">
                                                <li>Kunjungi laman internet banking</li>
                                                <li>Tekan tombol 'Masuk'</li>
                                                <li>Masukkan User ID dan Password</li>
                                                <li>Ketuk tombol 'Login'</li>
                                                <li>Pilih menu pembayaran atau transfer</li>
                                                <li>Pilih opsi transfer antarbank</li>
                                                <li>Pilih Bank BSI</li>
                                                <li>Isi kode bank BSI 451 diikuti nomor rekening <span class="font-mono font-semibold">7575707097</span></li>
                                                <li>Masukkan nominal transfer sesuai Total Pembayaran</li>
                                                <li>Verifikasi nomor rekening, nama penerima, dan nominal transfer</li>
                                                <li>Pilih ya/lanjut dan selesaikan transaksi</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function switchTab(tab, btn) {
                        // Hide all guides
                        document.querySelectorAll('[id$="-guide"]').forEach(el => el.classList.add('hidden'));
                        document.getElementById(tab + '-guide').classList.remove('hidden');

                        // Update button styles
                        btn.parentElement.querySelectorAll('button').forEach(b => {
                            b.classList.remove('bg-blue-600', 'text-white');
                            b.classList.add('text-gray-400');
                        });
                        btn.classList.add('bg-blue-600', 'text-white');
                        btn.classList.remove('text-gray-400');
                    }

                    function showUploadModal() {
                        document.getElementById('uploadModal').classList.remove('hidden');
                    }

                    function hideUploadModal() {
                        document.getElementById('uploadModal').classList.add('hidden');
                    }

                    function hideUploadModalIfOutside(event) {
                        if (event.target.id === 'uploadModal') {
                            hideUploadModal();
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</div>
@endsection