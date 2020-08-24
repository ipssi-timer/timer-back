<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Un utilisateur avec cet adresse mail existe déjà.")
 * @UniqueEntity(fields={"pseudo"}, message="Un utilisateur avec ce pseudo existe déjà.")
 */

class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @ORM\Column(type="date",nullable=true)
     *@Assert\NotBlank
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=100)
     *@Assert\Email(
     * message = "Adresse mail '{{ value }}' invalide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "Votre mot de passe doit au moins contenir {{ limit }} caractères.",
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $pseudo;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\GroupUsers", inversedBy="users")
     */
    private $groups;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Entry", mappedBy="user")
     */
    private $entries;


    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->entries = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function setRoles(string $roles):self
    {
        if( 'admin' === $roles ){
            $this->roles = ['ROLE_ADMIN'];
            return $this;
        }

        $this->roles = ['ROLE_USER'];
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }


    public function getUsername(): ?string
    {
        return $this->email;
    }


    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * @return Collection|GroupUsers[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function joinGroup(GroupUsers $group): self
    {
        // TODO : Condition(s) pour rejoindre un groupe
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeUserGroup(GroupUsers $userGroup): self
    {
        if ($this->userGroups->contains($userGroup)) {
            $this->userGroups->removeElement($userGroup);
        }

        return $this;
    }

    /**
     * @return Collection|Entry[]
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries[] = $entry;
            $entry->setUser($this);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->contains($entry)) {
            $this->entries->removeElement($entry);

            if ($entry->getUser() === $this) {
                $entry->setUser(null);
            }
        }

        return $this;
    }




}
