<?php

namespace App\Service\Front;

use App\Enum\EnumerationInterface;
use LogicException;
use ReflectionClass;
use Symfony\Contracts\Translation\TranslatorInterface;

class EnumerationTranslator
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function transByEnum(string $path, string $enum): array
    {
        $reflection = new ReflectionClass($enum);

        if (!$reflection->implementsInterface(EnumerationInterface::class)) {
            throw new LogicException('Enum ' . $enum . ' must implements ' . EnumerationInterface::class);
        }

        return $this->transByArray($path, $enum::getTypes());
    }

    public function transByArray(string $path, array $types, array $params = []): array
    {
        $translationsMap = [];

        foreach ($types as $type) {
            $translationsMap[$type] = $this->translator->trans($path . '.' . $type, $params);
        }

        return $translationsMap;
    }

    public function transByValues(string $path, string $param, array $values): array
    {
        $translationsMap = [];

        foreach ($values as $value) {
            $translationsMap[$value] = $this->translator->trans($path, [$param => $value]);
        }

        return $translationsMap;
    }
}
