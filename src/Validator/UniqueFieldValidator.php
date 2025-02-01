<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueFieldValidator extends ConstraintValidator {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueField) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s', UniqueField::class));
        }


        $repository = $this->entityManager->getRepository($constraint->entityClass);
        $result = $repository
            ->createQueryBuilder('u')
            ->where('u.' . $constraint->field . ' = :value')
            ->setParameter('value', $value);

        if($this->context->getObject()->getId()) {
            $result = $result
                ->andWhere('u.id != :currentUser')
                ->setParameter('currentUser', $this->context->getObject()->getId());
        }

        $result = $result->getQuery()
            ->getOneOrNullResult();

        if ($result) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ field }}', $constraint->field)
                ->addViolation();
        }
    }
}
