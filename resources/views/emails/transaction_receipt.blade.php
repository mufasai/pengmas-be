<x-mail::message>
# E-Receipt

**{{ $receipt['transaction_id'] }}**

---

**Tanggal:** {{ $receipt['date'] }} — {{ $receipt['time'] }}<br>
**Kasir:** {{ $receipt['cashier']['name'] }}<br>
**Metode Pembayaran:** {{ $receipt['payment_method'] }}

---

### Items

<x-mail::table>
| Produk | Kategori | Qty | Harga | Total |
|--------|----------|-----|-------|-------|
@foreach ($receipt['items'] as $item)
| {{ $item['product_name'] }} @if($item['variant_notes']) _({{ $item['variant_notes'] }})_ @endif | {{ $item['category'] }} | {{ $item['quantity'] }} | Rp{{ number_format($item['price'], 0, ',', '.') }} | Rp{{ number_format($item['total_price'], 0, ',', '.') }} |
@endforeach
</x-mail::table>

---

<x-mail::panel>
Subtotal: **Rp{{ number_format($receipt['payment_summary']['subtotal'], 0, ',', '.') }}**

@if($receipt['payment_summary']['discount_amount'] > 0)
Diskon: **-Rp{{ number_format($receipt['payment_summary']['discount_amount'], 0, ',', '.') }}**
@endif

Pajak ({{ $receipt['payment_summary']['tax_percentage'] }}%): **Rp{{ number_format($receipt['payment_summary']['tax_amount'], 0, ',', '.') }}**

---

**Total Pembayaran: Rp{{ number_format($receipt['payment_summary']['total_payment'], 0, ',', '.') }}**
</x-mail::panel>

Terima kasih telah berbelanja,<br>
{{ config('app.name') }}
</x-mail::message>