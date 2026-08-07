<?php

namespace App\Services;

use Illuminate\Support\Str;

class ParticipantIdentity
{
    public function normalize(string $name): string
    {
        return Str::lower(Str::ascii($this->clean($name)));
    }

    public function clean(string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', $name));
    }
}
