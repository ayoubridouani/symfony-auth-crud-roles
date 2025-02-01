<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class UniqueField extends Constraint {

    public string $message = 'The {{ field }} "{{ value }}" is already in use.';
    public string $entityClass;
    public String $field;
    public $groups;

    public function __construct($entityClass, $field, $message, array $options = [])
    {
        $this->entityClass = $entityClass;
        $this->field = $field;
        $this->message = $message;

        parent::__construct($options);
    }
}
