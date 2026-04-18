<?php

namespace App\Domains\Auth\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

/**
 * Página de login personalizada con throttle:
 *   - Máximo 5 intentos fallidos
 *   - Bloqueo de 10 minutos (600 segundos)
 */
class Login extends BaseLogin
{
    use WithRateLimiting;

    /** Mensaje de error general que se muestra como banner sobre el formulario. */
    public ?string $loginError = null;

    /** Máximo de intentos antes de bloquear. */
    protected int $maxAttempts = 5;

    /** Segundos de bloqueo tras superar el límite (10 minutos). */
    protected int $decaySeconds = 600;

    /**
     * Sobreescribe el formulario para insertar el banner de error
     * en la parte superior, antes de los campos.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('error_banner')
                    ->label('')
                    ->content(fn () => new HtmlString(
                        '<div style="border-radius:0.5rem;border:1px solid #fca5a5;background-color:#fff1f2;padding:0.75rem 1rem;font-size:0.875rem;font-weight:500;display:flex;align-items:center;gap:0.5rem;">'
                        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#dc2626" style="width:1.25rem;height:1.25rem;flex-shrink:0;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>'
                        . '<span style="color:#dc2626;">' . e($this->loginError) . '</span>'
                        . '</div>'
                    ))
                    ->hidden(fn () => empty($this->loginError)),

                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    public function authenticate(): LoginResponse
    {
        // Resetea el banner en cada nuevo intento
        $this->loginError = null;

        try {
            $this->rateLimit($this->maxAttempts, $this->decaySeconds);
        } catch (TooManyRequestsException $exception) {
            $seconds = $exception->secondsUntilAvailable;
            if ($seconds < 60) {
                $timeMessage = "menos de 1 minuto ({$seconds} segundo(s))";
            } else {
                $minutes = (int) floor($seconds / 60);
                $remainingSeconds = $seconds % 60;
                $timeMessage = $remainingSeconds > 0
                    ? "{$minutes} minuto(s) y {$remainingSeconds} segundo(s)"
                    : "{$minutes} minuto(s)";
            }

            // Mostrar el toast solo una vez por bloqueo (no acumular notificaciones)
            if (! session()->has('throttle_notified')) {
                session()->put('throttle_notified', true);
                Notification::make()
                    ->title('Demasiados intentos fallidos')
                    ->body("Has superado el límite de {$this->maxAttempts} intentos. Tu cuenta ha sido bloqueada temporalmente. Inténtalo de nuevo en {$timeMessage}.")
                    ->danger()
                    ->duration(10000)
                    ->send();
            }

            $this->loginError = "Demasiados intentos fallidos. Inténtalo de nuevo en {$timeMessage}.";
            throw ValidationException::withMessages(['authentication' => ' ']);
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $currentAttempts = session('login_failed_attempts', 0) + 1;
            session()->put('login_failed_attempts', $currentAttempts);
            $remaining = max(0, $this->maxAttempts - $currentAttempts);

            if ($remaining > 0) {
                $plural = $remaining === 1 ? 'intento' : 'intentos';
                $this->loginError = "Las credenciales no coinciden con los registros del sistema. Te quedan {$remaining} {$plural} antes de ser bloqueado temporalmente.";
            } else {
                $this->loginError = "Las credenciales no coinciden con los registros del sistema. ¡Atención! Tu cuenta será bloqueada en el próximo intento fallido.";
            }
            throw ValidationException::withMessages(['authentication' => ' ']);
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            $this->loginError = 'No tienes permiso para acceder al panel de administración.';
            throw ValidationException::withMessages(['authentication' => ' ']);
        }

        session()->regenerate();
        $this->clearRateLimiter();
        session()->forget('throttle_notified');
        session()->forget('login_failed_attempts');

        return app(LoginResponse::class);
    }
}
