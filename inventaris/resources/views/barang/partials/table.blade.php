@forelse($barangs as $barang)
    <tr class="hover:bg-gray-50 border-b border-gray-100 transition-colors duration-150">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
            {{ $barang->kode_inventaris ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
            {{ $barang->nama_fasilitas }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $barang->kategoriBarang->nama_kategori ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $barang->ruangan->nama_ruangan }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            @if($barang->kondisi === 'Baik')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span>
                    Baik
                </span>
            @elseif($barang->kondisi === 'Kurang Baik')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full"></span>
                    Kurang Baik
                </span>
            @elseif($barang->kondisi === 'Rusak')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                    <span class="w-1.5 h-1.5 mr-1.5 bg-orange-500 rounded-full"></span>
                    Rusak
                </span>
            @elseif($barang->kondisi === 'Mati')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 mr-1.5 bg-rose-500 rounded-full"></span>
                    Mati
                </span>
            @endif
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $barang->keterangan }}">
            {{ $barang->keterangan ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
            <div class="inline-flex items-center space-x-2">
                <a href="{{ route('barang.edit', $barang->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-md transition-colors">
                    Edit
                </a>
                <form action="{{ route('barang.destroy', $barang->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-md transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-10 whitespace-nowrap text-center text-sm text-gray-500">
            <div class="flex flex-col items-center justify-center space-y-2">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tidak ada data inventaris yang ditemukan.</span>
            </div>
        </td>
    </tr>
@endforelse

@if($barangs->hasPages())
    <tr>
        <td colspan="7" class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <div class="pagination-wrapper">
                {{ $barangs->links() }}
            </div>
        </td>
    </tr>
@endif
