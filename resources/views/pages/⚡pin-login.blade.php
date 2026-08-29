<?php

use Flux\Flux;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new
#[Layout('layouts::guest')]
#[Title('Entrar')]
class extends Component
{
    #[Validate('required|digits:4')]
    public string $pin = '';

    public function mount(): void
    {
        if (session()->has('admin_authed')) {
            $this->redirect(route('painel'), navigate: true);
        }
    }

    public function submit(
        #[Config('wedding.login.max_attempts')] int $maxAttempts,
        #[Config('wedding.login.decay_seconds')] int $decaySeconds,
    ): void {
        $this->validate();

        $key = 'pin-login:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->pin = '';
            Flux::toast(text: 'Muitas tentativas. Aguarde alguns minutos.', variant: 'danger');

            return;
        }

        RateLimiter::hit($key, $decaySeconds);

        if (! hash_equals((string) config('wedding.admin_pin'), $this->pin)) {
            $this->pin = '';
            Flux::toast(text: 'PIN incorreto.', variant: 'danger');

            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        session()->put('admin_authed', true);

        $this->redirect(route('painel'), navigate: true);
    }
};
?>

<div class="flex flex-col items-center gap-6 rounded-xl border border-outline-variant/50 bg-surface-container-low p-8 text-center">
    <flux:icon.heart class="size-9 text-primary" />

    <div>
        <flux:heading size="lg" class="font-display text-2xl text-primary">{{ config('app.name') }}</flux:heading>
        <flux:subheading class="mt-1">Painel dos noivos · código de 4 dígitos</flux:subheading>
    </div>

    <form wire:submit="submit" class="flex w-full flex-col gap-6">
        <flux:otp wire:model="pin" length="4" submit="auto" class="w-full justify-between" />

        @error('pin')
            <flux:text class="-mt-4 text-error">{{ $message }}</flux:text>
        @enderror

        <flux:button type="submit" variant="primary" icon:trailing="arrow-right" class="w-full justify-center">
            Entrar
        </flux:button>
    </form>
</div>
