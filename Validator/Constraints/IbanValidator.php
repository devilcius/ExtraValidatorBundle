<?php

namespace devilcius\ExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ExecutionContextInterface;

class IbanValidator extends ConstraintValidator
{

    private $ibanRegistry = array();
    private $disableIbanGmpExtension;
    protected $context;

    /**
     * {@inheritDoc}
     */
    public function initialize(ExecutionContextInterface $context)
    {
        $this->ibanLoadRegistry();
        $this->context = $context;
    }

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

        if (!$this->validateIbanNumber($value)) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }

    /**
     *
     * @param string $value
     * @return bool
     */
    private function validateIbanNumber($iban)
    {
        # First convert to machine format.
        $iban = $this->ibanToMachineFormat($iban);

        # Get country of IBAN
        $country = $this->ibanGetCountryPart($iban);
     
        # Test length of IBAN
        if (strlen($iban) != $this->ibanCountryGetIbanLength($country)) {
            return false;
        }         
       
        # Get country-specific IBAN format regex
        $regex = '/' . $this->ibanCountryGetIbanFormatRegex($country) . '/';          
        
        # Check regex and checksum
        if (preg_match($regex, $iban)) {
            # Regex passed, check checksum
            if (!$this->ibanVerifyChecksum($iban)) {
                return false;
            }
        } else {
            return false;
        }

        # Otherwise it 'could' exist
        return true;
    }

    # Get the country part from an IBAN

    private function ibanGetCountryPart($iban)
    {
        $iban = $this->ibanToMachineFormat($iban);
        return substr($iban, 0, 2);
    }

    # Get the IBAN length for an IBAN country

    private function ibanCountryGetIbanLength($iban_country)
    {
        return $this->ibanCountryGetInfo($iban_country, 'iban_length');
    }

    # Get the IBAN format (as a regular expression) for an IBAN country

    private function ibanCountryGetIbanFormatRegex($ibanCountry)
    {
        return $this->ibanCountryGetInfo($ibanCountry, 'iban_format_regex');
    }

    # Get information from the IBAN registry by country / code combination

    private function ibanCountryGetInfo($country, $code)
    {
       
        $country = strtoupper($country);
        $code = strtolower($code);
        if (array_key_exists($country, $this->ibanRegistry)) {
            if (array_key_exists($code, $this->ibanRegistry[$country])) {                
                return $this->ibanRegistry[$country][$code];               
            }
        }
        return false;
    }

# Check the checksum of an IBAN - code modified from Validate_Finance PEAR class

    private function ibanVerifyChecksum($iban)
    {
        # convert to machine format
        $iban = $this->ibanToMachineFormat($iban);
        # move first 4 chars (countrycode and checksum) to the end of the string
        $tempiban = substr($iban, 4) . substr($iban, 0, 4);
        # subsitutute chars
        $tempiban = $this->ibanChecksumStringReplace($tempiban);
        # mod97-10
        $result = $this->ibanMod9710($tempiban);
        # checkvalue of 1 indicates correct IBAN checksum
        if ($result != 1) {
            return false;
        }
        return true;
    }

# Perform MOD97-10 checksum calculation ('Germanic-level effiency' version - thanks Chris!)

    private function ibanMod9710($numericRepresentation)
    {
        # prefer php5 gmp extension if available
        if (!($this->disableIbanGmpExtension) && function_exists('gmp_intval')) {
            return gmp_intval(gmp_mod(gmp_init($numericRepresentation, 10), '97')) === 1;
        }
        
        # new manual processing (~3x slower)
        $length = strlen($numericRepresentation);
        $rest = "";
        $position = 0;
        while ($position < $length) {
            $value = 9 - strlen($rest);
            $n = $rest . substr($numericRepresentation, $position, $value);
            $rest = $n % 97;
            $position = $position + $value;
        }
        return ($rest === 1);
    }

# Character substitution required for IBAN MOD97-10 checksum validation/generation
#  $s  Input string (IBAN)

    function ibanChecksumStringReplace($string)
    {
        $ibanReplaceChars = range('A', 'Z');
        foreach (range(10, 35) as $tempvalue) {
            $ibanReplaceValues[] = strval($tempvalue);
        }
        return str_replace($ibanReplaceChars, $ibanReplaceValues, $string);
    }

# Convert an IBAN to machine format.  To do this, we
# remove IBAN from the start, if present, and remove
# non basic roman letter / digit characters

    private function ibanToMachineFormat($iban)
    {
        # Uppercase and trim spaces from left
        $iban = ltrim(strtoupper($iban));
        # Remove IIBAN or IBAN from start of string, if present
        $iban = preg_replace('/^I?IBAN/', '', $iban);
        # Remove all non basic roman letter / digit characters
        $iban = preg_replace('/[^a-zA-Z0-9]/', '', $iban);

        return $iban;
    }

    private function ibanLoadRegistry()
    {
        # if the registry is not yet loaded, or has been corrupted, reload
        if (!is_array($this->ibanRegistry) || count($this->ibanRegistry) < 1) {
            $data = file_get_contents(dirname(__FILE__) . '/iban-country-regex.txt');
            $lines = explode("\n", $data);
            array_shift($lines); # drop leading description line
            # loop through lines
            foreach ($lines as $line) {
                if ($line != '') {
                    # split to fields
                    $oldDisplayErrorsValue = ini_get('display_errors');
                    ini_set('display_errors', false);
                    $oldErrorReportingValue = ini_get('error_reporting');
                    ini_set('error_reporting', false);
                    list($country, $countryName, $domesticExample, $bbanExample, $bbanFormatSwift, $bbanFormatRegex, $bbanLength, $ibanExample, $ibanFormatSwift, $ibanFormatRegex, $ibanLength, $bbanBankidStartOffset, $bbanBankidStopOffset, $bbanBranchidStartOffset, $bbanBranchidStopOffset, $registryEdition, $countrySepa) = explode('|', $line);
                    ini_set('display_errors', $oldDisplayErrorsValue);
                    ini_set('error_reporting', $oldErrorReportingValue);
                    # assign to registry
                    $this->ibanRegistry[$country] = array(
                        'country' => $country,
                        'country_name' => $countryName,
                        'country_sepa' => $countrySepa,
                        'domestic_example' => $domesticExample,
                        'bban_example' => $bbanExample,
                        'bban_format_swift' => $bbanFormatSwift,
                        'bban_format_regex' => $bbanFormatRegex,
                        'bban_length' => $bbanLength,
                        'iban_example' => $ibanExample,
                        'iban_format_swift' => $ibanFormatSwift,
                        'iban_format_regex' => $ibanFormatRegex,
                        'iban_length' => $ibanLength,
                        'bban_bankid_start_offset' => $bbanBankidStartOffset,
                        'bban_bankid_stop_offset' => $bbanBankidStopOffset,
                        'bban_branchid_start_offset' => $bbanBranchidStartOffset,
                        'bban_branchid_stop_offset' => $bbanBankidStopOffset,
                        'registry_edition' => $registryEdition
                    );
                }
            }
        }
    }

}
