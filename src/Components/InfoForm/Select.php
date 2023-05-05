<?php

namespace App\Components\InfoForm;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'info_form:select')]
class Select
{
    public string $label;
    public string $value;
    public ?string $help = null;
}
