<?php

namespace devilcius\ExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DniValidator extends ConstraintValidator
{

    /**
     * DNI min length
     */
    const NIF_LENGTH = 9;

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {

        if (null === $value || '' === $value) {
            return true;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->validateNif($value)) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }

    // Función auxiliar usada para CIFs y NIFs especiales
    private function getCifSum($cif)
    {
        $sum = $cif[2] + $cif[4] + $cif[6];

        for ($i = 1; $i < 8; $i += 2) {
            $tmp = (string) (2 * $cif[$i]);

            $tmp = $tmp[0] + ((strlen($tmp) == 2) ? $tmp[1] : 0);

            $sum += $tmp;
        }

        return $sum;
    }

    /*
     *  Removes hyphen and ads a leading zero if necesarry 
     * 
     * @param string $value
     * @return string
     */

    private function fixNif($nif)
    {
        $fixedNif = strtoupper($nif);
        //removes hyphen
        if (strlen($nif) >= 2 && strrpos($nif, "-", -2) > 0) {
            $fixedNif = str_replace("-", "", $fixedNif);
        }        
        //add leading 0
        if (strlen($fixedNif) === 8) {
            $fixedNif = "0" . $fixedNif;
        }
        
        return $fixedNif;
    }

    /*
     *  Validates NIFs (DNIs and specials NIFs) 
     * 
     * @param string $value
     * @return bool
     */

    protected function validateNif($nif)
    {
        
        $nif = $this->fixNif($nif);
        
        if(strlen($nif) !== static::NIF_LENGTH) {
            return false;
        }
        
        $nifCodes = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $sum = (string) $this->getCifSum($nif);
        $n = 10 - substr($sum, -1);

        if (preg_match('/^[0-9]{8}[A-Z]{1}$/', $nif)) {
            // DNIs
            $num = substr($nif, 0, 8);

            return ($nif[8] == $nifCodes[$num % 23]);
        } elseif (preg_match('/^[XYZ][0-9]{7}[A-Z]{1}$/', $nif)) {
            // regular NIEs
            $tmp = substr($nif, 1, 7);
            $tmp = strtr(substr($nif, 0, 1), 'XYZ', '012') . $tmp;

            return ($nif[8] == $nifCodes[$tmp % 23]);
        } elseif (preg_match('/^[KLM]{1}/', $nif)) {
            // special NIFs
            return ($nif[8] == chr($n + 64));
        } elseif (preg_match('/^[T]{1}[A-Z0-9]{8}$/', $nif)) {
            // extraordinay NIE
            return true;
        }

        return false;
    }

}
