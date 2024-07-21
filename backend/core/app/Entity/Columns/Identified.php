<?php
declare(strict_types=1);

namespace App\Entity\Columns;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV6;

trait Identified
{
    #[
        Groups([ColumnsGroups::ID, ColumnsGroups::ALL]),
        ORM\Id,
        ORM\Column(type: 'uuid', unique: true),
        ORM\GeneratedValue(strategy: 'CUSTOM'),
        ORM\CustomIdGenerator(class: UuidGenerator::class),
    ]
    protected UuidV6 $id;

    public function getId(): UuidV6
    {
        $this->id ??= Uuid::v6();

        return $this->id;
    }

    /**
     * @throws Exception if ID is already set
     */
    public function setId(UuidV6|string|null $id = null): self
    {
        if (isset($this->id)) {
            throw new Exception('ID is already set');
        }
        if (!$id instanceof UuidV6) {
            $this->id = $id !== null ? UuidV6::fromString($id) : Uuid::v4();
        } else {
            $this->id = $id;
        }

        return $this;
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }
}
