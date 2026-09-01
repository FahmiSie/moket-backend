<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .ticket-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; }
        .qr-code { margin: 15px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; }
        .summary { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
    </style>
</head>
<body>
    @inject('ticketService', 'App\Services\TicketService')
    
    <div class="container">
        <div class="header">
            <h2>E-Ticket Anda Siap!</h2>
            <p>Terima kasih atas pembelian Anda untuk event <strong>{{ $transaction->event->title }}</strong></p>
        </div>

        <div class="summary">
            <p><strong>Nomor Invoice:</strong> {{ $transaction->invoice_number }}</p>
            <p><strong>Tanggal Pembayaran:</strong> {{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, H:i') : '-' }}</p>
            <p><strong>Total Tiket:</strong> {{ $tickets->count() }} tiket</p>
        </div>

        <h3>Daftar Tiket Anda:</h3>
        
        @foreach($tickets as $index => $ticket)
            <div class="ticket-box">
                <h4>Tiket {{ $index + 1 }} - {{ $ticket->ticketType->name }}</h4>
                <p>Kode Unik: {{ $ticket->code }}</p>
                <div class="qr-code">
                    <img src="data:image/svg+xml;base64,{{ $ticketService->generateQrCodeBase64($ticket) }}" alt="QR Code" width="200" height="200">
                </div>
                <p><em>Tunjukkan QR code ini saat check-in event.</em></p>
            </div>
        @endforeach

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh MokeT. Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
