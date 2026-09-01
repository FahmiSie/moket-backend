<?php

namespace App\Services;

use App\Models\Ticket;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketService
{
    /**
     * Generate QR Code as Base64 SVG for a given ticket.
     * SVG is preferred to avoid Imagick/GD PHP extension dependencies.
     * 
     * @param Ticket $ticket
     * @return string
     */
    public function generateQrCodeBase64(Ticket $ticket): string
    {
        $svgData = QrCode::format('svg')->size(300)->margin(2)->generate($ticket->code);
        return base64_encode($svgData);
    }
}
