<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerEngine\Shared\Transfer;

use SprykerEngine\Shared\Transfer\Exception\RequiredTransferPropertyException;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Reflection\MethodReflection;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Filter\Word\UnderscoreToCamelCase;

abstract class AbstractTransfer extends \ArrayObject implements TransferInterface
{

    /**
     * @var array
     */
    private $modifiedProperties = [];

    /**
     * @param bool $recursive
     *
     * @return array
     */
    public function toArray($recursive = true)
    {
        $values = [];
        $propertyNames = $this->getPropertyNames();

        $recursive = true;
        $filter = new CamelCaseToUnderscore();
        foreach ($propertyNames as $property) {
            $getter = 'get' . ucfirst($property);
            $value = $this->$getter();

            $key = strtolower($filter->filter($property));

            if (is_object($value)) {
                if ($recursive && $value instanceof TransferInterface) {
                    $values[$key] = $value->toArray($recursive);
                } elseif ($recursive && $value instanceof \ArrayObject && count($value) >= 1) {
                    foreach ($value as $elementKey => $arrayElement) {
                        if (is_array($arrayElement) || is_scalar($arrayElement)) {
                            $values[$key][$elementKey] = $arrayElement;
                        } else {
                            $values[$key][$elementKey] = $arrayElement->toArray($recursive);
                        }
                    }
                } else {
                    $values[$key] = $value;
                }
                continue;
            }

            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * @return array
     */
    private function getPropertyNames()
    {
        $classVars = get_class_vars(get_class($this));
        unset($classVars['modifiedProperties']);

        return array_keys($classVars);
    }

    /**
     * @param bool $recursive
     *
     * @return array
     */
    public function modifiedToArray($recursive = true)
    {
        $returnData = [];
        foreach ($this->modifiedProperties as $modifiedProperty) {
            $key = $modifiedProperty;
            $getterName = 'get' . ucfirst($modifiedProperty);
            $value = $this->$getterName();
            if (is_object($value)) {
                if ($recursive && $value instanceof TransferInterface) {
                    $returnData[$key] = $value->modifiedToArray($recursive);
                } else {
                    $returnData[$key] = $value;
                }
            } else {
                $returnData[$key] = $value;
            }
        }

        return $returnData;
    }

    /**
     * @param array $data
     * @param bool $ignoreMissingProperty
     *
     * @return $this
     */
    public function fromArray(array $data, $ignoreMissingProperty = false)
    {
        $filter = new UnderscoreToCamelCase();
        $properties = $this->getPropertyNames();
        foreach ($data as $key => $value) {
            $property = lcfirst($filter->filter($key));

            if (!in_array($property, $properties)) {
                if ($ignoreMissingProperty) {
                    continue;
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Missing property "%s" in "%s"', $property, get_class($this))
                    );
                }
            }

            $getter = 'get' . ucfirst($property);
            $getterReturn = $this->getGetterReturn($getter);
            $setter = 'set' . ucfirst($property);
            $type = $this->getSetterType($setter);

            // Process Array
            if (is_array($value) && $this->isArray($getterReturn) && $type === 'ArrayObject') {
                $elementType = $this->getArrayTypeWithNamespace($getterReturn);
                $value = $this->processArrayObject($elementType, $value);
            }

            // Process nested Transfer Objects
            if ($this->isTransferClass($type)) {
                /** @var TransferInterface $transferObject */
                $transferObject = new $type();
                if (is_array($value)) {
                    $transferObject->fromArray($value);
                    $value = $transferObject;
                }
            }

            try {
                $this->$setter($value);
            } catch (\Exception $e) {
                if ($ignoreMissingProperty) {
                    throw new \InvalidArgumentException(
                        sprintf('Missing property "%s" in "%s" (setter %s)', $property, get_class($this), $setter)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param string $elementType
     * @param array $arrayObject
     *
     * @return \ArrayObject
     */
    protected function processArrayObject($elementType, array $arrayObject)
    {
        /* @var TransferInterface $transferObject */
        $transferObjectsArray = new \ArrayObject();
        foreach ($arrayObject as $arrayElement) {
            if (is_array($arrayElement)) {
                if ($this->isAssociativeArray($arrayElement)) {
                    $transferObject = new $elementType();
                    $transferObject->fromArray($arrayElement);
                    $transferObjectsArray->append($transferObject);
                } else {
                    foreach ($arrayElement as $arrayElementItem) {
                        $transferObject = new $elementType();
                        $transferObject->fromArray($arrayElementItem);
                        $transferObjectsArray->append($transferObject);
                    }
                }
            } else {
                $transferObjectsArray->append(new $elementType());
            }
        }

        return $transferObjectsArray;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function isAssociativeArray(array $array)
    {
        return array_values($array) !== $array;
    }

    /**
     * @param string $getterMethod
     *
     * @return string
     */
    private function getGetterReturn($getterMethod)
    {
        $reflection = new MethodReflection(get_class($this), $getterMethod);

        /** @var ReturnTag $return */
        $return = $reflection->getDocBlock()->getTag('return');

        return $return->getTypes()[0];
    }

    /**
     * @param string $setter
     *
     * @return string
     */
    private function getSetterType($setter)
    {
        $reflection = new MethodReflection(get_class($this), $setter);

        return $reflection->getParameters()[0]->getType();
    }

    /**
     * @param string $returnType
     *
     * @return bool
     */
    private function isArray($returnType)
    {
        return strpos($returnType, '[]') > -1;
    }

    /**
     * @param string $returnType
     *
     * @return string
     */
    private function getArrayTypeWithNamespace($returnType)
    {
        return $this->getNamespace() . str_replace('[]', '', $returnType);
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        return 'Generated\\Shared\\Transfer\\';
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isTransferClass($type)
    {
        if (!is_string($type)) {
            return false;
        }

        $name = explode('\\', $type);

        return (
            count($name) > 3 &&
            $name[0] === 'Generated' &&
            $name[1] === 'Shared' &&
            $name[2] === 'Transfer'
        );
    }

    /**
     * @param string $property
     */
    protected function addModifiedProperty($property)
    {
        if (!in_array($property, $this->modifiedProperties)) {
            $this->modifiedProperties[] = $property;
        }
    }

    /**
     * @param string $property
     *
     * @throws RequiredTransferPropertyException
     *
     * @return void
     */
    protected function assertPropertyIsSet($property)
    {
        if ($this->$property === null) {
            throw new RequiredTransferPropertyException(sprintf(
                'Missing required property "%s" for transfer %s.',
                $property,
                get_class($this)
            ));
        }
    }

    /**
     * @param string $property
     *
     * @throws RequiredTransferPropertyException
     *
     * @return void
     */
    protected function assertCollectionPropertyIsSet($property)
    {
        /** @var \ArrayObject $collection */
        $collection = $this->$property;
        if ($collection->count() === 0) {
            throw new RequiredTransferPropertyException(sprintf(
                'Empty required collection property "%s" for transfer %s.',
                $property,
                get_class($this)
            ));
        }
    }

}
