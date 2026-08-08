<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'ruangan_id',
        'kategori_id',
        'nama_fasilitas',
        'jumlah',
        'kode_inventaris',
        'kondisi',
        'keterangan',
    ];

    /**
     * Get the room (ruangan) where the asset is located.
     */
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    /**
     * Get the category (kategori_barang) of the asset.
     */
    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }
}
