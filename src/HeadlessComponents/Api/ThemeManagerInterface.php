<?php

namespace NeutromeLabs\HeadlessComponents\Api;

interface ThemeManagerInterface
{

    public function current(): ThemeInterface;

    public function find(string $slug): ?ThemeInterface;

    public function list(): array;
}
