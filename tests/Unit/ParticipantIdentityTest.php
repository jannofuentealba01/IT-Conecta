<?php

namespace Tests\Unit;

use App\Services\ParticipantIdentity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParticipantIdentityTest extends TestCase
{
    public static function names(): array
    {
        return [
            'espacios' => ['  Ana   Pérez  ', 'ana perez'],
            'mayúsculas' => ['ALEJANDRO FUENTEALBA', 'alejandro fuentealba'],
            'acentos' => ['María-José Ñanco', 'maria-jose nanco'],
        ];
    }

    #[DataProvider('names')]
    public function test_it_normalizes_equivalent_names(string $input, string $expected): void
    {
        $this->assertSame($expected, (new ParticipantIdentity)->normalize($input));
    }

    public function test_it_preserves_a_clean_display_name(): void
    {
        $this->assertSame('María José', (new ParticipantIdentity)->clean('  María   José '));
    }
}
