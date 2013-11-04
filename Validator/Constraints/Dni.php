<?php


namespace devilcius\ExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;



class Dni extends Constraint
{
    public $message = 'This value is not a valid DNI number.';

}
