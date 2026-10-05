<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $family_id
 * @property string $name
 * @property string $name_normalized
 * @property string|null $phone
 * @property Carbon|null $invite_sent_at
 * @property bool|null $is_attending
 * @property Carbon|null $responded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Family $family
 */
#[Fillable(['name', 'phone', 'invite_sent_at', 'is_attending', 'responded_at'])]
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    /**
     * Normalize a name for accent- and case-insensitive search.
     */
    public static function normalize(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }

    /**
     * Link do WhatsApp com a mensagem do convite e o link assinado deste convidado.
     */
    public function whatsappInviteUrl(): ?string
    {
        if ($this->phone === null) {
            return null;
        }

        $inviteUrl = URL::signedRoute('convite', ['convidado' => $this->id]);
        $message = <<<TEXT
            Olá!

            Com o coração cheio de alegria, finalmente chegou o momento de compartilhar com vocês o nosso *convite oficial de casamento!* Será uma grande felicidade poder contar com a presença de vocês para celebrar conosco esse dia tão especial e esperado. ✨

            Nosso convite é digital e conta com *ícones clicáveis* que facilitarão o acesso a todas as informações importantes: a localização da igreja e da recepção, a confirmação de presença e também a nossa lista de presentes.

            Esperamos vocês para, juntos, celebrarmos o amor, a nossa história e o início de uma nova etapa das nossas vidas!

            Com muito carinho,
            Davi & Aline 🤍

            {$inviteUrl}
            TEXT;

        return "https://wa.me/{$this->phone}?text=".rawurlencode($message);
    }

    /**
     * @return BelongsTo<Family, $this>
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Keep the normalized name column in sync with the name.
     *
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): array => [
                'name' => $value,
                'name_normalized' => self::normalize($value),
            ],
        );
    }

    /**
     * Guarda só os dígitos, com DDI 55 quando vier só DDD + número.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): ?string {
                $digits = preg_replace('/\D/', '', (string) $value);

                if ($digits === '') {
                    return null;
                }

                return strlen($digits) <= 11 ? '55'.$digits : $digits;
            },
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_attending' => 'boolean',
            'responded_at' => 'datetime',
            'invite_sent_at' => 'datetime',
        ];
    }
}
