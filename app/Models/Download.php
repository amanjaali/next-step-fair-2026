<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/** Impact reports, press-kit assets, sponsorship deck, floor plan PDF. */
class Download extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name', 'kind', 'description'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published' => 'boolean'];
    }

    public function url(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        return $this->file_path ? asset('storage/'.$this->file_path) : null;
    }
}
