<?php

namespace NeutromeLabs\HeadlessComponents\Api;

interface ThemeInterface
{

    public const PARENT = 'parent';

    public const SLUG = 'slug';

    public const MODULE = 'module';

    public function getParent(): ?string;

    public function getSlug(): ?string;

    public function getModule(): ?string;

    public function __toString(): string;
}
