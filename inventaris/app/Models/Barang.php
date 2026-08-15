<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'ruangan_id',
        'kategori_id',
        'nama_fasilitas',
        'kode_inventaris',
        'tahun_pembelian',
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

    /**
     * Build the kode prefix for a room, e.g. "INV-RUANGKETUA".
     */
    public static function kodePrefix(Ruangan $ruangan): string
    {
        $nama = Str::upper(Str::replaceMatches('/[^A-Za-z0-9]/', '', $ruangan->nama_ruangan));

        return 'INV-' . $nama;
    }

    /**
     * Format a kode from a prefix and a running sequence number.
     */
    public static function formatKode(string $prefix, int $seq): string
    {
        return $prefix . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Determine the next sequence number for a room based on existing codes.
     */
    public static function nextSequence(Ruangan $ruangan): int
    {
        $prefix = static::kodePrefix($ruangan);

        $maxSeq = 0;
        foreach (static::where('kode_inventaris', 'like', $prefix . '%')->pluck('kode_inventaris') as $code) {
            $suffix = Str::afterLast($code, '-');
            if (ctype_digit($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        return $maxSeq + 1;
    }

    /**
     * Generate the next unique inventory code for a room.
     */
    public static function generateKodeInventaris(Ruangan $ruangan): string
    {
        return static::formatKode(static::kodePrefix($ruangan), static::nextSequence($ruangan));
    }
}
