<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Palette\RGB;

class BingoCardGenerator
{
    protected $imagine;
    protected $palette;

    public function __construct()
    {
        $this->imagine = new Imagine();
        $this->palette = new RGB();
    }

    public function generateCardImage(array $numbers, string $serialNumber, string $paymentStatus = 'Pendiente')
    {
        $width = 600;
        $height = 900; // Aumentamos la altura para el logo y estado de pago

        // Crear imagen base
        $image = $this->imagine->create(new Box($width, $height), $this->palette->color('#ffffff'));

        // Configuraciones
        $cellSize = 100;
        $headerHeight = 80;
        $margin = 50;
        $letters = ['B', 'I', 'N', 'G', 'O'];

        // --- 1. AGREGAR LOGO EN LA PARTE SUPERIOR ---
        $this->addLogo($image, $width);

        // --- 2. ENCABEZADO CON LETRAS BINGO ---
        $this->drawHeader($image, $letters, $margin, $headerHeight, $cellSize);

        // --- 3. CUERPO CON NÚMEROS ---
        $this->drawNumbers($image, $numbers, $letters, $margin, $headerHeight, $cellSize);

        // --- 4. NÚMERO DE SERIE ---
        $this->drawSerialNumber($image, $width, $height, $serialNumber);

        // --- 5. ESTADO DE PAGO ---
        $this->drawPaymentStatus($image, $width, $height, $paymentStatus);

        return $image;
    }

    protected function addLogo(&$image, $width)
    {
        try {
            $logoPath = public_path('images/logo.webp');
            if (file_exists($logoPath)) {
                $logo = $this->imagine->open($logoPath);
                $logoSize = $logo->getSize();

                // Redimensionar logo si es necesario (max 200px de ancho)
                if ($logoSize->getWidth() > 200) {
                    $ratio = 200 / $logoSize->getWidth();
                    $logo->resize(new Box(200, $logoSize->getHeight() * $ratio));
                }

                // Centrar logo en la parte superior
                $logoX = ($width - $logo->getSize()->getWidth()) / 2;
                $image->paste($logo, new Point($logoX, 20));
            }
        } catch (\Exception $e) {
            // Si hay error con el logo, continuar sin él
            Log::error('Error al cargar el logo: ' . $e->getMessage());
        }
    }

    protected function drawHeader(&$image, $letters, $margin, $headerHeight, $cellSize)
    {
        $headerFont = $this->imagine->font(public_path('webfonts/arial.ttf'), 36, $this->palette->color('#ffffff'));

        foreach ($letters as $i => $letter) {
            $x = $margin + ($i * $cellSize);

            // Fondo azul para el encabezado
            $header = $this->imagine->create(
                new Box($cellSize, $headerHeight),
                $this->palette->color('#3b82f6')
            );
            $image->paste($header, new Point($x, 150)); // 150 para dejar espacio para el logo

            // Texto centrado
            $textWidth = $headerFont->box($letter)->getWidth();
            $textHeight = $headerFont->box($letter)->getHeight();
            $textX = $x + ($cellSize - $textWidth) / 2;
            $textY = 150 + ($headerHeight - $textHeight) / 2;

            $image->draw()->text($letter, $headerFont, new Point($textX, $textY));
        }
    }

    protected function drawNumbers(&$image, $numbers, $letters, $margin, $headerHeight, $cellSize)
    {
        $numberFont = $this->imagine->font(public_path('webfonts/arial.ttf'), 24, $this->palette->color('#000000'));

        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                $x = $margin + ($col * $cellSize);
                $y = 150 + $headerHeight + ($row * $cellSize);

                // Borde de celda
                $image->draw()->rectangle(
                    new Point($x, $y),
                    new Point($x + $cellSize, $y + $cellSize),
                    $this->palette->color('#000000'),
                    false
                );

                // Contenido de celda
                if ($row === 2 && $col === 2) {
                    $this->drawFreeSpace($image, $x, $y, $cellSize);
                } else {
                    $number = $numbers[$letters[$col]][$row] ?? '';
                    if ($number !== '') {
                        $this->drawNumber($image, $number, $x, $y, $cellSize, $numberFont);
                    }
                }
            }
        }
    }

    protected function drawFreeSpace(&$image, $x, $y, $cellSize)
    {
        $freeFont = $this->imagine->font(public_path('webfonts/arial.ttf'), 20, $this->palette->color('#000000'));

        // Fondo especial para el espacio libre
        $freeSpace = $this->imagine->create(
            new Box($cellSize, $cellSize),
            $this->palette->color('#f0f0f0')
        );
        $image->paste($freeSpace, new Point($x, $y));

        // Texto "LIBRE" centrado
        $text = 'LIBRE';
        $textWidth = $freeFont->box($text)->getWidth();
        $textX = $x + ($cellSize - $textWidth) / 2;
        $textY = $y + ($cellSize / 2) - 10;

        $image->draw()->text($text, $freeFont, new Point($textX, $textY));
    }

    protected function drawNumber(&$image, $number, $x, $y, $cellSize, $font)
    {
        $textWidth = $font->box($number)->getWidth();
        $textHeight = $font->box($number)->getHeight();
        $textX = $x + ($cellSize - $textWidth) / 2;
        $textY = $y + ($cellSize - $textHeight) / 2;

        $image->draw()->text($number, $font, new Point($textX, $textY));
    }

    protected function drawSerialNumber(&$image, $width, $height, $serialNumber)
    {
        $serialFont = $this->imagine->font(public_path('webfonts/arial.ttf'), 18, $this->palette->color('#000000'));
        $text = "N° Serie: $serialNumber";
        $textWidth = $serialFont->box($text)->getWidth();

        $image->draw()->text(
            $text,
            $serialFont,
            new Point(($width - $textWidth) / 2, $height - 80)
        );
    }

    protected function drawPaymentStatus(&$image, $width, $height, $paymentStatus)
    {
        $statusColors = [
            'paid' => '#10B981', // Verde
            'pending' => '#F59E0B', // Amarillo
            'failed' => '#EF4444' // Rojo
        ];

        $color = $statusColors[strtolower($paymentStatus)] ?? '#6B7280'; // Gris por defecto

        $statusFont = $this->imagine->font(public_path('webfonts/arial-bold.ttf'), 20, $this->palette->color('#ffffff'));
        $statusText = strtoupper("Estado: {$paymentStatus}");

        // Fondo del estado
        $statusBg = $this->imagine->create(
            new Box($width - 100, 40),
            $this->palette->color($color)
        );
        $image->paste($statusBg, new Point(50, $height - 50));

        // Texto centrado
        $textWidth = $statusFont->box($statusText)->getWidth();
        $image->draw()->text(
            $statusText,
            $statusFont,
            new Point(($width - $textWidth) / 2, $height - 40)
        );
    }
}
