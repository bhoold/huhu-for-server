<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastLoginTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $createdTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastLoginIp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdIp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $createdByAdmin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getLastLoginTime(): ?int
    {
        return $this->lastLoginTime;
    }

    public function setLastLoginTime(?int $lastLoginTime): self
    {
        $this->lastLoginTime = $lastLoginTime;

        return $this;
    }

    public function getCreatedTime(): ?int
    {
        return $this->createdTime;
    }

    public function setCreatedTime(?int $createdTime): self
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): self
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }

    public function getCreatedIp(): ?string
    {
        return $this->createdIp;
    }

    public function setCreatedIp(?string $createdIp): self
    {
        $this->createdIp = $createdIp;

        return $this;
    }

    public function getCreatedByAdmin(): ?int
    {
        return $this->createdByAdmin;
    }

    public function setCreatedByAdmin(?int $createdByAdmin): self
    {
        $this->createdByAdmin = $createdByAdmin;

        return $this;
    }
}
