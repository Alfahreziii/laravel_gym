<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '! 💪')
            ->line('Selamat datang di ' . config('app.name') . '!')
            ->line('Terima kasih telah bergabung bersama kami. Untuk melanjutkan perjalanan fitness Anda, silakan verifikasi alamat email Anda dengan menekan tombol di bawah ini.')
            ->action('Verifikasi Email Saya', $verificationUrl)
            ->line('Link verifikasi ini akan kadaluarsa dalam 60 menit.')
            ->line('Jika Anda tidak membuat akun ini, Anda dapat mengabaikan email ini.')
            ->salutation('Salam Sehat,')
            ->salutation('Tim ' . config('app.name'));
    }
}
