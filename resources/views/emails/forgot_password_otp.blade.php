<x-mail::message>
# Halo!

Kami menerima permintaan untuk mereset password Anda. Berikut adalah kode pemulihan Anda:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Kode ini akan kedaluwarsa dalam 15 menit. Jika Anda tidak merasa meminta reset password, abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
