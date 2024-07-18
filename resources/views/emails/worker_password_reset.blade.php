{{-- resources/views/emails/worker_password_reset.blade.php --}}

@component('mail::message')
# Password Reset

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $resetLink])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ 'ahmed kahled' }}
@endcomponent
