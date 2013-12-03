<?php

namespace devilcius\ExtraValidatorBundle\Tests\Validator\Constraints;

use devilcius\ExtraValidatorBundle\Validator\Constraints\Iban;
use devilcius\ExtraValidatorBundle\Validator\Constraints\IbanValidator;

class IbanValidatorTest extends \PHPUnit_Framework_TestCase
{

    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new IbanValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
                ->method('addViolation');

        $this->validator->validate(null, new Iban());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
                ->method('addViolation');

        $this->validator->validate('', new Iban());
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new Iban());
    }

    
    /**
     * @dataProvider getValidIbans
     */    
    public function testValidIban($iban)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($iban, new Iban());
    }

    public function getValidIbans()
    {
        return array(
            array('ES38-2077-0441-65-1100283362'),
            array('ES38-20770441651100283362'),
            array('ES38 20770441651100283362'),
            array('ES382077044165 1100283362'),
        );
    }    
    
    /**
     * @dataProvider getInvalidIbans
     */
    public function testInvalidIban($iban)
    {
        $constraint = new Iban(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
                ->method('addViolation')
                ->with('myMessage', array(
                    '{{ value }}' => $iban,
        ));

        $this->validator->validate($iban, $constraint);
    }

    public function getInvalidIbans()
    {
        return array(
            array('ES0757-0441-64-11003764231'),
            array('ES112077044164s1100371423s'),
            array('2077-0441-64-110053764 223 '),
            array('2077-0441-64-11003764'),
            array('ESSSS11111111111111111111'),
        );
    }

}
