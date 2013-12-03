<?php


namespace devilcius\ExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;



class Iban extends Constraint
{
    public $message = 'This value is not a valid IBAN number.';

}
