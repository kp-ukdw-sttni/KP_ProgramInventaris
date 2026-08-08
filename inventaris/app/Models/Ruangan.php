<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'nama_ruangan',
        'deskripsi',
    ];

    /**
     * Get the assets/barang located in this room.
     */
    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'ruangan_id');
    }
}
