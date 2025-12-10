<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentPool extends Model
{
    use HasFactory;

    // Membuka kunci agar semua kolom bisa diisi massal via Seeder
    protected $guarded = [];
}
