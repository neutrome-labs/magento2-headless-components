<?php

namespace NeutromeLabs\HeadlessComponents\Model;

use Magento\Framework\DataObject;
use NeutromeLabs\HeadlessComponents\Api\ThemeInterface;

class Theme extends DataObject implements ThemeInterface
{

    public function getParent(): ?string
    {
        return $this->getData(self::PARENT);
    }

    public function getSlug(): ?string
    {
        return $this->getData(self::SLUG);
    }

    public function getModule(): ?string
    {
        return $this->getData(self::MODULE);
    }

    public function __toString(): string
    {
        return $this->getSlug();
    }
}
