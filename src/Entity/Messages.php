<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $wa_id;

    #[ORM\Column(type: 'text')]
    private $whatsappMessage;

    #[ORM\Column(type: 'string', length: 4)]
    private $messageType;

    #[ORM\Column(type: 'datetime')]
    private $created;

    public function __construct()
    {
        $this->created = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getWaId(): ?string
    {
        return $this->wa_id;
    }

    public function setWaId(string $wa_id): self
    {
        $this->wa_id = $wa_id;

        return $this;
    }

    public function getWhatsappMessage(): ?string
    {
        return $this->whatsappMessage;
    }

    public function setWhatsappMessage(string $whatsappMessage): self
    {
        $this->whatsappMessage = $whatsappMessage;

        return $this;
    }

    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }
}
