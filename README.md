devilciusExtraValidatorBundle
=================================

This bundle provides validators for common spanish input forms.

Current validators:

CCC
---------
Código Cuenta Cliente

NIF
---------
Nº de indetificación fiscal



Installation
---------------

### composer
    {
        "require": {
            "devilcius/extra-validator-bundle": "dev-master"
        }
    }


### validation.xml
    <?xml version="1.0" ?>
    <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
            http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

        <class name="Acme\DemoBundle\Entity\User">
            <property name="ccc">
                <constraint name="devilcius\ExtraValidatorBundle\Validator\Constraints\Ccc" >
                    <option name="message">Nº de cuenta no válido</option>             
                </constraint>
            </property>
            <property name="dni">
                <constraint name="devilcius\ExtraValidatorBundle\Validator\Constraints\Dni" >
                    <option name="message">Nº de DNI no válido</option>          
                </constraint>
            </property>
        </class>
    </constraint-mapping>
