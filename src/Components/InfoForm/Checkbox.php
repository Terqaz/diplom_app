<?php

namespace App\Components\InfoForm;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'info_form:checkbox')]
class Checkbox
{
    public string $label;
    public ?string $help = null;
    public bool $checked;
}
