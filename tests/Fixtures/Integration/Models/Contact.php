<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;


use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;

class Contact
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Contact|null
     */
    private $manager;
    /**
     * @var Contact[]
     */
    private $relations = [];
    /**
     * @var UploadedFileInterface
     */
    private $photo;
    /**
     * @var DateTimeInterface
     */
    private $birthDate;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getManager(): ?Contact
    {
        return $this->manager;
    }

    /**
     * @param Contact|null $manager
     */
    public function setManager(?Contact $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * @return Contact[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param Contact[] $relations
     */
    public function setRelations(array $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * @return UploadedFileInterface
     */
    public function getPhoto(): UploadedFileInterface
    {
        return $this->photo;
    }

    /**
     * @param UploadedFileInterface $photo
     */
    public function setPhoto(UploadedFileInterface $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return DateTimeInterface
     */
    public function getBirthDate(): DateTimeInterface
    {
        return $this->birthDate;
    }

    /**
     * @param DateTimeInterface $birthDate
     */
    public function setBirthDate(DateTimeInterface $birthDate): void
    {
        $this->birthDate = $birthDate;
    }
}
