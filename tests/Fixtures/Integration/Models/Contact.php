<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;


use DateTimeImmutable;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Annotations\Security;
use function array_search;
use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Autowire;

/**
 * @Type()
 * @MagicField(name="magicContact", phpType="Contact")
 */
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
    /**
     * @var string
     */
    private $company;
    /**
     * @Field()
     * @var int
     */
    private $age = 42;
    /**
     * @Field()
     * @var string
     */
    public $nickName = 'foo';
    /**
     * @Field()
     * @var string
     */
    public $status = 'foo';
    /**
     * @Field()
     * @var string
     */
    public $address = 'foo';
    /**
     * @Field()
     * @var bool
     */
    private $private = true;
    /**
     * @Field()
     * @var string
     */
    private $zipcode = '5555';
    /**
     * @Field()
     * @Right("NO_ACCESS")
     * @FailWith(null)
     * @var string
     */
    public $failWithNull = 'This should fail with NULL!';
    /**
     * @Field()
     * @Right("NO_ACCESS")
     * @HideIfUnauthorized()
     * @var string
     */
    public $hidden = 'you can see the property only if you have access';
    /**
     * @Field()
     * @Logged()
     * @var string
     */
    public $forLogged = 'you can see this only if you are logged';
    /**
     * @Field()
     * @Right("NO_ACCESS")
     * @var string
     */
    public $withRight = 'you can see this only if you have sufficient right';
    /**
     * @Field()
     * @Security("is_granted('NO_ACCESS')")
     * @var string
     */
    public $secured = 'you can see this only if access granted';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @deprecated use field `name`
     */
    public function getDeprecatedName()
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
    public function getBirthDate()
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

    /**
     * This getter will be overridden in the extend class.
     *
     * @Field()
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    /**
     * @Field()
     */
    public function repeatInnerName(#[Prefetch('prefetchTheContacts')] $data): string
    {
        $index = array_search($this, $data, true);
        if ($index === false) {
            throw new \RuntimeException('Index not found');
        }
        return $data[$index]->getName();
    }

    public static function prefetchTheContacts(iterable $contacts)
    {
        return $contacts;
    }

    /**
     * @Field()
     * @Logged()
     * @return string
     */
    public function onlyLogged(): string
    {
        return 'you can see this only if you are logged';
    }

    /**
     * @Field()
     * @Right(name="CAN_SEE_SECRET")
     * @return string
     */
    public function secret(): string
    {
        return 'you can see this only if you have the good right';
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'bar';
    }

    /**
     * @return string
     */
    public function getZipcode(string $foo): string
    {
        return $this->zipcode;
    }

    /**
     * @return string
     */
    private function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @Field()
     * @Autowire(for="testService", identifier="testService")
     * @Autowire(for="$otherTestService")
     * @return string
     */
    public function injectService(string $testService, stdClass $otherTestService = null): string
    {
        if ($testService !== 'foo') {
            return 'KO';
        }
        if (!$otherTestService instanceof stdClass) {
            return 'KO';
        }
        return 'OK';
    }

    public function injectServiceFromExternal(string $testService, string $testSkip = "foo", string $id = '42'): string
    {
        if ($testService !== 'foo') {
            return 'KO';
        }
        if ($testSkip !== 'foo') {
            return 'KO';
        }
        if ($id !== '42') {
            return 'KO';
        }
        return 'OK';
    }

    public function __get(string $property)
    {
        return new Contact('foo');
    }
}
