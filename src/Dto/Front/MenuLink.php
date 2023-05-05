<?php

namespace App\Dto\Front;

class MenuLink
{
    private string $code;
    private string $routeName;
    private array $queryParams;

    public function __construct(string $code, string $routeName, array $queryParams = []) {
        $this->code = $code;
        $this->routeName = $routeName;
        $this->queryParams = $queryParams;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
