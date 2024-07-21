<?php

declare(strict_types=1);

namespace App\Entity\Columns;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedAt
{
    #[ApiFilter(OrderFilter::class)]
	#[ORM\Column(type: 'datetime')]
    #[Groups([ColumnsGroups::CREATED_AT, ColumnsGroups::TIMESTAMPS, ColumnsGroups::ALL])]
    protected DateTime $createdAt;

    public function getCreatedAt(): DateTime
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new DateTime();
        }
        return $this->createdAt;
    }

	#[ORM\PrePersist]
    public function setCreatedAtOnPrePersist(): void
    {
        $this->createdAt ??= new DateTime();
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
