<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }


    public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/default.jpg');
        if ($this->banner) {
            if (str_starts_with($this->banner, 'http://') || str_starts_with($this->banner, 'https://')) {
                $thumbnail = $this->banner;
            } elseif (Storage::exists($this->banner)) {
                $thumbnail = Storage::url($this->banner);
            } elseif (file_exists(public_path('storage/' . $this->banner))) {
                $thumbnail = asset('storage/' . $this->banner);
            } elseif (file_exists(public_path($this->banner))) {
                $thumbnail = asset($this->banner);
            }
        }

        return Attribute::make(
            get: fn () => $thumbnail
        );
    }

}
