<?php

namespace App\Twig;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private DecoderInterface $decoder;

    public function __construct(DecoderInterface $decoder) {
        $this->decoder = $decoder;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
        ];
    }

    public function jsonDecode(string $s)
    {
        return $this->decoder->decode($s, 'json');
    }
}
