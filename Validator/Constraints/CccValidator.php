<?php
namespace devilcius\ExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CccValidator extends ConstraintValidator
{
    /**
     * This number is 20 digit length
     */
    const PATTERN = '/^[0-9]{20}$/';

    /**
     * {@inheritDoc}
     */
    public function isValid($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return true;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;
		
		//remove non numeric chars
		$value = preg_replace("/[^0-9,.]/", "", $value);

        if (!preg_match(static::PATTERN, $value, $matches)) {
            $this->setMessage($constraint->message, array('{{ value }}' => $value));

            return false;
        }

		
        if (!$this->validateCccNumber($value)) {
            $this->setMessage($constraint->message);

            return false;
        }

        return true;
    }

    /**
     *
     * @param string $value
     * @return bool
     */
    private function validateCccNumber($value)
    {
		/*
		*	bank company and bank branch
		*	control digit.
		*/
		$sum = 0;
		$sum += $value[0] * 4;
		$sum += $value[1] * 8;
		$sum += $value[2] * 5;
		$sum += $value[3] * 10;
		$sum += $value[4] * 9;
		$sum += $value[5] * 7;
		$sum += $value[6] * 3;
		$sum += $value[7] * 6;

		$division = floor($sum/11);
		$firstControlDigit = 11 - ($sum - ($division  * 11));
		
		if($firstControlDigit == 11) {
			$firstControlDigit = 0;		
		}

		if($firstControlDigit == 10) {
			$firstControlDigit = 1;		
		}

		if($firstControlDigit != $value[8]) {
			return false;
		}
		/*
		*	account
		*	control digit.
		*/		
		
		$sum = 0;
		$sum += $value[10] * 1;
		$sum += $value[11] * 2;
		$sum += $value[12] * 4;
		$sum += $value[13] * 8;
		$sum += $value[14] * 5;
		$sum += $value[15] * 10;
		$sum += $value[16] * 9;
		$sum += $value[17] * 7;
		$sum += $value[18] * 3;
		$sum += $value[19] * 6;

		$division = floor($sum/11);
		$secondControlDigit = 11 - ($sum-($division  * 11));

		if($secondControlDigit == 11) {
			$secondControlDigit = 0;		
		}		

		if($secondControlDigit == 10) {
			$secondControlDigit = 1;		
		}

		if($secondControlDigit != $value[9]) {
			return false;
		}

	}
}