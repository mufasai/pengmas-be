<x-mail::message>
# Halo!

Terima kasih telah mendaftar. Berikut adalah kode verifikasi Anda:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Kode ini akan kedaluwarsa dalam 15 menit. Mohon jangan bagikan kode ini kepada siapapun.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
