<?php
declare(strict_types=1);

namespace App\Entity\Columns;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UpdatedAt
{
    #[ApiFilter(OrderFilter::class)]
    #[Groups([ColumnsGroups::UPDATED_AT, ColumnsGroups::TIMESTAMPS, ColumnsGroups::ALL])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $updatedAt = null;

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt ?? null;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

}
