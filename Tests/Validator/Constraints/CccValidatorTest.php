<?php

namespace devilcius\ExtraValidatorBundle\Tests\Validator\Constraints;

use devilcius\ExtraValidatorBundle\Validator\Constraints\Ccc;
use devilcius\ExtraValidatorBundle\Validator\Constraints\CccValidator;

class CccValidatorTest extends \PHPUnit_Framework_TestCase
{

    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new CccValidator();
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

        $this->validator->validate(null, new Ccc());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
                ->method('addViolation');

        $this->validator->validate('', new Ccc());
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new Ccc());
    }

    
    /**
     * @dataProvider getValidCccs
     */    
    public function testValidCcc($ccc)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($ccc, new Ccc());
    }

    public function getValidCccs()
    {
        return array(
            array('2077-0441-64-1100376423'),
            array('20770441641100376423'),
            array('2077-0441-64-11003764 23 '),
            array('2077-0441-64-11003764-2-3'),
        );
    }    
    
    /**
     * @dataProvider getInvalidCccs
     */
    public function testInvalidCcc($ccc)
    {
        $constraint = new Ccc(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
                ->method('addViolation')
                ->with('myMessage', array(
                    '{{ value }}' => $ccc,
        ));

        $this->validator->validate($ccc, $constraint);
    }

    public function getInvalidCccs()
    {
        return array(
            array('20757-0441-64-11003764231'),
            array('2077044164s1100371423s'),
            array('2077-0441-64-110053764 223 '),
            array('2077-0441-64-11003764'),
            array('11111111111111111111'),
        );
    }

}
